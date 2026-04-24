<div>
    <div class="mb-4">
        <h5>{{ __('Report Summary') }}</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">{{ $report->summary_data['total_tasks'] }}</h6>
                        <p class="card-text small">{{ __('Total Tasks') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title text-success">{{ $report->summary_data['completed_tasks'] }}</h6>
                        <p class="card-text small">{{ __('Completed') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title text-warning">{{ $report->summary_data['pending_tasks'] }}</h6>
                        <p class="card-text small">{{ __('Pending') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title text-danger">{{ $report->summary_data['delayed_tasks'] }}</h6>
                        <p class="card-text small">{{ __('Delayed') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h6>{{ __('Tasks per User') }}</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Completed') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report->summary_data['tasks_per_user'] as $userData)
                        <tr>
                            <td>{{ $userData['user'] }}</td>
                            <td>{{ $userData['total'] }}</td>
                            <td>{{ $userData['completed'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h5>{{ __('Detailed Report') }}</h5>
        @foreach ($report->detailed_data as $milestone)
            <div class="mb-4">
                <h6>{{ $milestone['milestone_title'] }} <small class="text-muted">({{ $milestone['milestone_status'] }})</small></h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Task') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('Deadline') }}</th>
                                <th>{{ __('Completed') }}</th>
                                <th>{{ __('Delayed') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($milestone['tasks'] as $task)
                                <tr>
                                    <td>{{ $task['title'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $task['status'] === 'done' ? 'success' : ($task['status'] === 'in_progress' ? 'primary' : 'secondary') }}">
                                            {{ $task['status'] }}
                                        </span>
                                    </td>
                                    <td>{{ $task['priority'] }}</td>
                                    <td>{{ $task['start_date'] }}</td>
                                    <td>{{ $task['end_date'] }}</td>
                                    <td>{{ $task['completion_date'] }}</td>
                                    <td>
                                        @if ($task['delayed'])
                                            <span class="badge bg-danger">{{ __('Yes') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
