<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $photo;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
        public function updateProfileInformation(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'photo' => ['nullable', 'image', 'max:1024'],
        ]);

        // ✅ Handle photo upload
        if ($this->photo) {

            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('private')->delete($user->photo);
            }

            // Store new photo (auto unique name)
            $photoPath = $this->photo->store('profiles', 'private');

            $user->photo = $photoPath;
        }

        // ✅ Update basic info
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // ✅ Reset email verification if changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="space-y-10">
    <header class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i data-lucide="user-cog" class="w-6 h-6 me-3 text-primary"></i>
                {{ __('Account Information') }}
            </h2>
            <p class="mt-2 text-sm text-gray-400">
                {{ __("Update your professional profile and account access.") }}
            </p>
        </div>
        <div class="px-4 py-1.5 bg-primary/10 text-primary rounded-full text-[10px] font-bold uppercase tracking-widest">
            Personal Profile
        </div>
    </header>

    <form wire:submit="updateProfileInformation" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Photo Column -->
        <div class="lg:col-span-4 flex flex-col items-center">
            <label for="photo-profile" class="w-48 h-48 bg-gray-50 rounded-[2rem] border-4 border-white shadow-2xl shadow-primary/10 overflow-hidden relative group cursor-pointer transition-all hover:border-primary/20">
                <input wire:model="photo" id="photo-profile" type="file" class="sr-only">
                <span class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity z-10">
                    <i data-lucide="camera" class="w-8 h-8 mb-2"></i>
                    <span class="text-xs font-bold uppercase tracking-wider">Change Photo</span>
                </span>
                
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif (auth()->user()->photo)
                    <img src="{{ route('profile.photo', ['filename' => basename(auth()->user()->photo)]) }}" class="w-full h-full object-cover">
                @else
                    <img src="{{ asset('assets/profile/default.png') }}" class="w-full h-full object-cover opacity-60">
                @endif

                <div wire:loading wire:target="photo" class="absolute inset-0 bg-white/80 flex items-center justify-center z-20">
                    <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </label>
            <div class="mt-6 text-center">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Authenticated As</p>
                <p class="text-lg font-bold text-gray-900 capitalize">{{ auth()->user()->role }}</p>
            </div>

            <x-input-error class="mt-4" :messages="$errors->get('photo')" />
        </div>

        <!-- Form Fields Column -->
        <div class="lg:col-span-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">{{ __('Full Name') }}</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </span>
                        <input wire:model="name" type="text" class="block w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold" required>
                    </div>
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">{{ __('Email Address') }}</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input wire:model="email" type="email" class="block w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold" required>
                    </div>
                    <x-input-error :messages="$errors->get('email')" />
                </div>
            </div>

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="p-4 bg-warning/5 border border-warning/10 rounded-2xl flex items-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-warning me-3"></i>
                    <div>
                        <p class="text-xs font-bold text-warning uppercase tracking-widest">{{ __('Action Required') }}</p>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('Your email address is unverified.') }}
                            <button wire:click.prevent="sendVerification" class="font-bold text-primary hover:underline ml-1">
                                {{ __('Resend Verification Email') }}
                            </button>
                        </p>
                    </div>
                    
                    @if (session('status') === 'verification-link-sent')
                        <div class="ml-auto text-xs font-bold text-success animate-in fade-in">
                            {{ __('Sent!') }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="pt-6 flex items-center justify-between border-t border-gray-50">
                <div class="flex items-center text-xs text-gray-400">
                    <i data-lucide="shield-check" class="w-4 h-4 me-2"></i>
                    Your data is securely managed.
                </div>
                
                <div class="flex items-center space-x-4">
                    <x-action-message class="text-sm font-bold text-success shrink-0" on="profile-updated">
                        <span class="flex items-center">
                            <i data-lucide="check" class="w-4 h-4 me-1"></i>
                            {{ __('Changes Saved') }}
                        </span>
                    </x-action-message>

                    <button type="submit" class="px-10 py-4 bg-primary text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all flex items-center">
                        <i data-lucide="save" class="w-4 h-4 me-2"></i>
                        {{ __('Update Profile') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>
