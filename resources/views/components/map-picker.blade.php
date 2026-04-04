<div 
        x-data="orderModalData()"
        x-on:open-order-details.window="open = true; order = $event.detail.order; $nextTick(() => { lucide.createIcons(); initMap(); })"
        x-show="open" 
        class="fixed inset-0 z-[100] overflow-y-auto"
        style="display: none;"
    >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-4xl w-full p-10 overflow-hidden">
                <button @click="open = false" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600">
                    <i data-lucide="x"></i>
                </button>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-[2.5rem] flex flex-col items-center border border-gray-100">
                            <div class="w-32 h-32 bg-white p-2 rounded-2xl shadow-sm border flex items-center justify-center overflow-hidden">
                                <template x-if="order && order.code">
                                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('Order: ' + order.code)" alt="QR Code" class="w-full h-full object-contain">
                                </template>
                            </div>
                            <p class="mt-2 text-[10px] font-black text-gray-400">TRACKING QR</p>
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Recipient</label>
                            <p class="font-bold text-gray-900" x-text="order ? order.recipient.first_name + ' ' + order.recipient.last_name : ''"></p>
                            <p class="text-xs text-gray-500" x-text="order ? order.recipient.phone : ''"></p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                            <i data-lucide="map-pin" class="w-3 h-3 mr-1 text-primary"></i> Location Map
                        </label>

                        <div class="relative">
                            <div x-show="hasLocation" class="w-full h-64 bg-gray-200 rounded-[2.5rem] border-4 border-white shadow-inner overflow-hidden">
                                <div wire:ignore x-ref="mapContainer" class="w-full h-full"></div>
                            </div>

                            <div x-show="!hasLocation" class="w-full h-64 bg-gray-50 rounded-[2.5rem] border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center p-6">
                                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-400 mb-3">
                                    <i data-lucide="map-pin-off" class="w-6 h-6"></i>
                                </div>
                                <p class="text-sm font-bold text-gray-900">Map Unavailable</p>
                                <p class="text-[10px] text-gray-400 uppercase mt-1">No GPS coordinates recorded for this order</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <p class="text-[11px] font-medium text-gray-600 italic" x-text="order ? order.location : 'No address provided'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    // Extract Map Logic to global function
    window.orderModalData = function() {
        return {
            open: false, 
            order: null,
            map: null,
            marker: null,
            hasLocation: false,

            initMap() {
                this.hasLocation = !!(this.order && this.order.lat && this.order.lng);
                if (!this.hasLocation) return;
                
                try {
                    setTimeout(() => {
                        const lat = parseFloat(this.order.lat);
                        const lng = parseFloat(this.order.lng);

                        if (!this.map) {
                            this.map = L.map(this.$refs.mapContainer).setView([lat, lng], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
                            this.marker = L.marker([lat, lng]).addTo(this.map);
                        } else {
                            this.map.setView([lat, lng], 15);
                            this.marker.setLatLng([lat, lng]);
                        }
                        this.map.invalidateSize();
                    }, 400);
                } catch (error) {
                    console.error('Modal Map Error:', error);
                }
            }
        };
    };

    // Runs when wire:navigate transitions to this page
    document.addEventListener('livewire:navigated', () => {
        lucide.createIcons();
    });

    // Runs after every Livewire component update (essential for the Delete fix)
    document.addEventListener('livewire:updated', () => {
        lucide.createIcons();
    });
</script>