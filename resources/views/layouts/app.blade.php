<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-light">
        <div class="d-flex min-vh-100">
            <aside class="app-sidebar flex-shrink-0 bg-dark text-white d-flex flex-column border-end border-secondary">
                <div class="p-3 border-bottom border-secondary">
                    <a href="{{ route('dashboard') }}" class="text-white text-decoration-none fw-semibold" wire:navigate>
                        {{ config('app.name', 'Project Management') }}
                    </a>
                </div>
                <nav class="nav flex-column p-3 gap-1 flex-grow-1">
                    <a class="nav-link text-white-50 rounded px-2 py-2 {{ request()->routeIs('dashboard') ? 'bg-secondary text-white' : '' }}"
                       href="{{ route('dashboard') }}" wire:navigate>
                        {{ __('Dashboard') }}
                    </a>
                    @canany(['manage-projects', 'view-tasks'])
                        <a class="nav-link text-white-50 rounded px-2 py-2 {{ request()->routeIs('projects.*') ? 'bg-secondary text-white' : '' }}"
                           href="{{ route('projects.index') }}" wire:navigate>
                            {{ __('Projects') }}
                        </a>
                    @endcanany
                    @can('view-tasks')
                        <a class="nav-link text-white-50 rounded px-2 py-2 {{ request()->routeIs('tasks.index') ? 'bg-secondary text-white' : '' }}"
                           href="{{ route('tasks.index') }}" wire:navigate>
                            {{ __('Tasks') }}
                        </a>
                        <a class="nav-link text-white-50 rounded px-2 py-2 {{ request()->routeIs('tasks.board') ? 'bg-secondary text-white' : '' }}"
                           href="{{ route('tasks.board') }}" wire:navigate>
                            {{ __('Task Board') }}
                        </a>
                    @endcan
                    @can('view-reports')
                        <a class="nav-link text-white-50 rounded px-2 py-2 {{ request()->routeIs('reports.*') ? 'bg-secondary text-white' : '' }}"
                           href="{{ route('reports.index') }}" wire:navigate>
                            {{ __('Reports') }}
                        </a>
                    @endcan
                    @can('manage-users')
                        <a class="nav-link text-white-50 rounded px-2 py-2 {{ request()->routeIs('admin.users') ? 'bg-secondary text-white' : '' }}"
                           href="{{ route('admin.users') }}" wire:navigate>
                            {{ __('Users') }}
                        </a>
                    @endcan
                    @role('admin|project-manager')
                        <a class="nav-link text-white-50 rounded px-2 py-2 {{ request()->routeIs('manager.briefing') ? 'bg-secondary text-white' : '' }}"
                           href="{{ route('manager.briefing') }}" wire:navigate>
                            {{ __('Manager Briefing') }}
                        </a>
                    @endrole
                </nav>
            </aside>

            <div class="app-main flex-grow-1 d-flex flex-column">
                <livewire:layout.navigation />

                @if (isset($header))
                    <header class="bg-white border-bottom py-3 px-4">
                        {{ $header }}
                    </header>
                @endif

                <main class="flex-grow-1 p-4">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
