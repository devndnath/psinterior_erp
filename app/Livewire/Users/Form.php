<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?User $user = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;

    /** @var array<int,string> */
    public array $roles = [];

    public function mount(?User $user = null): void
    {
        // Only ever edit a Staff login here — a Client/Vendor row
        // reaching this route by id would be a different audience
        // entirely (User::isStaff()/scopeStaff()).
        $this->user = ($user?->exists && $user->isStaff()) ? $user : null;

        Gate::authorize($this->user ? 'update' : 'create', $this->user ?? User::class);

        if ($this->user) {
            $this->fill([
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => (string) $this->user->phone,
                'is_active' => $this->user->is_active,
                'roles' => $this->user->getRoleNames()->all(),
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.($this->user?->id ?: 'NULL')],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => [$this->user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name,guard_name,web'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        // A super-admin role can only be granted by someone who already
        // holds it — stops an Admin from quietly promoting an account
        // (including their own) to the one role that bypasses every
        // permission check.
        if (in_array('super-admin', $data['roles'], true) && ! auth()->user()->hasRole('super-admin')) {
            $this->addError('roles', 'Only a Super Admin can assign the Super Admin role.');

            return;
        }

        $service = app(UserService::class);

        if ($this->user) {
            $service->update($this->user, $data, $data['roles']);
            $message = 'User updated.';
        } else {
            $service->create($data, $data['roles']);
            $message = 'User created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('users.index', navigate: true);
    }

    #[Title('User')]
    public function render()
    {
        return view('livewire.users.form', [
            'availableRoles' => Role::where('guard_name', 'web')->orderBy('name')->pluck('name'),
        ]);
    }
}
