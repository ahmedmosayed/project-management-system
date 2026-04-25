<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
        <h1 class="h4 mb-0">{{ __('Projects') }}</h1>
        @role('admin')
            <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm" wire:navigate>{{ __('Create project') }}</a>
        @endrole
    </div>

    @if ($bannerMessage)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $bannerMessage }}
            <button type="button" class="btn-close" wire:click="dismissBanner" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label" for="search">{{ __('Search by name') }}</label>
                    <input id="search" type="search" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('Name…') }}">
                </div>
                @role('admin')
                    <div class="col-md-6">
                        <label class="form-label" for="managerFilter">{{ __('Manager') }}</label>
                        <select id="managerFilter" class="form-select" wire:model.live="managerFilter">
                            <option value="">{{ __('All managers') }}</option>
                            @foreach ($managerOptions as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endrole
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Manager') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Dates') }}</th>
                        @can('manage-projects')
                        <th scope="col" class="text-end">{{ __('Budget') }}</th>
                        @endcan
                        <th scope="col" class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr wire:key="project-{{ $project->id }}">
                            <td class="fw-medium">{{ $project->name }}</td>
                            <td>{{ $project->manager?->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $project->status->label() }}</span></td>
                            <td class="small text-muted">
                                @if ($project->start_date || $project->end_date)
                                    {{ $project->start_date?->format('Y-m-d') ?? '…' }}
                                    —
                                    {{ $project->end_date?->format('Y-m-d') ?? '…' }}
                                @else
                                    —
                                @endif
                            </td>
                            @can('manage-projects')
                            <td class="text-end">
                                @if ($project->budget !== null)
                                    {{ number_format((float) $project->budget, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            @endcan
                            <td class="text-end text-nowrap">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-info" wire:navigate>
                                    {{ __('Workspace') }}
                                </a>
                                @can('update', $project)
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $project->id }})">
                                    {{ __('Edit') }}
                                </button>
                                @endcan
                                @can('close', $project)
                                @if ($project->status->value !== 'closed')
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            wire:click="closeProject({{ $project->id }})"
                                            wire:confirm="{{ __('Close this project?') }}">
                                        {{ __('Close') }}
                                    </button>
                                @endif
                                @endcan
                                @can('delete', $project)
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        wire:click="deleteProject({{ $project->id }})"
                                        wire:confirm="{{ __('Delete this project permanently?') }}">
                                    {{ __('Delete') }}
                                </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('No projects found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($projects->hasPages())
            <div class="card-footer bg-white">
                {{ $projects->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @if ($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
             style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5">{{ __('Edit project') }}</h2>
                        <button type="button" class="btn-close" wire:click="closeEdit" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <form wire:submit="updateProject">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" for="edit_name">{{ __('Name') }}</label>
                                <input id="edit_name" type="text" class="form-control @error('edit_name') is-invalid @enderror" wire:model.blur="edit_name" required>
                                @error('edit_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_description">{{ __('Description') }}</label>
                                <textarea id="edit_description" class="form-control @error('edit_description') is-invalid @enderror" wire:model.blur="edit_description" rows="3"></textarea>
                                @error('edit_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="edit_start_date">{{ __('Start date') }}</label>
                                    <input id="edit_start_date" type="date" class="form-control @error('edit_start_date') is-invalid @enderror" wire:model.blur="edit_start_date">
                                    @error('edit_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="edit_end_date">{{ __('End date') }}</label>
                                    <input id="edit_end_date" type="date" class="form-control @error('edit_end_date') is-invalid @enderror" wire:model.blur="edit_end_date">
                                    @error('edit_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row g-3 mt-0">
                                <div class="col-md-6">
                                    <label class="form-label" for="edit_budget">{{ __('Budget') }}</label>
                                    <input id="edit_budget" type="text" inputmode="decimal" class="form-control @error('edit_budget') is-invalid @enderror" wire:model.blur="edit_budget">
                                    @error('edit_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="edit_manager_id">{{ __('Manager') }}</label>
                                    <select id="edit_manager_id" class="form-select @error('edit_manager_id') is-invalid @enderror" wire:model.live="edit_manager_id">
                                        @foreach ($managerOptions as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('edit_manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-0 mt-3">
                                <label class="form-label" for="edit_status">{{ __('Status') }}</label>
                                <select id="edit_status" class="form-select @error('edit_status') is-invalid @enderror" wire:model.live="edit_status">
                                    @foreach ($statuses as $case)
                                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                    @endforeach
                                </select>
                                @error('edit_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeEdit">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateProject">{{ __('Save changes') }}</span>
                                <span wire:loading wire:target="updateProject">{{ __('Saving…') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
