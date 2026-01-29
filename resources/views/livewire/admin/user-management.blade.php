<div>
    {{-- Page Header --}}
    <x-admin.page-header title="User Management" subtitle="Manage all users in the system">
        <x-slot:actions>
            <x-admin.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Add User
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <x-admin.alert variant="success" title="Success!" class="mb-4">
            {{ session('success') }}
        </x-admin.alert>
    @endif

    @if (session('error'))
        <x-admin.alert variant="danger" title="Error!" class="mb-4">
            {{ session('error') }}
        </x-admin.alert>
    @endif

    {{-- Users Table Card --}}
    <div class="modern-card">
        {{-- Search and Filters --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">All Users</h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text" style="background: var(--input-bg); border-color: var(--border-color);">
                    <i class="fas fa-search" style="color: var(--text-muted);"></i>
                </span>
                <input type="text" class="form-control" placeholder="Search users..."
                    wire:model.live.debounce.300ms="search" style="border-left: none;">
            </div>
        </div>

        {{-- Users Table --}}
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">{{ $user->initials() }}</div>
                                    <div>
                                        <div class="fw-semibold" style="color: var(--text-primary);">{{ $user->name }}</div>
                                        <small class="text-muted">ID: {{ $user->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="color: var(--text-secondary);">{{ $user->email }}</td>
                            <td class="text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($user->email_verified_at)
                                    <x-admin.badge variant="success" icon="fas fa-check-circle">Verified</x-admin.badge>
                                @else
                                    <x-admin.badge variant="warning" icon="fas fa-clock">Pending</x-admin.badge>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn action-btn-edit" wire:click="openEditModal({{ $user->id }})"
                                        title="Edit user">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn action-btn-delete" wire:click="confirmDelete({{ $user->id }})"
                                        title="Delete user">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-0">No users found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        {{ $editingUserId ? 'Edit User' : 'Create New User' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            wire:model="name" placeholder="Enter full name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span
                                style="color: var(--danger-color);">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            wire:model="email" placeholder="Enter email address">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password
                            @if (!$editingUserId)
                                <span style="color: var(--danger-color);">*</span>
                            @else
                                <small class="text-muted">(leave blank to keep current)</small>
                            @endif
                        </label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            wire:model="password"
                            placeholder="{{ $editingUserId ? 'Enter new password' : 'Enter password' }}">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                            wire:model="password_confirmation" placeholder="Confirm password">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <x-admin.button type="button" variant="outline" wire:click="closeModal">
                            Cancel
                        </x-admin.button>
                        <x-admin.button type="submit" variant="primary">
                            {{ $editingUserId ? 'Update User' : 'Create User' }}
                        </x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-admin.confirm-modal
        :show="$showDeleteModal"
        title="Confirm Delete"
        message="Are you sure you want to delete this user? This action cannot be undone."
        on-confirm="deleteUser"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    >
        <x-slot:confirmButton>
            <i class="fas fa-trash-alt me-2"></i>Delete User
        </x-slot:confirmButton>
    </x-admin.confirm-modal>
</div>
