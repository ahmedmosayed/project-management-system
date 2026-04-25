<div wire:poll.45s class="mb-4">
    <h2 class="h5 mb-3 text-muted"><i class="fas fa-crown me-2 text-warning"></i>{{ __('Admin Overview') }}</h2>
    <div class="row g-3">
        <!-- Projects Column -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Total Projects') }}</div>
                    <div class="display-6 fw-semibold">{{ $stats['total_projects'] }}</div>
                    <div class="small mt-1 text-muted d-flex justify-content-between">
                        <span class="text-success">{{ $stats['active_projects'] }} {{ __('Active') }}</span>
                        <span>{{ $stats['completed_projects'] }} {{ __('Completed') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Column -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Total Users') }}</div>
                    <div class="display-6 fw-semibold">{{ $stats['total_users'] }}</div>
                    <div class="small mt-1 text-muted">
                        {{ __('Registered users across all roles') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Total Column -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Total Tasks') }}</div>
                    <div class="display-6 fw-semibold">{{ $stats['total_tasks'] }}</div>
                    <div class="small mt-1 text-muted d-flex justify-content-between">
                        <span class="text-success">{{ $stats['completed_tasks'] }} {{ __('Completed') }}</span>
                        <span class="text-secondary">{{ $stats['pending_tasks'] }} {{ __('Pending') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delayed Tasks Column -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Delayed Tasks') }}</div>
                    <div class="display-6 fw-semibold text-danger">{{ $stats['delayed_tasks'] }}</div>
                    <div class="small mt-1 text-muted">
                        {{ __('Past deadline, not done') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Overview Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="h5 mb-3 text-muted"><i class="fas fa-project-diagram me-2 text-primary"></i>{{ __('Projects Overview') }}</h2>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold">{{ $stats['active_projects'] }}</div>
                    <div class="small text-uppercase mt-1">{{ __('Active Projects') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold">{{ $stats['delayed_projects'] }}</div>
                    <div class="small text-uppercase mt-1">{{ __('Delayed Projects') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold">{{ $stats['completed_projects'] }}</div>
                    <div class="small text-uppercase mt-1">{{ __('Completed Projects') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 bg-info text-white h-100">
                <div class="card-body text-center">
                    @php $successRate = $stats['total_projects'] > 0 ? round(($stats['completed_projects'] / $stats['total_projects']) * 100, 1) : 0; @endphp
                    <div class="display-6 fw-bold">{{ $successRate }}%</div>
                    <div class="small text-uppercase mt-1">{{ __('Overall Success Rate') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Activity Section -->
    <div class="row mt-4 mb-2">
        <div class="col-12">
            <h2 class="h5 mb-3 text-muted"><i class="fas fa-bolt me-2 text-warning"></i>{{ __('System Activity (Recent)') }}</h2>
        </div>
        
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="list-group list-group-flush">
                    <!-- Last Task -->
                    <div class="list-group-item p-3 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-semibold">{{ __('Last Task Created') }}</h6>
                            <p class="mb-0 text-muted small">
                                @if($stats['last_task'])
                                    <span class="fw-medium">{{ $stats['last_task']->title }}</span> 
                                    - {{ $stats['last_task']->created_at->diffForHumans() }}
                                @else
                                    {{ __('No tasks yet.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Last Project -->
                    <div class="list-group-item p-3 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-semibold">{{ __('Last Project Created') }}</h6>
                            <p class="mb-0 text-muted small">
                                @if($stats['last_project'])
                                    <span class="fw-medium">{{ $stats['last_project']->name }}</span> 
                                    - {{ $stats['last_project']->created_at->diffForHumans() }}
                                @else
                                    {{ __('No projects yet.') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Last User -->
                    <div class="list-group-item p-3 d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 me-3">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-semibold">{{ __('Last User Registered') }}</h6>
                            <p class="mb-0 text-muted small">
                                @if($stats['last_user'])
                                    <span class="fw-medium">{{ $stats['last_user']->name }}</span> ({{ $stats['last_user']->email }})
                                    - {{ $stats['last_user']->created_at->diffForHumans() }}
                                @else
                                    {{ __('No users yet.') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Last Notification -->
                    <div class="list-group-item p-3 d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-semibold">{{ __('Last Notification') }}</h6>
                            <p class="mb-0 text-muted small">
                                @if($stats['last_notification'])
                                    @php
                                        $notifData = json_decode($stats['last_notification']->data, true);
                                    @endphp
                                    <span class="fw-medium">{{ $notifData['message'] ?? $notifData['title'] ?? 'System Notification' }}</span>
                                    - {{ \Carbon\Carbon::parse($stats['last_notification']->created_at)->diffForHumans() }}
                                @else
                                    {{ __('No notifications yet.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
