<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom">
        <div>
            <a href="{{ route('projects.index') }}" class="small text-decoration-none" wire:navigate>← {{ __('Projects') }}</a>
            <h1 class="h4 mb-0 mt-1">{{ $project->name }}</h1>
            <p class="text-muted small mb-0">{{ __('Manager') }}: {{ $project->manager?->name ?? '—' }}</p>
            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                <span class="small text-muted">{{ __('Progress') }}</span>
                <div class="progress flex-grow-1" style="min-width: 120px; max-width: 280px; height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $project->progressPercent(auth()->user()) }}%" aria-valuenow="{{ $project->progressPercent(auth()->user()) }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <span class="small fw-medium">{{ number_format($project->progressPercent(auth()->user()), 1) }}%</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @can('close', $project)
            @if ($project->status !== \App\Enums\ProjectStatus::Completed)
                <button type="button" class="btn btn-success btn-sm"
                        wire:click="markProjectCompleted"
                        wire:confirm="{{ __('Mark this project as completed? Closure summary and notifications will be generated.') }}">
                    {{ __('Mark project completed') }}
                </button>
            @endif
            @endcan
            @can('update', $project)
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openMilestoneModal()">
                {{ __('Add milestone') }}
            </button>
            <button type="button" class="btn btn-primary btn-sm" wire:click="openTaskModal(null, null, null)">
                {{ __('Add backlog task') }}
            </button>
            <button type="button" class="btn btn-info btn-sm" wire:click="createReportTask">
                {{ __('Generate Report') }}
            </button>
            @endcan
        </div>
    </div>

    @if ($bannerMessage)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $bannerMessage }}
            <button type="button" class="btn-close" wire:click="dismissBanner"></button>
        </div>
    @endif

    @if ($project->description)
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2 small text-muted">{{ $project->description }}</div>
        </div>
    @endif

    @can('update', $project)
    @if ($latestReport)
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2 fw-semibold">{{ __('Latest Project Report') }}</div>
            <div class="card-body">
                <livewire:project.report :report="$latestReport" :wire:key="'report-'.$latestReport->id" />
            </div>
        </div>
    @endif
    @endcan

    @if ($project->status === \App\Enums\ProjectStatus::Completed && ($project->closure_performance_notes || $project->completed_at))
        <div class="card shadow-sm border-success mb-4">
            <div class="card-header bg-success text-white py-2 fw-semibold">{{ __('Project closure') }}</div>
            <div class="card-body small">
                @if ($project->completed_at)
                    <p class="mb-1"><strong>{{ __('Completed at') }}:</strong> {{ $project->completed_at->format('Y-m-d H:i') }}</p>
                @endif
                @if ($project->closure_duration_days !== null)
                    <p class="mb-1"><strong>{{ __('Duration') }}:</strong> {{ $project->closure_duration_days }} {{ __('days from project start') }}</p>
                @endif
                @if ($project->closure_performance_notes)
                    <pre class="mb-0 small bg-light p-2 rounded border" style="white-space: pre-wrap;">{{ $project->closure_performance_notes }}</pre>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="{{ auth()->user()->can('update', $project) ? 'col-lg-8' : 'col-12' }}">
    {{-- Backlog (WBS) --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ __('Backlog & general tasks') }}</span>
            @can('update', $project)
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openTaskModal(null, null, null)">
                {{ __('Add task') }}
            </button>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('WBS') }}</th>
                        <th>{{ __('Task') }}</th>
                        <th>{{ __('Assignee') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Deadline') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backlogForest as $task)
                        @include('livewire.project.partials.task-rows', [
                            'tasks' => collect([$task]),
                            'depth' => 0,
                            'milestoneId' => null,
                        ])
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">{{ __('No backlog tasks.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Milestones --}}
    @forelse ($project->milestones as $milestone)
        <div class="card shadow-sm mb-3" wire:key="milestone-{{ $milestone->id }}">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="fw-semibold">{{ $milestone->title }}</span>
                    <span class="badge bg-light text-dark border ms-2">{{ $milestone->status }}</span>
                    @if ($milestone->due_date)
                        <span class="small text-muted ms-2">{{ __('Due') }}: {{ $milestone->due_date->format('Y-m-d') }}</span>
                    @endif
                </div>
                <div class="d-flex gap-1">
                    @can('update', $project)
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            wire:click="openTaskModal(null, {{ $milestone->id }}, null)">
                        {{ __('Add task') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            wire:click="openMilestoneModal({{ $milestone->id }})">
                        {{ __('Edit') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            wire:click="deleteMilestone({{ $milestone->id }})"
                            wire:confirm="{{ __('Delete milestone and unlink tasks?') }}">
                        {{ __('Delete') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('WBS') }}</th>
                            <th>{{ __('Task') }}</th>
                            <th>{{ __('Assignee') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Priority') }}</th>
                            <th>{{ __('Deadline') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $tree = $milestoneTrees[$milestone->id] ?? collect(); @endphp
                        @forelse ($tree as $task)
                            @include('livewire.project.partials.task-rows', [
                                'tasks' => collect([$task]),
                                'depth' => 0,
                                'milestoneId' => $milestone->id,
                            ])
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">{{ __('No tasks in this milestone.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-secondary mb-0">
            {{ __('No milestones yet. Add a milestone to structure your WBS.') }}
        </div>
    @endforelse
        </div>
        @can('update', $project)
        <div class="col-lg-4">
            <livewire:activity.feed :project-id="$project->id" :key="'activity-'.$project->id" />
        </div>
        @endcan
    </div>

    {{-- Milestone modal --}}
    @if ($milestoneModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5">{{ $editingMilestoneId ? __('Edit milestone') : __('New milestone') }}</h2>
                        <button type="button" class="btn-close" wire:click="closeMilestoneModal"></button>
                    </div>
                    <form wire:submit="saveMilestone">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Title') }}</label>
                                <input type="text" class="form-control" wire:model.blur="ms_title" required>
                                @error('ms_title') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Due date') }}</label>
                                <input type="date" class="form-control" wire:model.blur="ms_due_date">
                                @error('ms_due_date') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" wire:model.live="ms_status">
                                    <option value="pending">{{ __('Pending') }}</option>
                                    <option value="in_progress">{{ __('In progress') }}</option>
                                    <option value="completed">{{ __('Completed') }}</option>
                                    <option value="cancelled">{{ __('Cancelled') }}</option>
                                </select>
                                @error('ms_status') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeMilestoneModal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Task modal --}}
    @if ($taskModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5">
                            @if ($editingTaskId)
                                {{ $canUpdateTask ? __('Edit task') : __('View task') }}
                            @else
                                {{ __('New task') }}
                            @endif
                        </h2>
                        <button type="button" class="btn-close" wire:click="closeTaskModal"></button>
                    </div>
                    <form wire:submit="saveTask">
                        <div class="modal-body">
                            <fieldset @disabled($editingTaskId && !$canUpdateTask)>
                                <input type="hidden" wire:model="tk_parent_id">
                                <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Milestone') }}</label>
                                    @if ($editingTaskId)
                                        <p class="form-control-plaintext small mb-0">
                                            {{ $tk_milestone_id ? $project->milestones->firstWhere('id', $tk_milestone_id)?->title ?? '—' : __('Backlog') }}
                                        </p>
                                        <input type="hidden" wire:model="tk_milestone_id">
                                    @else
                                        <select class="form-select" wire:model.live="tk_milestone_id">
                                            <option value="">{{ __('Backlog (no milestone)') }}</option>
                                            @foreach ($project->milestones as $m)
                                                <option value="{{ $m->id }}">{{ $m->title }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('tk_milestone_id') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Assign to') }}</label>
                                    <select class="form-select" wire:model.live="tk_assigned_to">
                                        <option value="">{{ __('Unassigned') }}</option>
                                        @foreach ($teamMembers as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roles->pluck('name')->join(', ') }})</option>
                                        @endforeach
                                    </select>
                                    @error('tk_assigned_to') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" wire:model.blur="tk_title" required>
                                    @error('tk_title') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <textarea class="form-control" rows="2" wire:model.blur="tk_description"></textarea>
                                    @error('tk_description') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <select class="form-select" wire:model.live="tk_status">
                                        @foreach (['todo', 'in_progress', 'review', 'done', 'blocked'] as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                    @error('tk_status') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Priority') }}</label>
                                    <select class="form-select" wire:model.live="tk_priority">
                                        @foreach (['low', 'medium', 'high', 'urgent'] as $pr)
                                            <option value="{{ $pr }}">{{ $pr }}</option>
                                        @endforeach
                                    </select>
                                    @error('tk_priority') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Deadline') }}</label>
                                    <input type="date" class="form-control" wire:model.blur="tk_deadline">
                                    @error('tk_deadline') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Attach files') }}</label>
                                    <input type="file" class="form-control form-control-sm" wire:model="tk_uploads" multiple wire:loading.attr="disabled">
                                    @error('tk_uploads.*') <div class="text-danger small">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="tk_uploads" class="small text-muted">{{ __('Preparing files…') }}</div>
                                </div>
                            </fieldset>
                            @if ($tk_parent_id)
                                <p class="small text-muted mt-2 mb-0">{{ __('Subtask of #:id', ['id' => $tk_parent_id]) }}</p>
                            @endif
                            @if ($editingTaskId)
                                <div class="mt-3 pt-3 border-top">
                                    <livewire:task.comments :task-id="$editingTaskId" :wire:key="'tc-'.$editingTaskId" />
                                    <div class="mt-3">
                                        <livewire:task.attachments :task-id="$editingTaskId" :wire:key="'ta-'.$editingTaskId" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeTaskModal">{{ __('Cancel') }}</button>
                            @if (!$editingTaskId || $canUpdateTask)
                                <button type="submit" class="btn btn-primary">{{ __('Save task') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
