<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

class SuperAdminUsers extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);

        // Don't allow super admin to deactivate themselves
        if ($user->hasRole('superadmin') && auth()->id() === $user->id) {
            session()->flash('error', 'You cannot deactivate your own super admin account.');
            return;
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        session()->flash('success', 'User status updated successfully.');
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->roleFilter, function($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->when($this->statusFilter, function($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('status', 'active');
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('status', 'inactive');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.super-admin-users', [
            'users' => $users
        ])->layout('layouts.super-admin');
    }
}
