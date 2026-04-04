<?php

use App\Models\Vehicle;
use App\Models\Manager;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public Vehicle $vehicle;

    // Form Fields
    public $make;
    public $model;
    public $license_plate;
    public $type;
    public $manager_id;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
        $this->make = $vehicle->make;
        $this->model = $vehicle->registration_card;
        $this->license_plate = $vehicle->license_plate;
        $this->type = $vehicle->type;
        $this->manager_id = $vehicle->manager_id;
    }

    public function save()
    {
        $validated = $this->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|unique:vehicles,license_plate,' . $this->vehicle->id,
            'type' => 'required|in:car,truck,van,motorcycle',
            'manager_id' => 'nullable|exists:managers,id',
        ]);

        $this->vehicle->update($validated);

        session()->flash('success', 'Vehicle updated successfully.');
        
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
    <div class="max-w-3xl mx-auto">
        {{-- Breadcrumbs / Back --}}
        <a href="{{ route('admin.vehicules.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary mb-4 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Fleet
        </a>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Vehicle</h1>
                <p class="text-sm text-gray-500">Update details for <span class="text-primary font-bold">{{ $vehicle->license_plate }}</span></p>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Make --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Vehicle Make</label>
                        <input wire:model="make" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                        @error('make') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Model --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">registration_card</label>
                        <input wire:model="model" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                        @error('model') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Plate --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">License Plate</label>
                        <input wire:model="license_plate" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-bold font-mono uppercase">
                        @error('license_plate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Type --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Vehicle Type</label>
                        <select wire:model="type" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                            <option value="car">Car</option>
                            <option value="truck">Truck</option>
                            <option value="van">Van</option>
                            <option value="motorcycle">Motorcycle</option>
                        </select>
                        @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Manager Assignment --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Assign to Manager</label>
                    <select wire:model="manager_id" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                        <option value="">Select a Manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->user->name }}</option>
                        @endforeach
                    </select>
                    @error('manager_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.vehicules.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-8 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-primary/20 hover:opacity-90 transition-all">
                    Update Vehicle
                </button>
            </div>
        </form>
    </div>
</div>