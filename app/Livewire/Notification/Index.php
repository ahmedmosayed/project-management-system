<?php

namespace App\Livewire\Notification;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public int $tick = 0;

    #[On('task-updated')]
    #[On('project-updated')]
    public function bumpFromDomainEvents(): void
    {
        $this->tick++;
    }

    public function markAsRead(string $id): void
    {
        $notification = Auth::user()->unreadNotifications()->where('id', $id)->first();
        $notification?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->unreadNotifications()->latest()->limit(20)->get();

        return view('livewire.notification.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
