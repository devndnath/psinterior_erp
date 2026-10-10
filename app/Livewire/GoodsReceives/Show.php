<?php

namespace App\Livewire\GoodsReceives;

use App\Models\GoodsReceive;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public GoodsReceive $goodsReceive;

    public function mount(GoodsReceive $goodsReceive): void
    {
        Gate::authorize('view', $goodsReceive);

        $this->goodsReceive = $goodsReceive;
    }

    #[Title('Goods Receive')]
    public function render()
    {
        $this->goodsReceive->load([
            'purchaseOrder.vendor',
            'purchaseOrder.project',
            'receivedBy',
            'items.purchaseOrderItem.product',
        ]);

        return view('livewire.goods-receives.show');
    }
}
