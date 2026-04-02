<?php

namespace App\Livewire\Admin;

use App\Models\Vehicle;
use App\Models\Manager;
use Livewire\Component;

class VehicleCreate extends Component
{
    public $registration_card, $license_plate, $make, $manager_id, $type;

    protected $rules = [
        'registration_card' => 'required|string|unique:vehicles,registration_card',
        'license_plate'     => 'required|string|unique:vehicles,license_plate',
        'make'              => 'required|string',
        'manager_id'        => 'required|exists:managers,id',
        'type'              => 'required|in:truck,van,motorcycle,car',
    ];

    public function save()
    {
        $this->validate();
        Vehicle::create([
            'registration_card' => $this->registration_card,
            'license_plate'     => $this->license_plate,
            'make'              => $this->make,
            'manager_id'        => $this->manager_id,
            'type'              => $this->type,
        ]);

        return redirect()->route('admin.vehicules.index');
    }

    public function render()
    {
        return view('livewire.admin.vehicules.create', [
            'managers' => Manager::with('user')->get()
        ]);
    }
}