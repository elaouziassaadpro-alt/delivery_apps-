<?php

use App\Models\User;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Manager;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public function layout()
    {
        return 'layouts.admin';
    }

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'client';

    public $photo; // Main User Profile Photo

    // Client fields
    public string $address = '';
    public $client_commission = 0;

    // Driver fields
    public string $driver_last_name = '';
    public string $driver_first_name = '';
    public string $driver_id_card_number = '';
    public $driver_id_card_file;
    public $driver_contract;
    public string $driver_phone = '';
    public string $driver_professional_email = '';
    public $driver_commission = 0;
    public $vehicle_id;

    // Manager fields
    public string $manager_last_name = '';
    public string $manager_first_name = '';
    public string $manager_id_card_number = '';

    public function with()
    {
        return [
            'vehicles' => Vehicle::all(),
        ];
    }

    protected function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:client,driver,manager'],
            'photo' => ['nullable', 'image', 'max:1024'],
        ];

        if ($this->role === 'client') {
            $rules['address'] = ['required', 'string'];
            $rules['client_commission'] = ['required', 'numeric', 'min:0'];
        }

        if ($this->role === 'driver') {
            $rules['driver_last_name'] = ['required', 'string'];
            $rules['driver_first_name'] = ['required', 'string'];
            $rules['driver_id_card_number'] = ['required', 'string'];
            $rules['driver_phone'] = ['required', 'string'];
            $rules['driver_professional_email'] = ['required', 'email'];
            $rules['driver_commission'] = ['required', 'numeric', 'min:0'];
            $rules['driver_id_card_file'] = ['nullable', 'file', 'max:2048'];
            $rules['driver_contract'] = ['nullable', 'file', 'max:2048'];
            $rules['vehicle_id'] = ['nullable', 'exists:vehicles,id'];
        }

        if ($this->role === 'manager') {

            $rules['manager_last_name'] = ['required', 'string'];
            $rules['manager_first_name'] = ['required', 'string'];
            $rules['manager_id_card_number'] = ['required', 'string'];
        }

        return $rules;
    }

    public function createUser(): void
    {
        $this->validate();

        DB::transaction(function () {
            $photoPath = $this->photo
                ? $this->photo->store('profiles', 'private')
                : null;
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'photo' => $photoPath,
        ]);

            if ($this->role === 'client') {
                Client::create([
                    'user_id' => $user->id,
                    'address' => $this->address,
                    'commission' => $this->client_commission,
                    'photo' => $photoPath, // Shared photo for now
                ]);
            }

            if ($this->role === 'driver') {
                $idCardPath = $this->driver_id_card_file ? $this->driver_id_card_file->store('drivers/id_cards', 'public') : null;
                $contractPath = $this->driver_contract ? $this->driver_contract->store('drivers/contracts', 'public') : null;

                Driver::create([
                    'user_id' => $user->id,
                    'vehicle_id' => $this->vehicle_id,
                    'last_name' => $this->driver_last_name,
                    'first_name' => $this->driver_first_name,
                    'id_card_number' => $this->driver_id_card_number,
                    'id_card_file' => $idCardPath,
                    'contract' => $contractPath,
                    'phone' => $this->driver_phone,
                    'email' => $this->driver_professional_email,
                    'commission' => $this->driver_commission,
                    'photo' => $photoPath, // Shared photo
                ]);
            }

            if ($this->role === 'manager') {
                Manager::create([
                    'user_id' => $user->id,
                    'last_name' => $this->manager_last_name,
                    'first_name' => $this->manager_first_name,
                    'id_card_number' => $this->manager_id_card_number,
                ]);
            }
        });

        session()->flash('status', 'User and profile created successfully.');
        $this->reset();
        $this->role = 'client';

    }
}; ?>

<div class="space-y-6">
    <x-slot name="header">
        Add Professional User
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
            <span>Users</span>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
            <span class="text-text-main font-medium">Create New</span>
        </span>
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <form wire:submit="createUser" class="space-y-6">
            @if (session('status'))
                <div class="p-4 bg-success/10 border border-success/20 rounded-2xl text-success flex items-center shadow-lg shadow-success/5 animate-in fade-in slide-in-from-top-4">
                    <i data-lucide="check-circle" class="w-6 h-6 me-4"></i>
                    <span class="text-base font-bold">{{ session('status') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Account Details -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center">
                        <label for="photo-upload" class="w-32 h-32 bg-gray-50 rounded-full border-4 border-white shadow-lg overflow-hidden mb-4 relative group cursor-pointer transition-all hover:border-primary/20">
                            <input wire:model="photo" id="photo-upload" type="file" class="sr-only">
                            <span class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="camera" class="w-6 h-6 mb-1"></i>
                                <span class="text-[10px] font-bold uppercase">Change</span>
                            </span>
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <i data-lucide="user" class="w-16 h-16 transition-transform group-hover:scale-110"></i>
                                </div>
                            @endif
                        </label>
                        <h3 class="font-bold text-gray-900 text-lg">Profile Photo</h3>
                        <p class="text-xs text-gray-400 text-center px-4 mt-1">Recommended: JPG, PNG below 1MB</p>
                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                    </div>

                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="key" class="w-5 h-5 me-2 text-primary"></i>
                            Account Base
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Type Selection -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">User Role</label>
                                <select wire:model.live="role" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                                    <option value="client">Client</option>
                                    <option value="driver">Driver</option>
                                    <option value="manager">Manager</option>
                                </select>

                            </div>

                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Login Email</label>
                                <input wire:model="email" type="email" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                <x-input-error :messages="$errors->get('email')" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Password</label>
                                <input wire:model="password" type="password" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                <x-input-error :messages="$errors->get('password')" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Specific Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 min-h-full">
                        <div class="mb-8 border-b border-gray-50 pb-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 line-clamp-1">
                                    {{ ucfirst($role) }} Profile Details
                                </h2>
                                <p class="text-sm text-gray-400 mt-1">Complete the professional profile for this {{ $role }}.</p>
                            </div>

                            <div class="px-4 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold uppercase tracking-widest">
                                Required Fields
                            </div>
                        </div>

                        <!-- Client Type -->
                        @if ($this->role === 'client')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Client Official Name</label>
                                    <input wire:model="name" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold" placeholder="Company or Individual Name">
                                    <x-input-error :messages="$errors->get('name')" />
                                </div>

                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Physical Address</label>
                                    <textarea wire:model="address" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold h-32" placeholder="Full delivery/billing address"></textarea>
                                    <x-input-error :messages="$errors->get('address')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Service Commission (DH)</label>
                                    <div class="relative">
                                        <span class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">
                                            DH
                                        </span>
                                        <input wire:model="client_commission" type="number" step="1" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold">
                                    </div>
                                    <x-input-error :messages="$errors->get('client_commission')" />
                                </div>
                            </div>
                        @endif

                        <!-- Driver Type -->
                        @if ($this->role === 'driver')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Last Name</label>
                                    <input wire:model="driver_last_name" wire:keyup="$set('name', driver_first_name + ' ' + driver_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl" placeholder="Doe">
                                    <x-input-error :messages="$errors->get('driver_last_name')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">First Name</label>
                                    <input wire:model="driver_first_name" wire:keyup="$set('name', driver_first_name + ' ' + driver_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl" placeholder="John">
                                    <x-input-error :messages="$errors->get('driver_first_name')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">ID Card Number (CIN)</label>
                                    <input wire:model="driver_id_card_number" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl" placeholder="K123456">
                                    <x-input-error :messages="$errors->get('driver_id_card_number')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Phone Number</label>
                                    <input wire:model="driver_phone" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl" placeholder="+212 600 000 000">
                                    <x-input-error :messages="$errors->get('driver_phone')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Professional Email</label>
                                    <input wire:model="driver_professional_email" type="email" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl" placeholder="driver@company.com">
                                    <x-input-error :messages="$errors->get('driver_professional_email')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Commission (DH)</label>
                                    <div class="relative">
                                        <span class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">
                                            DH
                                        </span>
                                        <input wire:model="driver_commission" type="number" step="0.01" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold">
                                    </div>
                                    <x-input-error :messages="$errors->get('driver_commission')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Assigned Vehicle</label>
                                    <select wire:model="vehicle_id" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl appearance-none">
                                        <option value="">No Vehicle Assigned</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->make }} - {{ $vehicle->license_plate }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('vehicle_id')" />
                                </div>

                                <div class="space-y-4 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-gray-50">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">ID Card Copy (File)</label>
                                        <input wire:model="driver_id_card_file" type="file" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Contract (PDF)</label>
                                        <input wire:model="driver_contract" type="file" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Manager Type -->
                        @if ($this->role === 'manager')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">Last Name</label>
                                    <input wire:model="manager_last_name" wire:keyup="$set('name', manager_first_name + ' ' + manager_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                    <x-input-error :messages="$errors->get('manager_last_name')" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700">First Name</label>
                                    <input wire:model="manager_first_name" wire:keyup="$set('name', manager_first_name + ' ' + manager_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                    <x-input-error :messages="$errors->get('manager_first_name')" />
                                </div>

                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-sm font-bold text-gray-700">ID Card Number (CIN)</label>
                                    <input wire:model="manager_id_card_number" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl">
                                    <x-input-error :messages="$errors->get('manager_id_card_number')" />
                                </div>
                            </div>
                        @endif

                        <div class="mt-12 flex justify-end">
                            <button type="submit" class="px-16 py-5 bg-primary text-white rounded-[1.5rem] font-bold text-lg shadow-2xl shadow-primary/40 hover:shadow-primary/60 hover:-translate-y-1 transition-all flex items-center">
                                <i data-lucide="check-circle" class="w-6 h-6 me-3"></i>
                                Save Comprehensive Profile
                                <div wire:loading class="ms-3">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

