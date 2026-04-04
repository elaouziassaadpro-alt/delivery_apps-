<?php

use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public function with()
    {
        return [
            'orders' => Order::with(['recipient', 'bon.user', 'driver.user'])
                ->where(function($query) {
                    $searchTerm = '%' . $this->search . '%';
                    $query->where('code', 'like', $searchTerm)
                          ->orWhereHas('recipient', function($q) use ($searchTerm) {
                              $q->where('first_name', 'like', $searchTerm)
                                ->orWhere('last_name', 'like', $searchTerm);
                          })
                          ->orWhereHas('bon.user', function($q) use ($searchTerm) {
                              $q->where('name', 'like', $searchTerm);
                          });
                })
                ->latest()
                ->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function editOrder(int $id)
    {
        return $this->redirect(route('admin.deliveries.edit', $id), navigate: true);
    }

    public function deleteOrder(int $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        session()->flash('status', 'Order deleted successfully.');
    }

    public function viewOrder(int $id)
    {
        $order = Order::with(['recipient', 'bon.user', 'driver.user', 'vehicle'])->findOrFail($id);
        $this->dispatch('open-order-details', order: $order);
    }
}; ?>

<div class="space-y-6">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <x-slot name="header">Deliveries Management</x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center text-sm">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-bold">Deliveries</span>
        </span>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.deliveries.create') }}" wire:navigate class="px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center">
            <i data-lucide="package-plus" class="w-4 h-4 me-2"></i> New Delivery
        </a>
    </x-slot>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="text-gray-400 w-5 h-5"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium" placeholder="Search code, name...">
            </div>

            <div class="flex items-center space-x-2 text-sm text-gray-500 font-medium">
                <i data-lucide="package" class="w-5 h-5 text-primary"></i>
                <span>Total: {{ \App\Models\Order::count() }} Deliveries</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Order</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Recipient</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">commission</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/30 transition-colors group" wire:key="{{ $order->id }}">
                            <td class="px-8 py-5">
                                <p class="font-bold text-gray-900">{{ $order->code }}</p>
                                <p class="text-xs text-gray-400">{{ $order->created_at->format('M d, Y') }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900">{{ $order->recipient->first_name }}</p>
                                <p class="text-[10px] text-gray-400">{{ $order->recipient->phone }}</p>
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $style = match(strtolower($order->status)) {
                                        'pending' => 'bg-amber-100 text-amber-600 border-amber-200',
                                        'delivered' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                                        default => 'bg-gray-100 text-gray-500 border-gray-200'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase border {{ $style }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900">{{ $order->commission }} <span class="text-xs text-gray-400">DH</span></p>
                            </td>
                            <td class="px-8 py-5 text-right flex items-center justify-end space-x-1">
                                <button wire:click="viewOrder({{ $order->id }})" 
                                        class="p-2 text-gray-400 hover:text-primary transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>

                                <button wire:click="deleteOrder({{ $order->id }})" 
                                        wire:confirm="Are you sure?" 
                                        class="p-2 text-gray-400 hover:text-red-500 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>
                                    </svg>
                                </button>

                                <button wire:click="editOrder({{ $order->id }})" 
                                        class="p-2 text-gray-400 hover:text-blue-500 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-8 py-10 text-center text-gray-400">No deliveries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-6">{{ $orders->links() }}</div>
    </div>

    <x-map-picker />
</div>

