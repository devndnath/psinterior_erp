<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Staff-side only (embedded in layouts/admin.blade.php's topbar via
 * resources/views/components/topbar.blade.php). Database-channel
 * notifications only — see app/Notifications/*. Not used anywhere in
 * the Client/Vendor portal layouts, which stay deliberately simpler.
 */
class Bell extends Component
{
    public function markAsRead(string $notificationId): void
    {
        Auth::user()->notifications()->whereKey($notificationId)->first()?->markAsRead();
    }

    /**
     * Marks one notification read, then navigates to the record it's
     * about — the "click a notification to go to it" behaviour.
     */
    public function markAsReadAndGo(string $notificationId, string $url): void
    {
        $this->markAsRead($notificationId);

        $this->redirect($url, navigate: true);
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.notifications.bell', [
            'unreadCount' => $user->unreadNotifications()->count(),
            'recent' => $user->notifications()->latest()->limit(10)->get(),
        ]);
    }
}
