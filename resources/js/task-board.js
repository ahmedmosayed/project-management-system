import Sortable from 'sortablejs';

let sortableInstances = [];

function destroyBoardSortables() {
    sortableInstances.forEach((s) => s.destroy());
    sortableInstances = [];
}

function initTaskBoardSortable() {
    const root = document.getElementById('task-board-root');
    if (!root) {
        destroyBoardSortables();

        return;
    }

    const lwId = root.getAttribute('data-lw');
    if (!lwId || typeof window.Livewire === 'undefined') {
        return;
    }

    const component = window.Livewire.find(lwId);
    if (!component) {
        return;
    }

    destroyBoardSortables();

    root.querySelectorAll('.task-board-list').forEach((el) => {
        sortableInstances.push(
            Sortable.create(el, {
                group: 'kanban',
                animation: 150,
                draggable: '[data-task-id]',
                filter: '.board-empty-hint',
                async onEnd(evt) {
                    const item = evt.item;
                    const taskId = parseInt(item.dataset.taskId, 10);
                    if (!taskId) {
                        return;
                    }
                    const toCol = evt.to.closest('[data-column]')?.dataset.column;
                    const fromCol = evt.from.closest('[data-column]')?.dataset.column;
                    if (!toCol || !fromCol) {
                        return;
                    }
                    const ids = [...evt.to.querySelectorAll('[data-task-id]')]
                        .map((li) => parseInt(li.dataset.taskId, 10))
                        .filter((n) => !Number.isNaN(n));

                    if (fromCol !== toCol) {
                        await component.moveToColumn(taskId, toCol);
                    }
                    await component.syncColumnOrder(toCol, ids);
                },
            })
        );
    });
}

document.addEventListener('livewire:init', () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            queueMicrotask(() => initTaskBoardSortable());
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    queueMicrotask(() => initTaskBoardSortable());
});

document.addEventListener('livewire:navigated', () => {
    queueMicrotask(() => initTaskBoardSortable());
});
