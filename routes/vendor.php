<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\VendorLogin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor portal routes — mirrors routes/client.php. Full screens (POs,
| goods receiving, invoices, payment status — spec §44) are Phase 11 work.
|--------------------------------------------------------------------------
*/

Route::middleware('guest:vendor')->group(function () {
    Route::get('/login', VendorLogin::class)->name('login');
});

Route::post('/logout', LogoutController::class)->name('logout')->defaults('guard', 'vendor');

Route::middleware(['auth:vendor', 'user_type:vendor'])->group(function () {
    Route::get('/', fn () => redirect()->route('vendor.dashboard'));
    Route::get('/dashboard', fn () => view('vendor.dashboard-placeholder'))->name('dashboard');
});
