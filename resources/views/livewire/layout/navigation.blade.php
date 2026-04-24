<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <span class="navbar-text text-muted small d-md-none">
            {{ config('app.name') }}
        </span>
        <div class="ms-auto d-flex align-items-center gap-2">
            @auth
                <livewire:notification.index />
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span>
                        </li>
                        @foreach (auth()->user()->getRoleNames() as $roleName)
                            <li><span class="dropdown-item-text small"><span class="badge bg-secondary">{{ $roleName }}</span></span></li>
                        @endforeach
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile') }}" wire:navigate>{{ __('Profile') }}</a>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item" wire:click="logout">{{ __('Log Out') }}</button>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </div>
</nav>
