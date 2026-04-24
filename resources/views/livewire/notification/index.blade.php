<div class="dropdown" wire:poll.60s>
    <button class="btn btn-outline-secondary btn-sm position-relative" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="{{ __('Notifications') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.664 2.258h10c.288-.692.502-1.49.664-2.258C12.985 8.195 12 6.088 12 4a4 4 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4-4.9V1a1 1 0 0 1 1-1h2a1 1 0 0 1 1v.09c2.282.46 4 2.48 4 4.9 0 .88.32 4.2 1.22 6z"/>
        </svg>
        @if ($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                <span class="visually-hidden">{{ __('Unread notifications') }}</span>
            </span>
        @endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" style="width: min(100vw - 2rem, 22rem); max-height: 22rem; overflow-y: auto;">
        <li class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-semibold small">{{ __('Notifications') }}</span>
            @if ($unreadCount > 0)
                <button type="button" class="btn btn-link btn-sm p-0 small" wire:click="markAllAsRead">{{ __('Mark all read') }}</button>
            @endif
        </li>
        @forelse ($notifications as $n)
            @php
                $data = $n->data;
                $kind = $data['kind'] ?? null;
                $href = match ($kind) {
                    'project_completed' => isset($data['project_id']) ? (auth()->user()->can('manage-projects') ? route('projects.show', $data['project_id']) : route('tasks.index')) : null,
                    'task_assigned', 'task_status_changed', 'task_completed', 'task_deadline_reminder' => isset($data['project_id']) ? (auth()->user()->can('manage-projects') ? route('projects.show', $data['project_id']) : route('tasks.index')) : null,
                    default => null,
                };
                $message = $data['message'] ?? $data['title'] ?? __('Notification');
            @endphp
            <li wire:key="notif-{{ $n->id }}">
                <div class="dropdown-item-text py-2 px-3 border-bottom border-light">
                    <div class="small">{{ $message }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                        @if ($href)
                            <a href="{{ $href }}" class="btn btn-sm btn-outline-primary py-0" wire:navigate wire:click.stop>{{ __('Open') }}</a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0" wire:click="markAsRead('{{ $n->id }}')">{{ __('Mark read') }}</button>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $n->created_at->diffForHumans() }}</div>
                </div>
            </li>
        @empty
            <li>
                <span class="dropdown-item-text text-muted small py-3 px-3 d-block text-center">{{ __('No unread notifications.') }}</span>
            </li>
        @endforelse
    </ul>
</div>
