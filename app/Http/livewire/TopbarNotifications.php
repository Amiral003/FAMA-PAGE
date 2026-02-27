<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Filament\Facades\Filament;

class TopbarNotifications extends Component
{
    public $notifications;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Filament::auth()->user()
            ->unreadNotifications
            ->sortByDesc('created_at');
    }

    public function markAsRead($id)
    {
        $notification = Filament::auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        $this->loadNotifications(); // recharge les notifications
    }

    public function render()
    {
        return view('livewire.topbar-notifications');
    }
}
