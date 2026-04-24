<div wire:poll.45s class="dashboard-stats">
    @php
        $statusCounts = $stats['status_counts'];
        $statusTotal = max(1, array_sum($statusCounts));
        $statusStyles = [
            'todo' => ['bg' => 'bg-secondary', 'label' => __('To do')],
            'in_progress' => ['bg' => 'bg-primary', 'label' => __('In progress')],
            'review' => ['bg' => 'bg-info', 'label' => __('Review')],
            'done' => ['bg' => 'bg-success', 'label' => __('Done')],
            'blocked' => ['bg' => 'bg-warning text-dark', 'label' => __('Blocked')],
        ];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Total tasks') }}</div>
                    <div class="display-6 fw-semibold">{{ number_format($stats['total_tasks']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100 border-success border-opacity-25">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Completed') }}</div>
                    <div class="display-6 fw-semibold text-success">{{ number_format($stats['completed_tasks']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100 border-danger border-opacity-25">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Delayed') }}</div>
                    <div class="display-6 fw-semibold text-danger">{{ number_format($stats['delayed_tasks_count']) }}</div>
                    <div class="small text-muted">{{ __('Past deadline, not done') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">{{ __('Overall progress') }}</div>
                    <div class="fs-2 fw-semibold">{{ number_format($stats['overall_progress'], 1) }}%</div>
                    <div class="progress mt-2" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ $stats['overall_progress'] }}%"
                             aria-valuenow="{{ $stats['overall_progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">{{ __('Tasks by status') }}</div>
                <div class="card-body">
                    <div class="progress rounded-pill overflow-visible mb-3" style="height: 1.25rem;">
                        @foreach ($statusStyles as $key => $meta)
                            @php $pct = ($statusCounts[$key] ?? 0) / $statusTotal * 100; @endphp
                            @if ($pct > 0)
                                <div class="progress-bar {{ $meta['bg'] }}"
                                     role="progressbar"
                                     style="width: {{ $pct }}%"
                                     title="{{ $meta['label'] }}: {{ $statusCounts[$key] ?? 0 }}"></div>
                            @endif
                        @endforeach
                    </div>
                    <ul class="list-unstyled small mb-0">
                        @foreach ($statusStyles as $key => $meta)
                            <li class="d-flex justify-content-between py-1 border-bottom border-light">
                                <span><span class="badge rounded-pill {{ $meta['bg'] }}">&nbsp;</span> {{ $meta['label'] }}</span>
                                <span class="fw-medium">{{ number_format($statusCounts[$key] ?? 0) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">{{ __('Progress by project') }}</div>
                <div class="card-body">
                    @forelse ($stats['project_progress'] as $row)
                        <div class="mb-3" wire:key="proj-bar-{{ $row['id'] }}">
                            <div class="d-flex justify-content-between small mb-1">
                                <a href="{{ route('projects.show', $row['id']) }}" wire:navigate class="text-decoration-none text-truncate me-2">{{ $row['name'] }}</a>
                                <span class="text-muted text-nowrap">{{ $row['completed_tasks'] }}/{{ $row['total_tasks'] }} · {{ number_format($row['progress'], 1) }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $row['progress'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No project data for your scope yet.') }}</p>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold">{{ __('Delayed tasks') }}</span>
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary" wire:navigate>{{ __('All tasks') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Task') }}</th>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Deadline') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Open') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stats['delayed_tasks'] as $task)
                        <tr wire:key="delayed-{{ $task->id }}">
                            <td class="fw-medium">{{ $task->title }}</td>
                            <td>
                                <a href="{{ route('projects.show', $task->project_id) }}" wire:navigate>{{ $task->project?->name ?? '—' }}</a>
                            </td>
                            <td class="text-danger small">{{ $task->deadline?->format('Y-m-d') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $task->status }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('projects.show', $task->project_id) }}" wire:navigate>{{ __('Project') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">{{ __('No delayed tasks in your scope.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0">
        {{ __('Stats refresh every 45s and when tasks change on this page.') }}
    </p>
</div>
