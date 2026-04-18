<?php

use App\Models\User;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Manager;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;


new #[Layout('layouts.admin')] class extends Component
{
    use Livewire\WithFileUploads;

    public User $user;
    public string $name = '';
    public string $email = '';
    public string $role = 'client';

    public bool $is_active = true;
    public $photo;
    public string $password = '';

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

    public function mount(User $user): void
    {
        $this->user = $user->load(['client', 'driver', 'manager']);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;

        $this->is_active = (bool)$user->is_active;

        if ($user->role === 'client' && $user->client) {

            $this->address = $user->client->address;
            $this->client_commission = $user->client->commission;
        }

        if ($user->role === 'driver' && $user->driver) {

            $this->driver_last_name = $user->driver->last_name;
            $this->driver_first_name = $user->driver->first_name;
            $this->driver_id_card_number = $user->driver->id_card_number;
            $this->driver_phone = $user->driver->phone;
            $this->driver_professional_email = $user->driver->email;
            $this->driver_commission = $user->driver->commission;
            $this->vehicle_id = $user->driver->vehicle_id;
        }

        if ($user->role === 'manager' && $user->manager) {

            $this->manager_last_name = $user->manager->last_name;
            $this->manager_first_name = $user->manager->first_name;
            $this->manager_id_card_number = $user->manager->id_card_number;
        }
    }

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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
            'role' => ['required', 'string', 'in:client,driver,manager,admin'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],

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

    public function updateUser(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Update Profile Photo if new one uploaded
            if ($this->photo) {
                $photoPath = $this->photo->store('profiles', 'private');
                $this->user->photo = $photoPath;
            }

            // 2. Update Basic User Info
            $updateData = [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'is_active' => $this->is_active,
            ];

            if ($this->password) {
                $updateData['password'] = Hash::make($this->password);
            }

            $this->user->update($updateData);


            // 3. Update Role Specific Details
            if ($this->role === 'client') {

                Client::updateOrCreate(
                    ['user_id' => $this->user->id],
                    [
                        'address' => $this->address,
                        'commission' => $this->client_commission,
                        'photo' => $this->user->photo,
                    ]
                );
            }

            if ($this->role === 'driver') {

                $driverData = [
                    'vehicle_id' => $this->vehicle_id,
                    'last_name' => $this->driver_last_name,
                    'first_name' => $this->driver_first_name,
                    'id_card_number' => $this->driver_id_card_number,
                    'phone' => $this->driver_phone,
                    'email' => $this->driver_professional_email,
                    'commission' => $this->driver_commission,
                    'photo' => $this->user->photo,
                ];

                if ($this->driver_id_card_file) {
                    $driverData['id_card_file'] = $this->driver_id_card_file->store('drivers/id_cards', 'public');
                }
                if ($this->driver_contract) {
                    $driverData['contract'] = $this->driver_contract->store('drivers/contracts', 'public');
                }

                Driver::updateOrCreate(
                    ['user_id' => $this->user->id],
                    $driverData
                );
            }

            if ($this->role === 'manager') {

                Manager::updateOrCreate(
                    ['user_id' => $this->user->id],
                    [
                        'last_name' => $this->manager_last_name,
                        'first_name' => $this->manager_first_name,
                        'id_card_number' => $this->manager_id_card_number,
                    ]
                );
            }
        });

        session()->flash('status', 'User profile updated successfully.');
        $this->redirect('/admin/users', navigate: true);
    }
}; ?>

<div class="space-y-10 pb-20">
    <x-slot name="header">
        Edit User Profile
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center text-sm">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <a href="{{ route('admin.users.index') }}" class="hover:text-primary transition-colors text-gray-400">Users</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-bold">Edit Account</span>
        </span>
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <form wire:submit="updateUser" class="space-y-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Account Core Basics -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Profile Photo -->
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col items-center relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                        
                        <label for="photo-upload" class="w-40 h-40 bg-gray-50 rounded-[2rem] border-4 border-white shadow-2xl overflow-hidden mb-6 relative group cursor-pointer transition-all hover:border-primary/20">
                            <input wire:model="photo" id="photo-upload" type="file" class="sr-only">
                            <span class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                <i data-lucide="camera" class="w-8 h-8 mb-2"></i>
                                <span class="text-xs font-bold uppercase tracking-widest">Update</span>
                            </span>
                            
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif ($user->photo)
                                <img src="{{ route('profile.photo', ['filename' => basename($user->photo)]) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 italic text-gray-300">
                                    <i data-lucide="user" class="w-16 h-16"></i>
                                </div>
                            @endif

                            <div wire:loading wire:target="photo" class="absolute inset-0 bg-white/80 flex items-center justify-center z-20">
                                <i data-lucide="loader-2" class="w-8 h-8 text-primary animate-spin"></i>
                            </div>
                        </label>
                        
                        <div class="text-center">
                            <h3 class="font-bold text-gray-900 text-xl tracking-tight">{{ $name ?: 'N/A' }}</h3>
                            <div class="mt-2 flex items-center justify-center">
                                <span class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-widest rounded-full">
                                    {{ $role }}
                                </span>
                                <span class="mx-2 text-gray-200">|</span>

                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">ID #USR-{{ $user->id }}</span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('photo')" class="mt-4" />
                    </div>

                    <!-- Status & Type -->
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900">System Access</h3>
                            <i data-lucide="shield-check" class="w-5 h-5 text-gray-300"></i>
                        </div>
                        
                        <div class="space-y-5">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Account Role</label>
                                <select wire:model.live="role" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                                    <option value="client">Client (Receiver)</option>
                                    <option value="driver">Driver (Delivery)</option>
                                    <option value="manager">Central Manager</option>
                                    <option value="admin" disabled>Administrator</option>
                                </select>
                            </div>


                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Account Status</label>
                                <select wire:model="is_active" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                                    <option value="1">Active Account</option>
                                    <option value="0">Suspended / Hidden</option>
                                </select>
                            </div>

                            <div class="pt-4 border-t border-gray-50 space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Primary Email</label>
                                <div class="relative">
                                    <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                    <input wire:model="email" type="email" class="block w-full pl-12 pr-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                </div>
                                <x-input-error :messages="$errors->get('email')" />
                            </div>

                            <div class="pt-4 border-t border-gray-50 space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Update Password (Optional)</label>
                                <div class="relative">
                                    <i data-lucide="key-round" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                    <input wire:model="password" type="password" placeholder="Leave blank to keep current" class="block w-full pl-12 pr-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                </div>
                                <x-input-error :messages="$errors->get('password')" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-12 rounded-[3.5rem] shadow-sm border border-gray-100 min-h-full flex flex-col">
                        <div class="mb-10 flex items-center justify-between">
                            <div>
                                <h2 class="text-3xl font-black text-gray-900 tracking-tight">
                                    {{ ucfirst($role) }} Internal Profile
                                </h2>
                                <p class="text-sm text-gray-400 mt-2">Manage additional professional attributes for this account.</p>
                            </div>

                            <div class="w-16 h-16 bg-gray-50 rounded-3xl flex items-center justify-center">
                                @if($role === 'client') <i data-lucide="store" class="w-8 h-8 text-primary"></i> @endif
                                @if($role === 'driver') <i data-lucide="truck" class="w-8 h-8 text-primary"></i> @endif
                                @if($role === 'manager') <i data-lucide="briefcase" class="w-8 h-8 text-primary"></i> @endif
                            </div>

                        </div>

                        <!-- Content Area -->
                        <div class="flex-1">
                            <!-- Client Form -->
                            @if ($this->role === 'client')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Display / Full Name</label>
                                        <input wire:model="name" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold">
                                        <x-input-error :messages="$errors->get('name')" />
                                    </div>

                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Physical Business Address</label>
                                        <textarea wire:model="address" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold h-32"></textarea>
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

                            <!-- Driver Form -->
                            @if ($this->role === 'driver')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Last Name</label>
                                        <input wire:model="driver_last_name" wire:keyup="$set('name', driver_first_name + ' ' + driver_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                        <x-input-error :messages="$errors->get('driver_last_name')" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">First Name</label>
                                        <input wire:model="driver_first_name" wire:keyup="$set('name', driver_first_name + ' ' + driver_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                        <x-input-error :messages="$errors->get('driver_first_name')" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">CIN (ID Number)</label>
                                        <input wire:model="driver_id_card_number" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                        <x-input-error :messages="$errors->get('driver_id_card_number')" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Mobile Phone</label>
                                        <input wire:model="driver_phone" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                        <x-input-error :messages="$errors->get('driver_phone')" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Assigned Vehicle</label>
                                        <select wire:model="vehicle_id" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                            <option value="">No Vehicle Assigned</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}">{{ $vehicle->make }} - {{ $vehicle->license_plate }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Fixed Commission ($)</label>
                                        <input wire:model="driver_commission" type="number" step="0.01" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                    </div>

                                    <!-- Files -->
                                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-gray-50">
                                        <div class="space-y-3">
                                            <label class="text-sm font-bold text-gray-700 block">ID Card Document</label>
                                            @if($user->driver && $user->driver->id_card_file)
                                                <div class="flex items-center space-x-2 mb-2 p-2 bg-gray-50 rounded-xl">
                                                    <i data-lucide="file-text" class="w-4 h-4 text-primary"></i>
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase">Current File Exists</span>
                                                </div>
                                            @endif
                                            <input wire:model="driver_id_card_file" type="file" class="block w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                        </div>

                                        <div class="space-y-3">
                                            <label class="text-sm font-bold text-gray-700 block">Work Contract (PDF)</label>
                                            @if($user->driver && $user->driver->contract)
                                                <div class="flex items-center space-x-2 mb-2 p-2 bg-gray-50 rounded-xl">
                                                    <i data-lucide="file-check" class="w-4 h-4 text-primary"></i>
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase">Contract Uploaded</span>
                                                </div>
                                            @endif
                                            <input wire:model="driver_contract" type="file" class="block w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Manager Form -->
                            @if ($this->role === 'manager')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Last Name</label>
                                        <input wire:model="manager_last_name" wire:keyup="$set('name', manager_first_name + ' ' + manager_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                        <x-input-error :messages="$errors->get('manager_last_name')" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-gray-700">First Name</label>
                                        <input wire:model="manager_first_name" wire:keyup="$set('name', manager_first_name + ' ' + manager_last_name)" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                        <x-input-error :messages="$errors->get('manager_first_name')" />
                                    </div>

                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-sm font-bold text-gray-700">Management ID Card Number (CIN)</label>
                                        <input wire:model="manager_id_card_number" type="text" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-semibold">
                                        <x-input-error :messages="$errors->get('manager_id_card_number')" />
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-12 pt-10 border-t border-gray-50 flex items-center justify-between">
                            <div class="flex items-center text-[10px] font-bold text-gray-300 uppercase tracking-[0.2em]">
                                <i data-lucide="shield" class="w-4 h-4 me-2"></i>
                                Professional Module
                            </div>
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('admin.users.index') }}" class="px-8 py-4 text-gray-400 font-bold text-sm hover:text-gray-600 transition-colors">
                                    Discard Changes
                                </a>
                                <button type="submit" class="px-12 py-5 bg-primary text-white rounded-[2rem] font-black text-sm shadow-2xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-1 transition-all flex items-center">
                                    <i data-lucide="save" class="w-5 h-5 me-3"></i>
                                    Update Comprehensive Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

