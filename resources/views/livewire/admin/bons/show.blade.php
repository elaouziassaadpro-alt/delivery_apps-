<?php

use Livewire\Volt\Component;
use App\Models\Bon;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public Bon $bon;

    public function mount(Bon $bon)
    {
        $this->bon = $bon;
    }
}; ?>

<div class="space-y-6">
    <x-slot name="header">
        {{ __('View Bon') }}
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <a href="{{ route('admin.bons.index') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Bons') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">{{ __('View') }} #{{ $bon->code }}</span>
        </span>
    </x-slot>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-bold mb-4">{{ __('Bon Details') }}</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="font-bold text-gray-500">{{ __('Code:') }}</span> {{ $bon->code }}</div>
            <div><span class="font-bold text-gray-500">{{ __('Date:') }}</span> {{ $bon->created_at->format('d M Y') }}</div>
            <div><span class="font-bold text-gray-500">{{ __('Client:') }}</span> {{ $bon->user->name ?? 'N/A' }}</div>
            <div><span class="font-bold text-gray-500">{{ __('Amount:') }}</span> {{ number_format($bon->price ?? 0, 2) }} DH</div>
            <div>
                <span class="font-bold text-gray-500">{{ __('Status:') }}</span> 
                <span class="px-2 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-gray-100 text-gray-800">
                    {{ $bon->status }}
                </span>
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('admin.bons.index') }}" class="px-6 py-3 text-sm bg-gray-50 text-gray-700 rounded-xl font-bold hover:bg-gray-100 transition-all inline-block">
                {{ __('Back to List') }}
            </a>
        </div>
    </div>
</div>