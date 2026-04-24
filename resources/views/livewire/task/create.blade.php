<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
        <h1 class="h4 mb-0">{{ __('Create task') }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm" wire:navigate>{{ __('All tasks') }}</a>
            <a href="{{ route('tasks.board') }}" class="btn btn-outline-primary btn-sm" wire:navigate>{{ __('Board') }}</a>
        </div>
    </div>

    @if ($successMessage)
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="status" aria-live="polite">
            <strong>{{ __('Done.') }}</strong> {{ $successMessage }}
            <button type="button" class="btn-close" wire:click="$set('successMessage', '')" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form wire:submit="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="project_id">{{ __('Project') }}</label>
                        <select id="project_id" class="form-select" wire:model.live="project_id" required>
                            <option value="">{{ __('Select project') }}</option>
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="milestone_id">{{ __('Milestone') }}</label>
                        <select id="milestone_id" class="form-select" wire:model.live="milestone_id" @disabled(! $project_id)>
                            <option value="">{{ __('Backlog') }}</option>
                            @foreach ($milestones as $m)
                                <option value="{{ $m->id }}">{{ $m->title }}</option>
                            @endforeach
                        </select>
                        @error('milestone_id') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="parent_id">{{ __('Parent task') }}</label>
                        <select id="parent_id" class="form-select" wire:model="parent_id" @disabled(! $project_id)>
                            <option value="">{{ __('None') }}</option>
                            @foreach ($parentTasks as $pt)
                                <option value="{{ $pt->id }}">{{ ($pt->wbs_code ? $pt->wbs_code.' — ' : '').$pt->title }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="assigned_to">{{ __('Assign to') }}</label>
                        <select id="assigned_to" class="form-select" wire:model="assigned_to">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach ($teamMembers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="title">{{ __('Title') }}</label>
                        <input id="title" type="text" class="form-control" wire:model="title" required>
                        @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">{{ __('Description') }}</label>
                        <textarea id="description" class="form-control" rows="3" wire:model="description"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="status">{{ __('Status') }}</label>
                        <select id="status" class="form-select" wire:model="status">
                            <option value="todo">todo</option>
                            <option value="in_progress">in_progress</option>
                            <option value="review">review</option>
                            <option value="done">done</option>
                            <option value="blocked">blocked</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="priority">{{ __('Priority') }}</label>
                        <select id="priority" class="form-select" wire:model="priority">
                            <option value="low">low</option>
                            <option value="medium">medium</option>
                            <option value="high">high</option>
                            <option value="urgent">urgent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="deadline">{{ __('Deadline') }}</label>
                        <input id="deadline" type="date" class="form-control" wire:model="deadline">
                        @error('deadline') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="attachments">{{ __('Attachments') }}</label>
                        <input id="attachments" type="file" class="form-control" wire:model="attachments" multiple wire:loading.attr="disabled">
                        @error('attachments.*') <div class="text-danger small">{{ $message }}</div> @enderror
                        <div wire:loading wire:target="attachments" class="small text-muted mt-1">{{ __('Preparing files…') }}</div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">{{ __('Create task') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
