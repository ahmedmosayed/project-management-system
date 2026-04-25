# Project Management System

A lightweight project management application built with Laravel 12 and Livewire 3. It supports project creation, task tracking, role-based access control, reports, attachments, and activity management.

## What this project does

- Manage projects with name, description, start/end dates, budget, manager, and status.
- Create, update, delete, and close projects.
- Track tasks with assignment, deadlines, priorities, status, comments, and attachments.
- Use a task board for visual task management.
- Restrict access by roles and permissions (`admin`, `project-manager`, `team-member`).
- Show reports and manager briefing pages for authorized users.

## Main structure

- `app/Models` - database models like `Project`, `Task`, and `User`.
- `app/Livewire` - Livewire components for dynamic pages and forms.
- `app/Enums/ProjectStatus.php` - project status definitions.
- `routes/web.php` - application routes.
- `config/permission.php` - role and permission settings.
- `resources/views` - Blade templates and UI views.

## Technologies

- Laravel 12
- Livewire 3
- Volt
- Spatie Laravel Permission
- Bootstrap
- Vite

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

Then start the app with `php artisan serve` or use Vite development mode.

## Notes

Access and available actions depend on the signed-in user's role and permissions.
