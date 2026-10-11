<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Vendor\PurchaseOrderPdfController;
use App\Livewire\Auth\VendorLogin;
use App\Livewire\Vendor\Dashboard as VendorDashboard;
use App\Livewire\Vendor\Ledger\Index as VendorLedgerIndex;
use App\Livewire\Vendor\PurchaseOrders\Index as VendorPurchaseOrderIndex;
use App\Livewire\Vendor\PurchaseOrders\Show as VendorPurchaseOrderShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor portal routes — mirrors routes/client.php. Every screen below
| enforces ownership ("is this MY record") inside its own Livewire
| component via a plain comparison against auth()->user()->vendor,
| never via Gate::authorize/->can() against a staff Policy — see
| "Portal authorization model" in PHASE11_SUMMARY.md.
|--------------------------------------------------------------------------
*/

Route::middleware('guest:vendor')->group(function () {
    Route::get('/login', VendorLogin::class)->name('login');
});

Route::post('/logout', LogoutController::class)->name('logout')->defaults('guard', 'vendor');

Route::middleware(['auth:vendor', 'user_type:vendor'])->group(function () {
    Route::get('/', fn () => redirect()->route('vendor.dashboard'));
    Route::get('/dashboard', VendorDashboard::class)->name('dashboard');

    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', VendorPurchaseOrderIndex::class)->name('index');
        Route::get('/{purchaseOrder}', VendorPurchaseOrderShow::class)->name('show');
        // Plain GET route, not a Livewire action — see
        // App\Http\Controllers\Pdf\InvoicePdfController's docblock: a
        // binary PDF response can't safely round-trip through Livewire's
        // JSON update cycle. Ownership re-checked inside the controller.
        Route::get('/{purchaseOrder}/pdf', PurchaseOrderPdfController::class)->name('pdf');
    });

    Route::get('/ledger', VendorLedgerIndex::class)->name('ledger.index');
});
