<?php
namespace App\Livewire;

use Livewire\Component;

class MapPicker extends Component
{
    public $latitude = 33.5731;  // default Morocco
    public $longitude = -7.5898;

    public function render()
    {
        return view('livewire.map-picker');
    }
}
