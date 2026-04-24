<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">{{ __('Reports') }}</h1>
    </x-slot>

    <div class="alert alert-secondary mb-0">
        {{ __('This page is protected by the :permission permission.', ['permission' => 'view-reports']) }}
    </div>
</x-app-layout>
