<?php

use App\Models\Vehicle;
use App\Models\Manager;
use Livewire\Volt\Component;

new class extends Component 
{
    public function layout()
    {
        return 'layouts.admin';
    }

    public string $registration_card = '';
    public string $license_plate = '';
    public string $make = '';
    public string $manager_id = '';
    public string $type = '';

    // Force the layout inside the rendering hook
    public function rendering($view)
    {
        $view->layout('layouts.admin');
    }

    public function save() 
    {
        $this->validate([
            'registration_card' => 'required|unique:vehicles,registration_card',
            'license_plate'     => 'required|unique:vehicles,license_plate',
            'make'              => 'required',
            'manager_id'        => 'required|exists:managers,id',
            'type'              => 'required|in:truck,van,motorcycle,car',
        ]);

        Vehicle::create([
            'registration_card' => $this->registration_card,
            'license_plate'     => $this->license_plate,
            'make'              => $this->make,
            'manager_id'        => $this->manager_id,
            'type'              => $this->type,
        ]);

        return redirect()->route('admin.vehicules.index');
    }

    public function with(): array
    {
        return [
            'managers' => Manager::with('user')->get(),
        ];
    }
}; ?>

<div class="p-8">
    <x-admin.card class="max-w-4xl mx-auto rounded-[2rem] shadow-2xl border-none p-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Add New Vehicle</h2>
            <p class="text-sm text-gray-400 uppercase tracking-widest font-bold">Fleet Management</p>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2">Vehicle Make</label>
                    <input type="text" wire:model="make" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 focus:ring-2 focus:ring-primary transition-all">
                    @error('make') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2">Vehicle Type</label>
                    <select wire:model="type" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4">
                        <option value="">Select Type</option>
                        <option value="truck">Truck</option>
                        <option value="van">Van</option>
                        <option value="motorcycle">Motorcycle</option>
                        <option value="car">Car</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2">License Plate</label>
                    <input type="text" wire:model="license_plate" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4">
                    @error('license_plate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2">Registration Card</label>
                    <input type="text" wire:model="registration_card" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4">
                    @error('registration_card') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2">Assign to Manager</label>
                    <select wire:model="manager_id" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4">
                        <option value="">Choose a manager...</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">
                                {{ $manager->user->name ?? 'Manager #' . $manager->id }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" wire:loading.attr="disabled" class="bg-primary text-white px-8 py-4 rounded-2xl font-bold uppercase text-xs shadow-lg hover:opacity-90 transition-all">
                    <span wire:loading.remove>Register Vehicle</span>
                    <span wire:loading>Processing...</span>
                </button>
            </div>
        </form>
    </x-admin.card>
</div>