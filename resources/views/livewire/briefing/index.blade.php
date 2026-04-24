<div wire:poll.45s class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">{{ __('Manager Briefing') }}</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" wire:navigate>
                        <i class="fas fa-arrow-left me-1"></i>{{ __('Back to Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @forelse ($briefing['projects'] as $project)
        <div class="card shadow-sm mb-4" wire:key="brief-card-{{ $project['name'] }}">
            <div class="card-header bg-white">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="mb-0">{{ $project['name'] }}</h5>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary fs-6">{{ $project['progress'] }}% {{ __('Progress') }}</span>
                        <div class="progress flex-grow-1" style="min-width: 200px; max-width: 300px; height: 12px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $project['progress'] }}%" aria-valuenow="{{ $project['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="fs-2 fw-bold text-primary">{{ $project['total_tasks'] }}</div>
                            <div class="text-muted">{{ __('Total Tasks') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                            <div class="fs-2 fw-bold text-success">{{ $project['completed_tasks'] }}</div>
                            <div class="text-muted">{{ __('Completed') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                            <div class="fs-2 fw-bold text-danger">{{ $project['delayed_tasks'] }}</div>
                            <div class="text-muted">{{ __('Delayed') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                            <div class="fs-2 fw-bold text-warning">{{ $project['high_priority_tasks'] }}</div>
                            <div class="text-muted">{{ __('High Priority') }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    @if ($project['next_milestone'])
                        <div class="col-md-6">
                            <div class="border-start border-primary border-4 ps-3">
                                <h6 class="text-primary mb-2">{{ __('Next Milestone') }}</h6>
                                <div class="fw-semibold">{{ $project['next_milestone']['name'] }}</div>
                                <div class="text-muted small">{{ __('Due') }}: {{ $project['next_milestone']['due_date'] }}</div>
                            </div>
                        </div>
                    @endif

                    @if ($project['upcoming_deadlines']->isNotEmpty())
                        <div class="col-md-6">
                            <div class="border-start border-warning border-4 ps-3">
                                <h6 class="text-warning mb-2">{{ __('Upcoming Deadlines') }}</h6>
                                <div class="small">
                                    @foreach ($project['upcoming_deadlines'] as $task)
                                        <div class="d-flex justify-content-between py-1 border-bottom border-light">
                                            <span class="fw-medium">{{ $task->title }}</span>
                                            <span class="badge bg-warning">{{ $task->deadline->format('M j, Y') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($project['at_risk_tasks']->isNotEmpty())
                    <div class="mt-4">
                        <h6 class="text-danger mb-3">{{ __('At-Risk Tasks') }}</h6>
                        <div>
                            @foreach ($project['at_risk_tasks'] as $task)
                                <span class="badge bg-danger fs-6 p-2 me-2 mb-2" wire:key="risk-{{ $task->id }}">{{ $task->title }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($project['team_workload']))
                    <div class="mt-4">
                        <h6 class="text-info mb-3">{{ __('Team Workload') }}</h6>
                        <div class="row g-3">
                            @foreach ($project['team_workload'] as $workload)
                                <div class="col-auto">
                                    <div class="card border-info">
                                        <div class="card-body p-3 text-center">
                                            <div class="fw-semibold">{{ $workload['user'] }}</div>
                                            <div class="fs-4 fw-bold text-info">{{ $workload['completed'] }}/{{ $workload['total'] }}</div>
                                            <div class="small text-muted">{{ __('Tasks') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="fs-1 text-muted mb-3">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h5 class="text-muted">{{ __('No Projects to Brief On') }}</h5>
                <p class="text-muted mb-0">{{ __('There are currently no projects under your management scope.') }}</p>
            </div>
        </div>
    @endforelse
</div>
