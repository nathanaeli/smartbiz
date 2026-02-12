<?php

namespace App\Livewire;

use Livewire\Component;

class ShowDuka extends Component
{
    public $duka;

    public function mount($duka)
    {
        $this->duka = $duka;
    }

    public function render()
    {
        return view('livewire.show-duka');
    }
}
