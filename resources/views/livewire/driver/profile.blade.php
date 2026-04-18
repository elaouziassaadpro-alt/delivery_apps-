<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

new #[Layout('layouts.driver')] class extends Component {

    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public $photo;


    // Password Update
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;

        if ($user->driver) {
            $this->first_name = $user->driver->first_name ?? '';
            $this->last_name = $user->driver->last_name ?? '';
            $this->phone = $user->driver->phone ?? '';
        }
    }


    public function updateProfile(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
        ]);

        if ($user->driver) {
            $user->driver->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
            ]);
        }


        $this->dispatch('profile-updated');
        session()->flash('status', 'Profile Information Updated.');
    }

    public function updatedPhoto(): void
    {
        $this->updatePhoto();
    }

    public function updatePhoto(): void
    {
        $this->validate([
            'photo' => ['image', 'max:2048'],
        ]);

        $path = $this->photo->store('profiles', 'private');
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['photo' => $path]);
        
        $this->photo = null;
        $this->dispatch('profile-updated');
        session()->flash('status', 'Profile Photo Updated.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->password),
        ]);


        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('status', 'Password Securely Updated.');
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-primary/10 rounded-2xl text-primary">
                <i data-lucide="user-cog" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Profile Settings</h1>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-12 pb-24 px-4 sm:px-6">
        <!-- 1. Profile Identity Header Card -->
        <div class="bg-[#0f172a] rounded-[2.5rem] p-8 sm:p-10 text-white relative overflow-hidden shadow-2xl group border border-white/5">
             <!-- Background Accents -->
             <div class="absolute -right-20 -top-20 w-80 h-80 bg-primary/20 rounded-full blur-[100px] group-hover:bg-primary/30 transition-all duration-700"></div>
             <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-blue-500/10 rounded-full blur-[80px]"></div>

             <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start space-y-8 md:space-y-0 md:space-x-12">
                <!-- Photo Upload Section -->
                <div class="relative">
                    <div class="w-40 h-40 rounded-[2.5rem] overflow-hidden border-4 border-white/10 shadow-2xl relative bg-white/5 ring-8 ring-white/5">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                        @else
                            <x-user-avatar :user="Auth::user()" class="w-full h-full object-cover" />
                        @endif

                        <div wire:loading wire:target="photo" class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm">
                            <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    </div>

                    <label for="profile-photo" class="absolute -bottom-4 -right-4 w-12 h-12 bg-primary rounded-2xl flex items-center justify-center text-white shadow-xl shadow-primary/30 cursor-pointer hover:scale-110 active:scale-95 transition-all group/cam">
                        <i data-lucide="camera" class="w-5 h-5 group-hover/cam:rotate-12 transition-transform"></i>
                        <input wire:model="photo" id="profile-photo" type="file" class="sr-only">
                    </label>
                </div>

                <div class="flex-1 text-center md:text-left pt-2">
                    <h2 class="text-3xl font-black tracking-tight mb-3 uppercase flex flex-col sm:flex-row items-center md:items-start gap-3">
                        {{ Auth::user()->name }}
                        <span class="px-3 py-1 bg-primary text-[10px] font-black tracking-widest rounded-lg flex items-center shadow-lg shadow-primary/20">
                            <i data-lucide="shield-check" class="w-3 h-3 me-1.5"></i>
                            Verified
                        </span>
                    </h2>
                    <div class="flex flex-wrap justify-center md:justify-start gap-4">
                        <div class="flex items-center space-x-2 text-gray-400">
                            <i data-lucide="mail" class="w-4 h-4 text-primary"></i>
                            <span class="text-xs font-bold">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-400 border-l border-white/10 ps-4">
                            <i data-lucide="truck" class="w-4 h-4 text-primary"></i>
                            <span class="text-xs font-bold uppercase tracking-widest">Active Driver</span>
                        </div>
                    </div>
                </div>
             </div>
        </div>

        @if (session('status'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="p-6 bg-emerald-500/10 border border-emerald-500/20 rounded-3xl text-emerald-600 flex items-center animate-in fade-in slide-in-from-top-4">
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white me-4 shadow-lg shadow-emerald-500/20">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </div>
                <span class="text-sm font-black uppercase tracking-widest">{{ session('status') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 2. Basic Information Form -->
            <div class="bg-white rounded-[2.5rem] p-8 sm:p-10 shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4 mb-10">
                    <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                        <i data-lucide="user" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Identity Info</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">General Account Settings</p>
                    </div>
                </div>

                <form wire:submit="updateProfile" class="space-y-6 flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-1">First Name</label>
                            <input wire:model="first_name" type="text" class="w-full px-6 py-4 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-gray-900">
                            <x-input-error :messages="$errors->get('first_name')" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-1">Last Name</label>
                            <input wire:model="last_name" type="text" class="w-full px-6 py-4 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-gray-900">
                            <x-input-error :messages="$errors->get('last_name')" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-1">Contact Phone</label>
                        <div class="relative">
                            <i data-lucide="phone" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300"></i>
                            <input wire:model="phone" type="text" class="w-full pl-12 pr-6 py-4 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-gray-900">
                        </div>
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-1">Email (System Login)</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300"></i>
                            <input wire:model="email" type="email" class="w-full pl-12 pr-6 py-4 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-gray-900">
                        </div>
                        <x-input-error :messages="$errors->get('email')" />
                    </div>


                    <div class="pt-6 mt-auto">
                        <button type="submit" class="w-full relative group overflow-hidden bg-[#0f172a] text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-xl hover:bg-primary transition-all flex items-center justify-center">
                            <span class="relative z-10">Save Global Profile</span>
                            <i data-lucide="check" class="w-4 h-4 ms-2 relative z-10 group-hover:scale-125 transition-transform"></i>
                            <div class="absolute inset-0 bg-primary -translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 3. Security Form -->
            <div class="bg-white rounded-[2.5rem] p-8 sm:p-10 shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4 mb-10">
                    <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Security engine</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Privacy & Protection</p>
                    </div>
                </div>

                <form wire:submit="updatePassword" class="space-y-6 flex-1">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-1">Current Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300"></i>
                            <input wire:model="current_password" type="password" class="w-full pl-12 pr-6 py-4 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-gray-900">
                        </div>
                        <x-input-error :messages="$errors->get('current_password')" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-1">New Password</label>
                        <div class="relative">
                            <i data-lucide="key" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300"></i>
                            <input wire:model="password" type="password" class="w-full pl-12 pr-6 py-4 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-gray-900">
                        </div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-1">Confirm New Password</label>
                        <div class="relative">
                            <i data-lucide="shield-check" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300"></i>
                            <input wire:model="password_confirmation" type="password" class="w-full pl-12 pr-6 py-4 bg-gray-50/50 border border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-gray-900">
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" />
                    </div>

                    <div class="pt-6 mt-auto">
                        <button type="submit" class="w-full bg-primary text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-1 transition-all flex items-center justify-center group/btn">
                            <span class="relative z-10">Secure Update Keys</span>
                            <i data-lucide="save" class="w-4 h-4 ms-2 group-hover/btn:scale-125 transition-transform"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>