<?php

use Livewire\Volt\Component;
use App\Models\Bon;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public Bon $bon;

    public function mount(Bon $bon)
    {
        $this->bon = $bon->load(['user', 'orders_driver.recipient']);
    }
}; ?>

<div class="space-y-6">
    <x-slot name="header">
        {{ __('View Driver Bon') }}
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <a href="{{ route('admin.bons.driver.index') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Bons') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">{{ __('View') }} #{{ $bon->code }}</span>
        </span>
    </x-slot>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">{{ __('Bon Details') }}</h2>
                <p class="text-sm text-gray-400">{{ __('Detailed information for driver delivery note.') }}</p>
            </div>
            <div class="bg-primary/10 text-primary px-4 py-2 rounded-xl font-bold text-sm">
                #{{ $bon->code }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="space-y-1">
                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">{{ __('Driver') }}</label>
                <p class="font-bold text-gray-900">{{ $bon->user->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-400">{{ $bon->user->email ?? '' }}</p>
            </div>

            <div class="space-y-1">
                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">{{ __('Date') }}</label>
                <p class="font-bold text-gray-900">{{ $bon->created_at->format('d M Y, H:i') }}</p>
            </div>

            <div class="space-y-1">
                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">{{ __('Status') }}</label>
                <div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest
                        @if($bon->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($bon->status === 'processing') bg-blue-100 text-blue-800
                        @elseif($bon->status === 'completed') bg-green-100 text-green-800
                        @elseif($bon->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ $bon->status }}
                    </span>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">{{ __('Total Amount') }}</label>
                <p class="text-xl font-black text-primary">{{ number_format($bon->price ?? 0, 2) }} DH</p>
            </div>

            <div class="space-y-1">
                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">{{ __('Payment Status') }}</label>
                <p class="font-bold {{ $bon->payment_status === 'paid' ? 'text-green-600' : 'text-red-500' }}">
                    {{ ucfirst($bon->payment_status) }}
                </p>
            </div>

            <div class="space-y-1">
                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">{{ __('Delivery Type') }}</label>
                <p class="font-bold text-gray-900">{{ ucfirst($bon->delivery_type) }}</p>
            </div>
        </div>

        @if($bon->notes)
            <div class="mt-8 pt-8 border-t border-gray-50">
                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">{{ __('Notes') }}</label>
                <p class="mt-2 text-gray-600 bg-gray-50 p-4 rounded-2xl border border-gray-100">{{ $bon->notes }}</p>
            </div>
        @endif

        <div class="mt-10 pt-10 border-t border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                <i data-lucide="package" class="w-5 h-5 me-2 text-primary"></i>
                {{ __('Associated Orders') }} ({{ $bon->orders_driver->count() }})
            </h3>

            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">{{ __('Code') }}</th>
                            <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">{{ __('Recipient') }}</th>
                            <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">{{ __('Destination') }}</th>
                            <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">{{ __('Price') }}</th>
                            <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($bon->orders_driver as $order)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 font-black text-gray-900">#{{ $order->code }}</td>
                                <td class="px-6 py-4">{{ $order->recipient->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-gray-500 max-w-[200px] truncate">{{ $order->location ?? '-' }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ number_format($order->price ?? 0, 2) }} DH</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest {{ $order->color() }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-10 flex items-center gap-4">
            <a href="{{ route('admin.bons.driver.index') }}" class="px-8 py-4 bg-gray-50 text-gray-700 rounded-2xl font-bold hover:bg-gray-100 transition-all">
                {{ __('Back to List') }}
            </a>
            <a href="{{ route('admin.bons.driver.edit', $bon) }}" class="px-8 py-4 bg-primary text-white rounded-2xl font-bold shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all">
                {{ __('Edit Bon') }}
            </a>
        </div>
    </div>
</div>
