<?php

use App\Livewire\Project\Create as ProjectCreate;
use App\Livewire\Project\Index as ProjectIndex;
use App\Livewire\Project\Show as ProjectShow;
use App\Livewire\Task\Board as TaskBoard;
use App\Livewire\Task\Create as TaskCreate;
use App\Livewire\Task\Index as TaskIndex;
use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

//    Route::middleware(['permission:manage-projects'])->group(function () {
//        Route::get('projects', ProjectIndex::class)->name('projects.index');
//        Route::get('projects/{project}', ProjectShow::class)->name('projects.show');
//    });
//
//    Route::middleware(['role:admin'])->group(function () {
//        Route::get('projects/create', ProjectCreate::class)->name('projects.create');
//    });

    Route::middleware(['permission:view-tasks|manage-projects'])->group(function () {
        Route::get('projects', ProjectIndex::class)->name('projects.index');
        Route::get('projects/create', ProjectCreate::class)->name('projects.create');
        Route::get('projects/{project}', ProjectShow::class)->name('projects.show');
    });

    Route::middleware(['permission:view-tasks|manage-projects|manage-tasks'])->group(function () {
        Route::get('tasks', TaskIndex::class)->name('tasks.index');
        Route::get('tasks/board', TaskBoard::class)->name('tasks.board');
    });

    Route::middleware(['permission:manage-projects|manage-tasks'])->group(function () {
        Route::get('tasks/create', TaskCreate::class)->name('tasks.create');
    });

    Route::get('task-attachments/{taskAttachment}/download', function (TaskAttachment $taskAttachment) {
        $task = $taskAttachment->task;
        abort_unless(
            auth()->user()->can('view', $task) || auth()->user()->can('manage-projects') || auth()->user()->can('manage-tasks'),
            403
        );
        if (! Storage::disk('public')->exists($taskAttachment->path)) {
            abort(404);
        }

        return Storage::disk('public')->download($taskAttachment->path, $taskAttachment->original_name);
    })->name('task-attachments.download');

    Route::middleware(['permission:manage-users'])->group(function () {
        Route::get('admin/users', App\Livewire\Admin\Users::class)->name('admin.users');
    });

    Route::middleware(['permission:view-reports'])->group(function () {
        Route::get('reports', App\Livewire\Reports\Index::class)->name('reports.index');
    });

    Route::middleware(['role:admin|project-manager'])->group(function () {
        Route::get('manager-briefing', App\Livewire\Briefing\Index::class)->name('manager.briefing');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
