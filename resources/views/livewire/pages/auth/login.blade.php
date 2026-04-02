<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    public function login()
    {
        $this->validate();

        // Check if an admin specifically exists, or just use count() if it's a fresh app
        if (User::where('role', 'admin')->count() == 0 && $this->form->email == 'admin@admin.com') {
            User::create([
                'name' => 'Admin',
                'email' => $this->form->email,
                'password' => Hash::make($this->form->password),
                'role' => 'admin',
                'photo' => 'assets/profile/default.png',
                'email_verified_at' => now(),
            ]);            
        }

        $this->form->authenticate();

        Session::regenerate();

        // We call the redirect logic here
        return $this->redirectTo();
    }

    public function redirectTo()
    {
        $user = Auth::user();

        // 1. Basic safety check
        if (!$user || !$user->role) {
            Auth::logout();
            return redirect('/login')->with('error', 'User role not assigned.');
        }

        // 2. Normalize role for Linux/Enum compatibility
        $roleValue = strtolower($user->role);
        $userRole = UserRole::tryFrom($roleValue);

        // 3. Fallback if the role in DB doesn't match the Enum
        if (!$userRole) {
            Log::error("Invalid role detected for User ID {$user->id}: " . $user->role);
            Auth::logout();
            return redirect('/login')->with('error', 'Invalid system role.');
        }

        // 4. Final Redirect
        return redirect()->intended(route($userRole->dashboardRoute(), absolute: false));
    }
}; ?>

<div class="space-y-8 animate-in fade-in zoom-in duration-700">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div class="group">
            <label for="email" class="block text-sm font-bold text-gray-700 mb-2 transition-colors group-focus-within:text-primary">
                {{ __('Email Address') }}
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                </span>
                <input wire:model="form.email" id="email" 
                       class="block w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium placeholder:text-gray-400" 
                       type="email" name="email" required autofocus autocomplete="username" placeholder="name@company.com">
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="group">
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-sm font-bold text-gray-700 group-focus-within:text-primary transition-colors">
                    {{ __('Password') }}
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-primary hover:text-primary/80 transition-colors uppercase tracking-widest" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot?') }}
                    </a>
                @endif
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                </span>
                <input wire:model="form.password" id="password" 
                       class="block w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium placeholder:text-gray-400"
                       type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center cursor-pointer group">
                <div class="relative">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="sr-only peer" name="remember">
                    <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition-all duration-300 group-hover:bg-gray-300 peer-checked:bg-[#7466d5] peer-checked:shadow-[0_0_12px_#7466d5]"></div>
                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full shadow transition-transform duration-300 peer-checked:translate-x-4"></div>
                </div>
                <span class="ms-3 text-sm font-bold text-gray-400 group-hover:text-gray-600 transition-colors">{{ __('Keep me logged in') }}</span>
            </label>
        </div>  

        <div class="pt-4">
            <button type="submit" class="w-full relative group overflow-hidden bg-primary text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-primary/30 hover:shadow-primary/40 active:scale-[0.98] transition-all">
                <span class="relative z-10 flex items-center justify-center">
                    {{ __('Log in to Portal') }}
                    <i data-lucide="arrow-right" class="w-5 h-5 ms-2 group-hover:translate-x-1 transition-transform"></i>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            </button>
        </div>
    </form>
</div>

<style>
    input:checked ~ .dot {
        transform: translateX(100%);
    }
</style>
