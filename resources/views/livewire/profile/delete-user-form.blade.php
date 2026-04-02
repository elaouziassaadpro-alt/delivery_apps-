<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center me-4">
                <i data-lucide="trash-2" class="w-6 h-6 text-red-600"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    {{ __('Permanently Delete Account') }}
                </h2>
                <p class="mt-1 text-sm text-gray-400 max-w-xl">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be reversed.') }}
                </p>
            </div>
        </div>
        
        <button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="px-8 py-3 bg-red-600 text-white rounded-xl font-bold text-sm shadow-xl shadow-red-200 hover:bg-red-700 transition-all flex items-center"
        >
            <i data-lucide="alert-octagon" class="w-4 h-4 me-2"></i>
            {{ __('Delete Account') }}
        </button>
    </header>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-10">
            <div class="flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-6">
                    <i data-lucide="alert-triangle" class="w-10 h-10 text-red-500"></i>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ __('Are you absolutely sure?') }}
                </h2>

                <p class="mt-3 text-sm text-gray-500 max-w-sm">
                    {{ __('This will permanently delete your account and all associated data. Please enter your password to confirm.') }}
                </p>
            </div>

            <div class="mt-8">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block text-center">{{ __('Your Password') }}</label>
                <div class="relative max-w-sm mx-auto">
                    <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input
                        wire:model="password"
                        type="password"
                        class="block w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-red-100 transition-all text-sm font-semibold text-center"
                        placeholder="••••••••"
                    />
                </div>
                <div class="flex justify-center mt-2">
                    <x-input-error :messages="$errors->get('password')" />
                </div>
            </div>

            <div class="mt-10 flex items-center justify-center space-x-4">
                <button type="button" x-on:click="$dispatch('close')" class="px-8 py-4 bg-gray-100 text-gray-600 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">
                    {{ __('Cancel, Keep Account') }}
                </button>

                <button type="submit" class="px-8 py-4 bg-red-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-red-100 hover:bg-red-700 transition-all">
                    {{ __('Yes, Delete Permanently') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
