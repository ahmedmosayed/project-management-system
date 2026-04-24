<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'project-management') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center px-3">
            <div class="text-center mb-4">
                <h1 class="h3">{{ config('app.name', 'Project Management') }}</h1>
                {{-- <p class="text-muted mb-0">{{ __('Laravel Breeze + Livewire + Bootstrap + Spatie Permissions') }}</p> --}}
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                @auth
                    <a class="btn btn-primary" href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</a>
                @else
                    <a class="btn btn-primary" href="{{ route('login') }}" wire:navigate>{{ __('Log in') }}</a>
                    @if (Route::has('register'))
                        <a class="btn btn-outline-secondary" href="{{ route('register') }}" wire:navigate>{{ __('Register') }}</a>
                    @endif
                @endauth
            </div>
        </div>
    </body>
</html>
