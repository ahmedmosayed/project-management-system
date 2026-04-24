<div id="task-board-root" data-lw="{{ $this->getId() }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
        <h1 class="h4 mb-0">{{ __('Task board') }}</h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm" wire:navigate>{{ __('Table') }}</a>
            @canany(['manage-projects', 'manage-tasks'])
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm" wire:navigate>{{ __('Create task') }}</a>
            @endcanany
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <label class="form-label small mb-1" for="projectFilter">{{ __('Project filter') }}</label>
            <select id="projectFilter" class="form-select form-select-sm" style="max-width: 24rem;" wire:model.live="projectFilter">
                <option value="">{{ __('All projects') }}</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100 border-secondary shadow-sm">
                <div class="card-header bg-light py-2 fw-semibold">{{ __('Pending') }}</div>
                <div class="card-body p-2">
                    <ul class="task-board-list list-unstyled mb-0 min-vh-25" style="min-height: 120px;" data-column="pending">
                        @forelse ($pendingTasks as $task)
                            <li class="mb-2" wire:key="board-pending-{{ $task->id }}" data-task-id="{{ $task->id }}">
                                <div class="card border">
                                    <div class="card-body py-2 px-3">
                                        <div class="fw-medium">{{ $task->title }}</div>
                                        <div class="small text-muted">{{ $task->project?->name }}</div>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary">{{ $task->priority }}</span>
                                            <span class="badge bg-light text-dark border">{{ $task->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-muted small board-empty-hint">{{ __('No tasks in this column.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-header bg-primary text-white py-2 fw-semibold">{{ __('In progress') }}</div>
                <div class="card-body p-2">
                    <ul class="task-board-list list-unstyled mb-0 min-vh-25" style="min-height: 120px;" data-column="in_progress">
                        @forelse ($inProgressTasks as $task)
                            <li class="mb-2" wire:key="board-ip-{{ $task->id }}" data-task-id="{{ $task->id }}">
                                <div class="card border-primary">
                                    <div class="card-body py-2 px-3">
                                        <div class="fw-medium">{{ $task->title }}</div>
                                        <div class="small text-muted">{{ $task->project?->name }}</div>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary">{{ $task->priority }}</span>
                                            <span class="badge bg-light text-dark border">{{ $task->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-muted small board-empty-hint">{{ __('No tasks in this column.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 border-success shadow-sm">
                <div class="card-header bg-success text-white py-2 fw-semibold">{{ __('Completed') }}</div>
                <div class="card-body p-2">
                    <ul class="task-board-list list-unstyled mb-0 min-vh-25" style="min-height: 120px;" data-column="completed">
                        @forelse ($completedTasks as $task)
                            <li class="mb-2" wire:key="board-done-{{ $task->id }}" data-task-id="{{ $task->id }}">
                                <div class="card border-success">
                                    <div class="card-body py-2 px-3">
                                        <div class="fw-medium">{{ $task->title }}</div>
                                        <div class="small text-muted">{{ $task->project?->name }}</div>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary">{{ $task->priority }}</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-muted small board-empty-hint">{{ __('No tasks in this column.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
