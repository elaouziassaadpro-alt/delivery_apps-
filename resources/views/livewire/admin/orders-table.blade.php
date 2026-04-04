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

                    {{-- Customer --}}
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-900">{{ $order->bon?->user?->name ?? 'N/A' }}</p>
                        <p class="text-[11px] font-bold text-gray-400 mt-0.5">{{ $order->bon?->user?->email ?? '' }}</p>
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

</x-admin.card>

{{-- =============================================
     ORDER DETAILS POPUP MODAL
     Triggered by: wire:click="view($id)"
     ============================================= --}}
<div
    x-data="orderDetailsModal()"
    x-on:open-order-details.window="openModal($event.detail.order)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto"
>
    {{-- Backdrop --}}
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
        @click="closeModal()"
        x-transition:enter="transition duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    {{-- Modal Panel --}}
    <div
        class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden z-10"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
    >
        {{-- Header Bar --}}
        <div class="flex items-center justify-between px-8 pt-8 pb-6 border-b border-gray-100">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-black uppercase tracking-widest mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse inline-block"></span>
                    <span>Order Details</span>
                </div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight" x-text="order ? '#' + order.code : ''"></h2>
            </div>
            <div class="flex items-center space-x-3">
                {{-- Status badge --}}
                <span
                    class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest"
                    :class="{
                        'bg-yellow-100 text-yellow-700': order && order.status === 'pending',
                        'bg-blue-100 text-blue-700':    order && order.status === 'transit',
                        'bg-green-100 text-green-700':  order && order.status === 'delivered',
                        'bg-red-100 text-red-700':      order && order.status === 'cancelled',
                        'bg-gray-100 text-gray-700':    order && !['pending','transit','delivered','cancelled'].includes(order.status),
                    }"
                    x-text="order ? order.status : ''"
                ></span>
                <button @click="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">

            {{-- Left Column --}}
            <div class="p-8 space-y-6 border-r border-gray-100">

                {{-- QR Code --}}
                <div class="bg-gray-50 rounded-3xl p-6 flex flex-col items-center border border-gray-100">
                    <div class="w-36 h-36 bg-white rounded-2xl shadow-sm border p-2 flex items-center justify-center overflow-hidden">
                        <template x-if="order && order.code">
                            <img
                                :src="'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent('Order: ' + order.code)"
                                alt="QR Code"
                                class="w-full h-full object-contain"
                            >
                        </template>
                    </div>
                    <p class="mt-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tracking QR</p>
                </div>

                {{-- Recipient Info --}}
                <div class="space-y-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Recipient</p>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900" x-text="order && order.recipient ? order.recipient.first_name + ' ' + order.recipient.last_name : 'N/A'"></p>
                            <p class="text-sm text-gray-500" x-text="order && order.recipient ? order.recipient.phone : ''"></p>
                            <p class="text-xs text-gray-400" x-text="order && order.recipient ? order.recipient.email : ''"></p>
                        </div>
                    </div>
                </div>

                {{-- Client (Bon owner) --}}
                <div class="space-y-2">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Client (Bon Owner)</p>
                    <div class="flex items-center space-x-2 text-gray-700">
                        <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                        <span class="font-bold text-sm" x-text="order ? order.client : 'N/A'"></span>
                    </div>
                </div>

                {{-- Price & Payment --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Price</p>
                        <p class="text-xl font-black text-gray-900" x-text="order ? parseFloat(order.price).toFixed(2) + ' DH' : '-'"></p>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Payment</p>
                        <p class="text-sm font-bold"
                            :class="order && order.payment_status === 'paid' ? 'text-green-600' : 'text-red-500'"
                            x-text="order ? (order.payment_status ?? 'Unpaid') : '-'">
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Column — Map --}}
            <div class="p-8 space-y-4">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center space-x-1">
                    <i data-lucide="map-pin" class="w-3 h-3 text-primary"></i>
                    <span>Delivery Location</span>
                </p>

                {{-- Map container --}}
                <div x-show="hasLocation" class="w-full h-60 rounded-3xl overflow-hidden border-2 border-gray-100 shadow-inner">
                    <div wire:ignore x-ref="mapContainer" class="w-full h-full"></div>
                </div>

                {{-- No GPS placeholder --}}
                <div x-show="!hasLocation" class="w-full h-60 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center p-6">
                    <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-400 mb-3">
                        <i data-lucide="map-pin-off" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-bold text-gray-700">No GPS Coordinates</p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Map unavailable</p>
                </div>

                {{-- Address text --}}
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-1">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Address</p>
                    <p class="text-sm font-medium text-gray-600 leading-relaxed" x-text="order && order.location ? order.location : 'No address provided'"></p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end space-x-3">
            <button @click="closeModal()" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold text-sm hover:bg-gray-200 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

@once
<script>
    window.orderDetailsModal = function () {
        return {
            open: false,
            order: null,
            map: null,
            marker: null,
            hasLocation: false,

            openModal(data) {
                this.order = data;
                this.open = true;
                this.$nextTick(() => {
                    lucide.createIcons();
                    this.initMap();
                });
            },

            closeModal() {
                this.open = false;
                // Stop the map so it can be re-initialised next time
                if (this.map) {
                    this.map.remove();
                    this.map = null;
                    this.marker = null;
                }
            },

            initMap() {
                this.hasLocation = !!(this.order && this.order.lat && this.order.lng);
                if (!this.hasLocation) return;

                setTimeout(() => {
                    try {
                        const lat = parseFloat(this.order.lat);
                        const lng = parseFloat(this.order.lng);

                        if (!this.map) {
                            this.map = L.map(this.$refs.mapContainer).setView([lat, lng], 15);
                            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                                maxZoom: 19
                            }).addTo(this.map);
                            this.marker = L.marker([lat, lng])
                                .addTo(this.map)
                                .bindPopup(this.order.location || 'Delivery point')
                                .openPopup();
                        } else {
                            this.map.setView([lat, lng], 15);
                            this.marker.setLatLng([lat, lng]);
                        }
                        this.map.invalidateSize();
                    } catch (err) {
                        console.error('Modal map error:', err);
                    }
                }, 350);
            }
        };
    };
</script>
@endonce