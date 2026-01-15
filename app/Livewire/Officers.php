<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Officers extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showAddModal = false;

    public $name, $email, $phone, $password;

    public function resetForm()
    {
        $this->reset(['name','email','phone','password']);
    }

    public function saveOfficer()
    {
        $this->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone,
            'password' => Hash::make($this->password),
        ]);

        $this->resetForm();
        $this->showAddModal = false;

        session()->flash('success', 'Officer registered successfully.');
    }

    public function render()
    {
        return view('livewire.officers', [
            'officers' => User::orderBy('id','desc')->paginate(10)
        ]);
    }
}
