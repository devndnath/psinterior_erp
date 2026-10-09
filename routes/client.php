<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\ClientLogin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client portal routes — mounted under /client with the `client` guard
| forced for the whole group (bootstrap/app.php). Guard access only; the
| portal's actual screens (project progress, designs, approvals, invoices,
| documents — spec §43) are built in Phase 11 once every module they read
| from exists. Phase 1 just proves a client can log in and nowhere else.
|--------------------------------------------------------------------------
*/

Route::middleware('guest:client')->group(function () {
    Route::get('/login', ClientLogin::class)->name('login');
});

Route::post('/logout', LogoutController::class)->name('logout')->defaults('guard', 'client');

Route::middleware(['auth:client', 'user_type:client'])->group(function () {
    Route::get('/', fn () => redirect()->route('client.dashboard'));
    Route::get('/dashboard', fn () => view('client.dashboard-placeholder'))->name('dashboard');
});
