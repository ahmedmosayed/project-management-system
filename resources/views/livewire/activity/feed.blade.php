<div class="card shadow-sm" wire:poll.10s>
    <div class="card-header py-2 fw-semibold small">{{ __('Activity') }}</div>
    <ul class="list-group list-group-flush small" style="max-height: 320px; overflow-y: auto;">
        @forelse ($activities as $act)
            <li class="list-group-item py-2" wire:key="act-{{ $act->id }}">
                <div>{{ $act->description }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">
                    {{ $act->causer?->name ?? __('System') }} · {{ $act->created_at->diffForHumans() }}
                    @if ($act->event)
                        <span class="badge bg-light text-dark border ms-1">{{ $act->event }}</span>
                    @endif
                </div>
            </li>
        @empty
            <li class="list-group-item text-muted">{{ __('No activity yet.') }}</li>
        @endforelse
    </ul>
</div>
