<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Vendors'])]
#[Title('Vendors')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    /** '' | due | paid | advance | none — by what's owed to the vendor. */
    #[Url]
    public string $balance = '';

    #[Url]
    public string $sort = 'latest';

    public function mount(): void
    {
        Gate::authorize('viewAny', Vendor::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'balance', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'balance', 'sort']);
    }

    /**
     * Payable = total of purchase orders placed with the vendor (sent,
     * partly or fully received — not drafts or cancelled). Paid =
     * expenses recorded against the vendor. Due = payable − paid; a
     * negative due is an advance. Same basis as the Vendor Wise Payment
     * report. Raw SQL so the list can filter and sort on them.
     */
    private const PAYABLE = "(select coalesce(sum(po.total), 0) from purchase_orders po where po.vendor_id = vendors.id and po.deleted_at is null and po.status not in ('draft', 'cancelled'))";

    private const PAID = '(select coalesce(sum(e.amount), 0) from expenses e where e.vendor_id = vendors.id and e.deleted_at is null)';

    public static function showsMoney(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->can('view_finance') || $user?->can('view_purchase_orders'));
    }

    public function deleteVendor(int $vendorId): void
    {
        $vendor = Vendor::findOrFail($vendorId);
        Gate::authorize('delete', $vendor);

        $vendor->delete();

        $this->dispatch('toast', message: "Vendor {$vendor->vendor_code} deleted.", type: 'success');
    }

    public function render()
    {
        $money = self::showsMoney();
        $due = '('.self::PAYABLE.' - '.self::PAID.')';

        $base = Vendor::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->search}%")
                    ->orWhere('vendor_code', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%");
            }))
            ->when($money && $this->balance === 'due', fn ($q) => $q->whereRaw("$due > 0.005"))
            ->when($money && $this->balance === 'paid', fn ($q) => $q->whereRaw(self::PAYABLE.' > 0')->whereRaw("abs($due) <= 0.005"))
            ->when($money && $this->balance === 'advance', fn ($q) => $q->whereRaw("$due < -0.005"))
            ->when($money && $this->balance === 'none', fn ($q) => $q->whereRaw(self::PAYABLE.' = 0')->whereRaw(self::PAID.' = 0'));

        $vendors = (clone $base)
            ->when($money, fn ($q) => $q->select('vendors.*')->selectRaw(self::PAYABLE.' as payable_sum, '.self::PAID.' as paid_sum'))
            ->when($money && $this->sort === 'due', fn ($q) => $q->orderByRaw("$due desc"))
            ->when($money && $this->sort === 'payable', fn ($q) => $q->orderByRaw(self::PAYABLE.' desc'))
            ->when($money && $this->sort === 'paid', fn ($q) => $q->orderByRaw(self::PAID.' desc'))
            ->when($this->sort === 'name', fn ($q) => $q->orderBy('company_name'))
            ->latest()
            ->paginate(15);

        // Totals across every vendor matching the filters, not just this page.
        $summary = null;
        if ($money) {
            $all = (clone $base)->selectRaw(self::PAYABLE.' as payable_sum, '.self::PAID.' as paid_sum')->get();
            $dues = $all->map(fn ($v) => (float) $v->payable_sum - (float) $v->paid_sum);
            $summary = [
                'vendors' => $all->count(),
                'payable' => (float) $all->sum('payable_sum'),
                'paid' => (float) $all->sum('paid_sum'),
                'due' => (float) $dues->filter(fn ($d) => $d > 0)->sum(),
                'withDue' => $dues->filter(fn ($d) => $d > 0.005)->count(),
                'advance' => (float) -$dues->filter(fn ($d) => $d < 0)->sum(),
            ];
        }

        return view('livewire.vendors.index', [
            'vendors' => $vendors,
            'money' => $money,
            'summary' => $summary,
        ]);
    }
}
