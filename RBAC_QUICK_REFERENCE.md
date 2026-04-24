# RBAC Quick Reference Card

## 🔐 Three Roles Overview

```
┌─────────────┬──────────────────┬──────────────┐
│    Admin    │  Project Manager │ Team Member  │
├─────────────┼──────────────────┼──────────────┤
│ All access  │ Assigned projects│ Own tasks    │
│ No limits   │ + their tasks    │ only         │
│             │ + reports        │ + reporting  │
└─────────────┴──────────────────┴──────────────┘
```

---

## 📊 Feature Access Matrix

| Feature               | Admin |  PM  | TM  |
| --------------------- | :---: | :--: | :-: |
| Users Management      |  ✅   |  ❌  | ❌  |
| Projects (create)     |  ✅   |  ✅  | ❌  |
| Projects (edit own)   |  ✅   |  ✅  | ❌  |
| Projects (delete)     |  ✅   |  ❌  | ❌  |
| Projects (close)      |  ✅   |  ✅  | ❌  |
| Tasks (create)        |  ✅   |  ✅  | ❌  |
| Tasks (view own)      |  ✅   | ✅\* | ✅  |
| Tasks (edit own)      |  ✅   | ✅\* | ❌  |
| Tasks (change status) |  ✅   | ✅\* | ✅  |
| Comments              |  ✅   |  ✅  | ✅  |
| Attachments           |  ✅   |  ✅  | ✅  |
| Reports (view)        |  ✅   | ✅\* | ❌  |
| Activity Logs         |  ✅   |  ❌  | ❌  |
| Manager Briefing      |  ✅   |  ✅  | ❌  |
| Settings              |  ✅   |  ❌  | ❌  |

\* = Only for assigned projects/tasks

---

## 🛣️ Routes Summary

```
GET  /dashboard                      → All (auth required)
GET  /projects                       → Permission: manage-projects
GET  /projects/create               → Permission: manage-projects
GET  /projects/{id}                 → Permission: manage-projects + Policy
GET  /tasks                         → Permission: view-tasks | manage-*
GET  /tasks/create                  → Permission: manage-projects | manage-tasks
GET  /tasks/board                   → Permission: view-tasks | manage-*
GET  /reports                       → Permission: view-reports
GET  /admin/users                   → Permission: manage-users
GET  /manager-briefing              → Role: admin|project-manager
```

---

## 🎯 Data Scoping

### Admin

- Sees: Everything
- Scope: None (full access)

### Project Manager

- Sees: Assigned projects + their tasks
- Scope: `manager_id = auth()->id()`
- Reports: Only from assigned projects

### Team Member

- Sees: Assigned tasks only
- Scope: `assigned_to = auth()->id()`
- Reports: ❌ No access

---

## 🔒 Authorization Methods

### In Routes (Middleware)

```php
Route::middleware(['permission:manage-projects'])->group(...)
Route::middleware(['role:admin|project-manager'])->group(...)
```

### In Livewire (Mount)

```php
abort_unless(auth()->user()->can('manage-projects'), 403);
```

### In Livewire (Actions)

```php
abort_unless(auth()->user()->can('update', $task), 403);
```

### In Blade (Sidebar)

```blade
@can('manage-projects')
    <a href="/projects">Projects</a>
@endcan
```

---

## 📝 Permissions List

| Permission                | Admin | PM  | TM  |
| ------------------------- | :---: | :-: | :-: |
| `manage-users`            |  ✅   | ❌  | ❌  |
| `manage-projects`         |  ✅   | ✅  | ❌  |
| `manage-tasks`            |  ✅   | ✅  | ❌  |
| `view-tasks`              |  ✅   | ✅  | ✅  |
| `comment-tasks`           |  ✅   | ✅  | ✅  |
| `upload-task-attachments` |  ✅   | ✅  | ✅  |
| `view-reports`            |  ✅   | ✅  | ❌  |

---

## 🛠️ Testing Role Assignments

```bash
# Assign role (in migrations or tinker)
$user->assignRole('admin');
$user->assignRole('project-manager');
$user->assignRole('team-member');

# Check role
auth()->user()->hasRole('admin')               # bool
auth()->user()->hasRole('admin|project-manager')  # bool

# Check permission
auth()->user()->can('manage-projects')         # bool
auth()->user()->can('manage-tasks')            # bool
```

---

## 🚀 Common Scenarios

### Scenario 1: Team Member Views Tasks

1. Route: `GET /tasks` → `permission:view-tasks|manage-*` ✅
2. Mount: `abort_unless(can('view-tasks'), 403)` ✅
3. Query: `Task::visibleTo(auth()->user())` → Only own tasks ✅
4. UI: Edit/Delete buttons hidden by `@can('update', $task)` ✅

### Scenario 2: PM Creates Task in Project

1. Blade: Check if `can('manage-projects')` to show button ✅
2. Route: `GET /tasks/create` → `permission:manage-tasks` ✅
3. Livewire: `can('create', $project)` checks manager_id ✅
4. Database: Task saved with project_id & assigned_to ✅

### Scenario 3: Admin Access

1. All routes accessible (no middleware blocks) ✅
2. All policies return `true` via `before()` ✅
3. All queries return all data (no scoping) ✅
4. All UI elements visible ✅

---

## 📦 File Locations

```
Config:     config/permission.php
Seeding:    database/seeders/RolePermissionSeeder.php
Policies:   app/Policies/ProjectPolicy.php
            app/Policies/TaskPolicy.php
Models:     app/Models/Project.php (visibleTo scope)
            app/Models/Task.php (visibleTo scope)
Routes:     routes/web.php
Views:      resources/views/layouts/app.blade.php (sidebar)
Services:   app/Services/DashboardService.php
Livewire:   app/Livewire/Task/Index.php
            app/Livewire/Project/Show.php
            app/Livewire/Reports/Index.php
```

---

## ⚠️ Security Reminders

1. **Always use policies for model-specific checks**
    - `auth()->user()->can('update', $task)`

2. **Always use scopes for list queries**
    - `Task::visibleTo(auth()->user())`

3. **Always protect routes with middleware**
    - `Route::middleware(['permission:manage-projects'])`

4. **Always hide unauthorized UI**
    - `@can('create', $project)`

5. **Never trust the frontend**
    - Authorization is server-side only
    - Blade guards are for UX, not security

---

## ✅ Checklist for New Features

- [ ] Add route with appropriate middleware
- [ ] Add Livewire mount auth check
- [ ] Add Livewire action auth checks
- [ ] Add model policy method
- [ ] Add blade @can guards
- [ ] Use visibleTo() scope in queries
- [ ] Test with all three roles
- [ ] Test URL hacking (direct route access)
- [ ] Document in RBAC matrix
