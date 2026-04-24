<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">{{ __('User administration') }}</h1>
    </x-slot>

    <div class="alert alert-warning mb-0">
        {{ __('This page is protected by the :permission permission (typically the Admin role).', ['permission' => 'manage-users']) }}
    </div>
</x-app-layout>
