<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">{{ __('Profile') }}</h1>
    </x-slot>

    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
