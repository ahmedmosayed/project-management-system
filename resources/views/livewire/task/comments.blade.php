<div wire:poll.5s>
    <div class="fw-semibold small mb-2">{{ __('Comments') }}</div>
    <div class="mb-2" style="max-height: 200px; overflow-y: auto;">
        @forelse ($comments as $c)
            <div class="small border-bottom py-2" wire:key="c-{{ $c->id }}">
                <span class="fw-medium">{{ $c->user?->name ?? '—' }}</span>
                <span class="text-muted">{{ $c->created_at->diffForHumans() }}</span>
                <div class="mt-1">{{ $c->body }}</div>
            </div>
        @empty
            <p class="text-muted small mb-0">{{ __('No comments yet.') }}</p>
        @endforelse
    </div>
    <div class="input-group input-group-sm">
        <textarea class="form-control" rows="2" wire:model="body" placeholder="{{ __('Add a comment…') }}"></textarea>
        <button type="button" class="btn btn-outline-primary" wire:click="post">{{ __('Post') }}</button>
    </div>
    @error('body') <div class="text-danger small">{{ $message }}</div> @enderror
</div>
