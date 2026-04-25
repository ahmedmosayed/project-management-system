<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
        <h1 class="h4 mb-0">{{ __('All tasks') }}</h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('tasks.board') }}" class="btn btn-outline-primary btn-sm" wire:navigate>{{ __('Board') }}</a>
            @canany(['manage-projects', 'manage-tasks'])
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm" wire:navigate>{{ __('Create task') }}</a>
            @endcanany
            @can('manage-projects')
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm" wire:navigate>{{ __('Projects') }}</a>
            @endcan
        </div>
    </div>

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="statusFilter">{{ __('Status') }}</label>
                    <select id="statusFilter" class="form-select" wire:model.live="statusFilter">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="todo">{{ __('todo') }}</option>
                        <option value="in_progress">{{ __('in_progress') }}</option>
                        <option value="review">{{ __('review') }}</option>
                        <option value="done">{{ __('done') }}</option>
                        <option value="blocked">{{ __('blocked') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('WBS') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Project') }}</th>
                        <th>{{ __('Milestone') }}</th>
                        <th style="min-width: 9rem;">{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Deadline') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr wire:key="task-row-{{ $task->id }}">
                            <td class="font-monospace small">{{ $task->wbs_code ?? '—' }}</td>
                            <td class="fw-medium">{{ $task->title }}</td>
                            <td>
                                @can('view', $task->project)
                                    <a href="{{ route('projects.show', $task->project_id) }}" wire:navigate>{{ $task->project?->name }}</a>
                                @else
                                    {{ $task->project?->name }}
                                @endcan
                            </td>
                            <td class="small">{{ $task->milestone?->title ?? __('Backlog') }}</td>
                            <td>
                                <label class="visually-hidden" for="status-{{ $task->id }}">{{ __('Status') }}</label>
                                <select id="status-{{ $task->id }}" class="form-select form-select-sm"
                                        onchange="Livewire.find('{{ $this->getId() }}').call('updateInlineStatus', {{ $task->id }}, this.value)">
                                    <option value="todo" @selected($task->status === 'todo')>todo</option>
                                    <option value="in_progress" @selected($task->status === 'in_progress')>in_progress</option>
                                    <option value="review" @selected($task->status === 'review')>review</option>
                                    <option value="done" @selected($task->status === 'done')>done</option>
                                    <option value="blocked" @selected($task->status === 'blocked')>blocked</option>
                                </select>
                            </td>
                            <td><span class="badge bg-secondary">{{ $task->priority }}</span></td>
                            <td class="small">{{ $task->deadline?->format('Y-m-d') ?? '—' }}</td>
                            <td class="text-end text-nowrap">
                                @can('view', $task)
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openEditModal({{ $task->id }})">
                                        {{ auth()->user()->can('update', $task) ? __('Edit') : __('View') }}
                                    </button>
                                @endcan
                                @can('delete', $task)
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="deleteTask({{ $task->id }})"
                                            wire:confirm="{{ __('Delete this task?') }}">
                                        {{ __('Delete') }}
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">{{ __('No tasks match your filters.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tasks->hasPages())
            <div class="card-footer bg-white">
                {{ $tasks->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @if ($editModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape.window="closeEditModal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5">{{ $canUpdateTask ? __('Edit task') : __('View task') }}</h2>
                        <button type="button" class="btn-close" wire:click="closeEditModal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <fieldset @disabled(!$canUpdateTask)>
                            <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="ed_project_id">{{ __('Project') }}</label>
                                <select id="ed_project_id" class="form-select" wire:model.live="ed_project_id" disabled>
                                    @foreach ($projectsForEdit as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="ed_milestone_id">{{ __('Milestone') }}</label>
                                <select id="ed_milestone_id" class="form-select" wire:model.live="ed_milestone_id">
                                    <option value="">{{ __('Backlog') }}</option>
                                    @foreach ($milestonesForEdit as $m)
                                        <option value="{{ $m->id }}">{{ $m->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="ed_parent_id">{{ __('Parent task') }}</label>
                                <select id="ed_parent_id" class="form-select" wire:model="ed_parent_id">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($parentTasksForEdit as $pt)
                                        <option value="{{ $pt->id }}">{{ ($pt->wbs_code ? $pt->wbs_code.' — ' : '').$pt->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="ed_title">{{ __('Title') }}</label>
                                <input id="ed_title" type="text" class="form-control" wire:model="ed_title">
                                @error('ed_title') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="ed_description">{{ __('Description') }}</label>
                                <textarea id="ed_description" class="form-control" rows="3" wire:model="ed_description"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="ed_status">{{ __('Status') }}</label>
                                <select id="ed_status" class="form-select" wire:model="ed_status">
                                    <option value="todo">todo</option>
                                    <option value="in_progress">in_progress</option>
                                    <option value="review">review</option>
                                    <option value="done">done</option>
                                    <option value="blocked">blocked</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="ed_priority">{{ __('Priority') }}</label>
                                <select id="ed_priority" class="form-select" wire:model="ed_priority">
                                    <option value="low">low</option>
                                    <option value="medium">medium</option>
                                    <option value="high">high</option>
                                    <option value="urgent">urgent</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="ed_deadline">{{ __('Deadline') }}</label>
                                <input id="ed_deadline" type="date" class="form-control" wire:model="ed_deadline">
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeEditModal">{{ __('Cancel') }}</button>
                        @if($canUpdateTask)
                            <button type="button" class="btn btn-primary" wire:click="saveEdit">{{ __('Save') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
