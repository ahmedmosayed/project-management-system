@props(['tasks', 'depth' => 0, 'milestoneId' => null])

@foreach ($tasks as $task)
    <tr wire:key="task-{{ $task->id }}">
        <td class="text-muted small" style="padding-inline-start: {{ 0.75 + $depth * 1.25 }}rem">
            <span class="font-monospace">{{ $task->wbs_code ?? '—' }}</span>
        </td>
        <td class="fw-medium">{{ $task->title }}</td>
        <td>{{ $task->assignee?->name ?? '—' }}</td>
        <td><span class="badge bg-light text-dark border">{{ $task->status }}</span></td>
        <td><span class="badge bg-secondary">{{ $task->priority }}</span></td>
        <td class="small">{{ $task->deadline?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-end text-nowrap">
            @can('update', $task)
            <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                    wire:click="openTaskModal(null, @js($milestoneId), {{ $task->id }})">
                {{ __('Subtask') }}
            </button>
            @endcan
            @can('view', $task)
            <button type="button" class="btn btn-sm btn-outline-primary py-0"
                    wire:click="openTaskModal({{ $task->id }}, @js($milestoneId), null)">
                {{ auth()->user()->can('update', $task) ? __('Edit') : __('View') }}
            </button>
            @endcan
            @can('delete', $task)
            <button type="button" class="btn btn-sm btn-outline-danger py-0"
                    wire:click="deleteTask({{ $task->id }})"
                    wire:confirm="{{ __('Delete this task?') }}">
                {{ __('Delete') }}
            </button>
            @endcan
        </td>
    </tr>
    @if ($task->treeChildren->isNotEmpty())
        @include('livewire.project.partials.task-rows', [
            'tasks' => $task->treeChildren,
            'depth' => $depth + 1,
            'milestoneId' => $milestoneId,
        ])
    @endif
@endforeach
