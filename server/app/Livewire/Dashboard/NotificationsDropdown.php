<?php

namespace App\Livewire\Dashboard;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public Collection|null $notifications;

    public function loadNotifications()
    {
        $this->notifications = Notification::forAdminOnly()
            ->latest()
            ->limit(6)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.notifications-dropdown');
    }
}
