<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">{{ __('Manager briefing') }}</h1>
    </x-slot>

    <div class="alert alert-primary mb-0">
        {{ __('This page is protected by the :middleware middleware (roles: admin or project-manager).', ['middleware' => 'role']) }}
    </div>
</x-app-layout>
