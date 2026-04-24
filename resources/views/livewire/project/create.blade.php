<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
        <h1 class="h4 mb-0">{{ __('Create project') }}</h1>
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm" wire:navigate>{{ __('Back to list') }}</a>
    </div>

    <div class="mx-auto" style="max-width: 40rem;">
        @if ($successMessage)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $successMessage }}
                <button type="button" class="btn-close" wire:click="$set('successMessage', '')" aria-label="{{ __('Close') }}"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form wire:submit="save">
                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">{{ __('Description') }}</label>
                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" wire:model.blur="description" rows="3"></textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="start_date">{{ __('Start date') }}</label>
                            <input id="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror" wire:model.blur="start_date">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="end_date">{{ __('End date') }}</label>
                            <input id="end_date" type="date" class="form-control @error('end_date') is-invalid @enderror" wire:model.blur="end_date">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label" for="budget">{{ __('Budget') }}</label>
                            <input id="budget" type="text" inputmode="decimal" class="form-control @error('budget') is-invalid @enderror" wire:model.blur="budget" placeholder="0.00">
                            @error('budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="manager_id">{{ __('Manager') }}</label>
                            <select id="manager_id" class="form-select @error('manager_id') is-invalid @enderror" wire:model.blur="manager_id">
                                <option value="">{{ __('Select manager') }}</option>
                                @foreach ($managers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label" for="status">{{ __('Status') }}</label>
                        <select id="status" class="form-select @error('status') is-invalid @enderror" wire:model.blur="status">
                            @foreach ($statuses as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">{{ __('Save project') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
