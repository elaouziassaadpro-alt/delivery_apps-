<?php

use App\Models\User;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Manager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'client';


    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:client,driver,manager'],
        ]);


        $validated['password'] = Hash::make($validated['password']);

        DB::transaction(function () use ($validated) {
            $user = User::create($validated);

            // Create basic profile record based on role
            if ($user->role === 'client') {

                Client::create([
                    'user_id' => $user->id,
                    'address' => 'Not provided yet',
                    'commission' => 0,
                ]);
            } elseif ($user->role === 'driver') {

                Driver::create([
                    'user_id' => $user->id,
                    'last_name' => $user->name,
                    'first_name' => '',
                    'id_card_number' => 'Pending',
                    'phone' => 'Pending',
                    'email' => $user->email,
                    'commission' => 0,
                ]);
            } elseif ($user->role === 'manager') {

                Manager::create([
                    'user_id' => $user->id,
                    'last_name' => $user->name,
                    'first_name' => '',
                    'id_card_number' => 'Pending',
                ]);
            }

            event(new Registered($user));
            Auth::login($user);
        });

        if (Auth::user()->isDriver()) {
            $this->redirect(route('driver.dashboard', absolute: false), navigate: true);
        } else {
            $this->redirect(route('dashboard', absolute: false), navigate: true);
        }
    }

}; ?>

<div class="space-y-8 animate-in fade-in zoom-in duration-700">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900">Create Account</h2>
        <p class="text-gray-500 text-sm mt-1 font-medium">Join our delivery network today</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <!-- Name -->
        <div class="group">
            <label for="name" class="block text-sm font-bold text-gray-700 mb-1 transition-colors group-focus-within:text-primary">
                {{ __('Full Name') }}
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </span>
                <input wire:model="name" id="name" 
                       class="block w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium placeholder:text-gray-400" 
                       type="text" name="name" required autofocus autocomplete="name" placeholder="John Doe">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email -->
        <div class="group">
            <label for="email" class="block text-sm font-bold text-gray-700 mb-1 transition-colors group-focus-within:text-primary">
                {{ __('Email Address') }}
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </span>
                <input wire:model="email" id="email" 
                       class="block w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium placeholder:text-gray-400" 
                       type="email" name="email" required autocomplete="username" placeholder="john@example.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- User Role -->
        <div class="group">
            <label for="role" class="block text-sm font-bold text-gray-700 mb-1 transition-colors group-focus-within:text-primary">
                {{ __('Account Role') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </span>
                <select wire:model="role" id="role" 
                        class="block w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium appearance-none">
                    <option value="client">Client</option>
                    <option value="driver">Driver</option>
                    <option value="manager">Manager</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </span>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-1" />
        </div>


        <div class="grid grid-cols-2 gap-4">
            <!-- Password -->
            <div class="group">
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1 transition-colors group-focus-within:text-primary">
                    {{ __('Password') }}
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input wire:model="password" id="password" 
                           class="block w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium placeholder:text-gray-400" 
                           type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="group">
                <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1 transition-colors group-focus-within:text-primary">
                    {{ __('Confirm') }}
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </span>
                    <input wire:model="password_confirmation" id="password_confirmation" 
                           class="block w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium placeholder:text-gray-400" 
                           type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                </div>
            </div>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-1" />

        <div class="pt-2">
            <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-primary/30 hover:shadow-primary/40 active:scale-[0.98] transition-all flex items-center justify-center">
                {{ __('Create Account') }}
                <i data-lucide="user-plus" class="w-5 h-5 ms-2"></i>
            </button>
        </div>

        <div class="text-center pt-2">
            <a class="text-sm font-bold text-gray-400 hover:text-primary transition-colors" href="{{ route('login') }}" wire:navigate>
                {{ __('Already have an account? Log in') }}
            </a>
        </div>
    </form>
</div>
