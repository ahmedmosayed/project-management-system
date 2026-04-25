<div>
    <div class="row g-3 mb-4">
        <!-- Projects Overview -->
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Total Projects') }}</div>
                    <div class="display-6 fw-semibold">{{ $stats['total_projects'] }}</div>
                    <div class="small mt-1 text-muted">
                        <span class="text-success">{{ $stats['active_projects'] }} Active</span> | 
                        <span>{{ $stats['completed_projects'] }} Completed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Overview -->
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Total Tasks') }}</div>
                    <div class="display-6 fw-semibold">{{ $stats['total_tasks'] }}</div>
                    <div class="small mt-1 text-muted">
                        <span class="text-success">{{ $stats['completed_tasks'] }} Done</span> | 
                        <span class="text-danger">{{ $stats['delayed_tasks'] }} Delayed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Overview -->
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Total Users') }}</div>
                    <div class="display-6 fw-semibold">{{ $stats['total_users'] }}</div>
                    <div class="small mt-1 text-muted">
                        @foreach($stats['users_by_role'] as $role => $count)
                            <span class="me-2">{{ ucfirst(str_replace('-', ' ', $role)) }}: {{ $count }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Task Completion Rate -->
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Completion Rate') }}</div>
                    @php $rate = $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 1) : 0; @endphp
                    <div class="display-6 fw-semibold">{{ $rate }}%</div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
