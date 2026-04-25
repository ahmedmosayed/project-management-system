<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ __('Admin Dashboard') }}</h1>
    </div>

    <!-- Global Statistics -->
    <livewire:admin.stats />

    <div class="row g-4 mt-1">
        <!-- Global Projects Monitor (Briefing) -->
        <div class="col-xl-8">
            <h2 class="h5 mb-3">{{ __('System-wide Projects Monitor') }}</h2>
            <livewire:briefing.index />
        </div>

        <!-- Global Activity Feed -->
        <div class="col-xl-4">
            <h2 class="h5 mb-3">{{ __('Global Activity Feed') }}</h2>
            <livewire:activity.feed />
        </div>
    </div>
</div>
