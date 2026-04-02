<x-admin.card class="p-0 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-bold">Recent Orders</h3>
        <input type="text" wire:model.live="search" placeholder="Search orders..." class="text-sm border-gray-200 rounded-xl focus:ring-primary px-4 py-2 w-64">
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Destination</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-8 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($orders as $order)
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    {{-- Order ID --}}
                    <td class="px-6 py-4 font-black text-gray-900">#{{ $order->id }}</td>
                    
                    {{-- Date --}}
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-gray-600">{{ $order->created_at->format('d M Y') }}</span>
                    </td>

                    {{-- Customer (Added a spot for phone number to make it look premium) --}}
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-900">{{ $order->client?->user?->name ?? 'N/A' }}</p>
                        <p class="text-[11px] font-bold text-gray-400 mt-0.5">{{ $order->client?->address ?? 'No address' }}</p>
                    </td>

                    {{-- Destination --}}
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 max-w-[200px] truncate" title="{{ $order->location }}">
                            {{ $order->location ?? 'Address pending...' }}
                        </div>
                    </td>

                    {{-- Amount --}}
                    <td class="px-6 py-4 text-sm font-black text-gray-900">
                        {{ number_format($order->price ?? 0, 2) }} DH
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-4">
                        @php
                            $status_color = match($order->status) {
                                'pending'   => 'bg-yellow-100 text-yellow-800 outline-yellow-200',
                                'transit'   => 'bg-blue-100 text-blue-800 outline-blue-200',
                                'delivered' => 'bg-green-100 text-green-800 outline-green-200',
                                'cancelled' => 'bg-red-100 text-red-800 outline-red-200',
                                default     => 'bg-gray-100 text-gray-800 outline-gray-200',
                            };
                        @endphp
                        <span class="px-3 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest outline outline-2 outline-offset-1 {{ $status_color }}">
                            {{ $order->status }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-8 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                            <button wire:click="view({{ $order->id }})" class="p-2 bg-white rounded-xl text-gray-400 hover:text-primary hover:shadow-md hover:shadow-primary/10 transition-all border border-gray-100">
                                 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button wire:click="edit({{ $order->id }})" class="p-2 bg-white rounded-xl text-gray-400 hover:text-blue-500 hover:shadow-md hover:shadow-blue-500/10 transition-all border border-gray-100">
                                 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </button>
                            <button wire:click="delete({{ $order->id }})" wire:confirm="Are you sure?" class="p-2 bg-white rounded-xl text-gray-400 hover:text-red-500 hover:shadow-md hover:shadow-red-500/10 transition-all border border-gray-100">
                                 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @if($orders->isEmpty())
            <div class="p-10 text-center flex flex-col items-center justify-center">
                <i data-lucide="inbox" class="w-10 h-10 text-gray-300 mb-3"></i>
                <p class="text-gray-500 font-bold text-sm">No orders found.</p>
            </div>
        @endif

        <div class="p-4 border-t border-gray-50 bg-gray-50/30">
            {{ $orders->links() }}
        </div>
    </div>
    <x-map-picker />

</x-admin.card>