<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">{{ __('Dashboard') }}</h1>
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('project-manager'))
                <a href="{{ route('manager.briefing') }}" class="btn btn-primary" wire:navigate>
                    <i class="fas fa-chart-line me-1"></i>{{ __('Manager Briefing') }}
                </a>
            @endif
        </div>
    </x-slot>

    @role('admin')
        <livewire:dashboard.admin-stats />
    @endrole

    <livewire:dashboard.stats />

    <livewire:dashboard.reports />

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <p class="text-muted small mb-0">
                {{ __('Roles') }}:
                @forelse (auth()->user()->getRoleNames() as $role)
                    <span class="badge bg-secondary">{{ $role }}</span>
                @empty
                    <span class="text-muted">{{ __('none') }}</span>
                @endforelse
            </p>
        </div>
    </div>
</x-app-layout>
