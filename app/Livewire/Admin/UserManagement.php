<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.livewire-layout')]
#[Title('User Management')]
class UserManagement extends Component
{
    use WithPagination;

    // Search
    #[Url(as: 'q')]
    public string $search = '';

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // State
    public ?int $editingUserId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];

        if ($this->editingUserId) {
            $rules['email'][] = 'unique:users,email,' . $this->editingUserId;
            if ($this->password) {
                $rules['password'] = ['confirmed', Password::defaults()];
            }
        } else {
            $rules['email'][] = 'unique:users,email';
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        return $rules;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingUserId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $userId;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }

            $user->save();
            session()->flash('success', 'User updated successfully.');
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            session()->flash('success', 'User created successfully.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function confirmDelete(int $userId): void
    {
        $this->deletingUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if ($this->deletingUserId) {
            User::destroy($this->deletingUserId);
            session()->flash('success', 'User deleted successfully.');
        }

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingUserId = null;
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->editingUserId = null;
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }
}
