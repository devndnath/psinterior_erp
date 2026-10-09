<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Clients\Show as ClientShow;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Designs\Form as DesignForm;
use App\Livewire\Designs\Index as DesignIndex;
use App\Livewire\Designs\Show as DesignShow;
use App\Livewire\Leads\Form as LeadForm;
use App\Livewire\Leads\Index as LeadIndex;
use App\Livewire\Leads\Show as LeadShow;
use App\Livewire\Projects\Form as ProjectForm;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Projects\Show as ProjectShow;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\SiteVisits\Form as SiteVisitForm;
use App\Livewire\SiteVisits\Index as SiteVisitIndex;
use App\Livewire\SiteVisits\Show as SiteVisitShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff ("web" guard) routes
|--------------------------------------------------------------------------
|
| Every module built from Phase 2 onward registers its routes here (or in
| its own routes/<module>.php required from this file, once the route list
| grows large enough to split). Client and vendor portals are entirely
| separate files — see bootstrap/app.php.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::post('/logout', LogoutController::class)->name('logout')->defaults('guard', 'web');

Route::middleware(['auth:web', 'user_type:staff'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    Route::middleware('can:manage_settings')->group(function () {
        Route::get('/settings', SettingsIndex::class)->name('settings.index');
    });

    // Phase 2: CRM — Leads, Clients, Site Visits (ARCHITECTURE.md §6/§7).
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', LeadIndex::class)->name('index')->middleware('can:view_leads');
        Route::get('/create', LeadForm::class)->name('create')->middleware('can:create_leads');
        Route::get('/{lead}/edit', LeadForm::class)->name('edit')->middleware('can:edit_leads');
        Route::get('/{lead}', LeadShow::class)->name('show')->middleware('can:view_leads');
    });

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', ClientIndex::class)->name('index')->middleware('can:view_clients');
        Route::get('/create', ClientForm::class)->name('create')->middleware('can:create_clients');
        Route::get('/{client}/edit', ClientForm::class)->name('edit')->middleware('can:edit_clients');
        Route::get('/{client}', ClientShow::class)->name('show')->middleware('can:view_clients');
    });

    Route::prefix('site-visits')->name('site-visits.')->group(function () {
        Route::get('/', SiteVisitIndex::class)->name('index')->middleware('can:view_site_visits');
        Route::get('/create', SiteVisitForm::class)->name('create')->middleware('can:create_site_visits');
        Route::get('/{siteVisit}/edit', SiteVisitForm::class)->name('edit')->middleware('can:edit_site_visits');
        Route::get('/{siteVisit}', SiteVisitShow::class)->name('show')->middleware('can:view_site_visits');
    });

    // Phase 3: Projects, Milestones, Tasks (ARCHITECTURE.md §6.3).
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', ProjectIndex::class)->name('index')->middleware('can:view_projects');
        Route::get('/create', ProjectForm::class)->name('create')->middleware('can:create_projects');
        Route::get('/{project}/edit', ProjectForm::class)->name('edit')->middleware('can:edit_projects');
        Route::get('/{project}', ProjectShow::class)->name('show')->middleware('can:view_projects');
    });

    // Phase 4: Design — revisions and client approvals (ARCHITECTURE.md §3.5).
    Route::prefix('designs')->name('designs.')->group(function () {
        Route::get('/', DesignIndex::class)->name('index')->middleware('can:view_designs');
        Route::get('/create', DesignForm::class)->name('create')->middleware('can:create_designs');
        Route::get('/{design}/edit', DesignForm::class)->name('edit')->middleware('can:edit_designs');
        Route::get('/{design}', DesignShow::class)->name('show')->middleware('can:view_designs');
    });
});
