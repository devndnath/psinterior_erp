<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Client\InvoicePdfController;
use App\Livewire\Auth\ClientLogin;
use App\Livewire\Client\ChangeOrders\Index as ClientChangeOrderIndex;
use App\Livewire\Client\ChangeOrders\Show as ClientChangeOrderShow;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\Designs\Index as ClientDesignIndex;
use App\Livewire\Client\Designs\Show as ClientDesignShow;
use App\Livewire\Client\Documents\Index as ClientDocumentIndex;
use App\Livewire\Client\Invoices\Index as ClientInvoiceIndex;
use App\Livewire\Client\Invoices\Show as ClientInvoiceShow;
use App\Livewire\Client\Projects\Index as ClientProjectIndex;
use App\Livewire\Client\Projects\Show as ClientProjectShow;
use App\Livewire\Client\Quotations\Index as ClientQuotationIndex;
use App\Livewire\Client\Quotations\Show as ClientQuotationShow;
use App\Livewire\Client\ServiceRequests\Form as ClientServiceRequestForm;
use App\Livewire\Client\ServiceRequests\Index as ClientServiceRequestIndex;
use App\Livewire\Client\ServiceRequests\Show as ClientServiceRequestShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client portal routes — mounted under /client with the `client` guard
| forced for the whole group (bootstrap/app.php). Every screen below
| enforces ownership ("is this MY record") inside its own Livewire
| component via a plain comparison against auth()->user()->client, never
| via Gate::authorize/->can() against a staff Policy — see "Portal
| authorization model" in PHASE11_SUMMARY.md. The `auth:client` +
| `user_type:client` middleware here is only the HTTP-layer guard check,
| not the ownership check.
|--------------------------------------------------------------------------
*/

Route::middleware('guest:client')->group(function () {
    Route::get('/login', ClientLogin::class)->name('login');
});

Route::post('/logout', LogoutController::class)->name('logout')->defaults('guard', 'client');

Route::middleware(['auth:client', 'user_type:client'])->group(function () {
    Route::get('/', fn () => redirect()->route('client.dashboard'));
    Route::get('/dashboard', ClientDashboard::class)->name('dashboard');

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', ClientProjectIndex::class)->name('index');
        Route::get('/{project}', ClientProjectShow::class)->name('show');
    });

    Route::prefix('designs')->name('designs.')->group(function () {
        Route::get('/', ClientDesignIndex::class)->name('index');
        Route::get('/{design}', ClientDesignShow::class)->name('show');
    });

    Route::prefix('quotations')->name('quotations.')->group(function () {
        Route::get('/', ClientQuotationIndex::class)->name('index');
        Route::get('/{quotation}', ClientQuotationShow::class)->name('show');
    });

    Route::prefix('change-orders')->name('change-orders.')->group(function () {
        Route::get('/', ClientChangeOrderIndex::class)->name('index');
        Route::get('/{changeOrder}', ClientChangeOrderShow::class)->name('show');
    });

    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', ClientInvoiceIndex::class)->name('index');
        Route::get('/{invoice}', ClientInvoiceShow::class)->name('show');
        // Plain GET route, not a Livewire action — see
        // App\Http\Controllers\Pdf\InvoicePdfController's docblock: a
        // binary PDF response can't safely round-trip through Livewire's
        // JSON update cycle. Ownership re-checked inside the controller.
        Route::get('/{invoice}/pdf', InvoicePdfController::class)->name('pdf');
    });

    Route::get('/documents', ClientDocumentIndex::class)->name('documents.index');

    Route::prefix('service-requests')->name('service-requests.')->group(function () {
        Route::get('/', ClientServiceRequestIndex::class)->name('index');
        Route::get('/create', ClientServiceRequestForm::class)->name('create');
        Route::get('/{serviceRequest}', ClientServiceRequestShow::class)->name('show');
    });
});
