<div>
    <div class="grid grid-cols-2 gap-4">
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Open Purchase Orders</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $openPurchaseOrders }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Received This Month</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $receivedThisMonth }}</p>
        </div>
    </div>
</div>
