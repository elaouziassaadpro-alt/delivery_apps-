<x-admin-layout>
    <x-slot name="header">
        Profile Settings
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">My Profile</span>
        </span>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-12 pb-20">
        <!-- Personal Information -->
        <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            <livewire:profile.update-profile-information-form />
        </div>

        <!-- Security -->
        <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100">
            <livewire:profile.update-password-form />
        </div>

        <!-- Danger Zone -->
        <div class="bg-red-50/50 p-10 rounded-[2.5rem] border border-red-100/50">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-admin-layout>
