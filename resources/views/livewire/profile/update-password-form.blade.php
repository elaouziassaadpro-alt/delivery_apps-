<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="space-y-10">
    <header class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-lucide="shield-check" class="w-6 h-6 me-3 text-primary"></i>
                {{ __('Security Settings') }}
            </h2>
            <p class="mt-2 text-sm text-gray-400">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </div>
        <div class="px-4 py-1.5 bg-success/10 text-success rounded-full text-[10px] font-bold uppercase tracking-widest">
            Security Active
        </div>
    </header>

    <form wire:submit="updatePassword" class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">{{ __('Current Password') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input wire:model="current_password" type="password" class="block w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold" autocomplete="current-password">
                </div>
                <x-input-error :messages="$errors->get('current_password')" />
            </div>

            <div class="hidden md:block"></div> {{-- Spacer for layout alignment --}}

            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">{{ __('New Password') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                    </span>
                    <input wire:model="password" type="password" class="block w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold" autocomplete="new-password">
                </div>
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">{{ __('Confirm Password') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                    </span>
                    <input wire:model="password_confirmation" type="password" class="block w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold" autocomplete="new-password">
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <div class="pt-6 flex items-center justify-between border-t border-gray-50">
            <div class="flex items-center text-xs text-error font-medium">
                <i data-lucide="info" class="w-4 h-4 me-2"></i>
                Requires current password to update.
            </div>
            
            <div class="flex items-center space-x-4">
                <x-action-message class="text-sm font-bold text-success shrink-0" on="password-updated">
                    {{ __('Password Updated') }}
                </x-action-message>

                <button type="submit" class="px-10 py-4 bg-primary text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all flex items-center">
                    <i data-lucide="refresh-cw" class="w-4 h-4 me-2"></i>
                    {{ __('Update Credentials') }}
                </button>
            </div>
        </div>
    </form>
</section>
