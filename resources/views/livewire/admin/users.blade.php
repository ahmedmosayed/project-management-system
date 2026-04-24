<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">{{ __('User Management') }}</h1>
                <button type="button" class="btn btn-primary" wire:click="create">
                    <i class="fas fa-plus me-1"></i>{{ __('Add User') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('Search users...') }}">
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('name')" class="sortable">
                                {{ __('Name') }}
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('email')" class="sortable">
                                {{ __('Email') }}
                                @if($sortField === 'email')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th>{{ __('Roles') }}</th>
                            <th wire:click="sortBy('created_at')" class="sortable">
                                {{ __('Created') }}
                                @if($sortField === 'created_at')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <div class="avatar-initial rounded-circle bg-primary text-white">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            @if($user->id === auth()->id())
                                                <small class="text-muted">{{ __('(You)') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge bg-secondary me-1">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted small">{{ __('No roles') }}</span>
                                    @endforelse
                                </td>
                                <td>{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                wire:click="edit({{ $user->id }})"
                                                title="{{ __('Edit User') }}">
                                            {{ __('Edit') }}
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    wire:click="confirmDelete({{ $user->id }})"
                                                    title="{{ __('Delete User') }}">
                                                {{ __('Delete') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <div>{{ __('No users found') }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade {{ $showCreateModal || $showEditModal ? 'show' : '' }}"
         style="{{ $showCreateModal || $showEditModal ? 'display: block;' : '' }}"
         tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingUser ? __('Edit User') : __('Create User') }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="resetForm"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Name') }}</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       wire:model="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       wire:model="email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Password') }}</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       wire:model="password"
                                       placeholder="{{ $editingUser ? __('Leave blank to keep current') : '' }}">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Confirm Password') }}</label>
                                <input type="password" class="form-control"
                                       wire:model="password_confirmation">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Roles') }}</label>
                                <div class="row g-2">
                                    @foreach($roles as $role)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="selectedRoles"
                                                       value="{{ $role->name }}"
                                                       id="role-{{ $role->name }}">
                                                <label class="form-check-label" for="role-{{ $role->name }}">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedRoles')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetForm">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $editingUser ? __('Update User') : __('Create User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade {{ $showDeleteModal ? 'show' : '' }}"
         style="{{ $showDeleteModal ? 'display: block;' : '' }}"
         tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Delete User') }}</h5>
                    <button type="button" class="btn-close" wire:click="resetForm"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this user?') }}</p>
                    @if($deletingUser)
                        <div class="alert alert-warning">
                            <strong>{{ $deletingUser->name }}</strong> ({{ $deletingUser->email }})
                        </div>
                        <p class="text-muted small mb-0">
                            {{ __('This action cannot be undone.') }}
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="resetForm">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="delete">
                        {{ __('Delete User') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Backdrop -->
    @if($showCreateModal || $showEditModal || $showDeleteModal)
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <strong class="me-auto">{{ __('Success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    {{ session('message') }}
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <strong class="me-auto">{{ __('Warning') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    <style>
    .sortable {
        cursor: pointer;
        user-select: none;
    }
    .sortable:hover {
        background-color: rgba(0,0,0,0.05);
    }
    .avatar {
        width: 40px;
        height: 40px;
    }
    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
    }
    </style>
</div>
