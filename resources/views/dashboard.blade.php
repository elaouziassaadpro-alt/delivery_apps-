@php
    /* |--------------------------------------------------------------------------
    | Data Logic
    |--------------------------------------------------------------------------
    */
    // Wrap in safer calculation to prevent errors if migrations or data are missing
    try {
        $stats = [
            'total' => (int)\App\Models\Order::count(),
            'pending' => (int)\App\Models\Order::where('status', 'pending')->count(),
            'revenue' => (float)(\App\Models\Order::where('status', 'delivered')->sum('price') ?? 0),
            'cancelled' => (int)\App\Models\Order::where('status', 'cancelled')->count(),
        ];
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning("Dashboard stats fallback: " . $e->getMessage());
        $stats = ['total' => 0, 'pending' => 0, 'revenue' => 0.0, 'cancelled' => 0];
    }

    $mappedOrders = collect([]);
    try {
        $mappedOrders = \App\Models\Order::whereNotNull('lat')->whereNotNull('lng')
            ->with('bon.client.user') 
            ->get(['id', 'code', 'lat', 'lng', 'status', 'bon_id', 'location'])
            ->map(fn($o) => [
                'id'       => $o->id,
                'lat'      => (float)($o->lat ?? 0),
                'lng'      => (float)($o->lng ?? 0),
                'code'     => (string)($o->code ?? 'N/A'),
                'status'   => strtolower((string)($o->status ?? 'pending')),
                'location' => (string)($o->location ?? 'Unknown'),
                'client'   => $o->bon?->client?->user?->name ?? 'N/A'
            ]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Dashboard map query failed: ' . $e->getMessage());
    }

    $weeklyData = collect(range(0, 6))->map(function($i) {
        $date = now()->subDays(6 - $i)->format('Y-m-d');
        return [
            'day' => now()->subDays(6 - $i)->format('D'),
            'count' => \App\Models\Order::whereDate('created_at', $date)->count()
        ];
    });

    $maxCount = $weeklyData->max('count');
    $maxCount = $maxCount > 0 ? (int)$maxCount : 1; 
    
    // Define a central Depot/HQ location for route tracing
    $depotLocation = [33.5731, -7.5898]; // Example: Casablanca
    
@endphp

<x-admin-layout>
    <style>
        /* --- Innovative Map Styles --- */
        
        /* Dark Mode Map Container */
        .map-container-dark {
            background: #1e293b; 
            border-radius: 2.5rem;
            overflow: hidden;
        }

        /* Custom Marker Styles */
        .custom-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.9);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        
        .custom-marker:hover {
            transform: scale(1.2);
            z-index: 1000 !important;
        }

        .marker-pending {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            animation: pulse-amber 2s infinite;
        }

        .marker-transit {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            animation: pulse-blue 2s infinite;
            position: relative;
        }
        /* Little moving truck icon inside transit marker */
        .marker-transit::after {
            content: '🚚';
            font-size: 10px;
            position: absolute;
        }

        .marker-delivered {
            background: linear-gradient(135deg, #34d399, #10b981);
            border-color: #d1fae5;
        }

        .marker-cancelled {
            background: linear-gradient(135deg, #f87171, #ef4444);
            border-color: #fee2e2;
        }

        /* Depot Marker */
        .marker-depot {
            background: #fff;
            border: 4px solid #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.3);
            font-size: 12px;
            font-weight: bold;
            color: #6366f1;
        }

        /* Animated Route Line */
        .leaflet-interactive.animated-route {
            stroke: #3b82f6;
            stroke-width: 2;
            stroke-dasharray: 10, 10;
            stroke-linecap: round;
            animation: dash-flow 1s linear infinite;
            stroke-opacity: 0.7;
        }

        @keyframes dash-flow {
            to { stroke-dashoffset: -20; }
        }

        @keyframes pulse-amber {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        
        /* Filter Buttons */
        .map-filter-btn {
            transition: all 0.2s;
        }
        .map-filter-btn.active {
            transform: scale(1.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="p-8 bg-slate-50 min-h-screen">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-admin.stat label="Total Orders" value="{{ $stats['total'] }}" icon="package" />
            <x-admin.stat label="Pending" value="{{ $stats['pending'] }}" icon="truck" />
            <x-admin.stat label="Revenue" value="{{ number_format($stats['revenue'], 2) }} DH" icon="dollar" />
            <x-admin.stat label="Cancelled" value="{{ $stats['cancelled'] }}" icon="x-circle" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            {{-- INNOVATED MAP CARD --}}
            <div class="lg:col-span-2">
                <x-admin.card class="h-[600px] flex flex-col p-0 overflow-hidden relative border-none shadow-2xl rounded-[2.5rem]">
                    <div class="relative w-full h-full flex-1 map-container-dark" 
                        x-data="dashboardMap({{ Js::from($mappedOrders) }}, {{ Js::from($depotLocation) }})" 
                        x-init="init()">
                        
                        {{-- Leaflet Map Container --}}
                        <div wire:ignore x-ref="map" class="absolute inset-0 w-full h-full z-0"></div>

                        {{-- CONTROL OVERLAY --}}
                        <div class="absolute top-6 right-6 z-[1000] flex flex-col space-y-3">
                            {{-- Live Indicator --}}
                            <div class="bg-black/40 backdrop-blur-xl px-4 py-2 rounded-xl border border-white/10 shadow-lg flex items-center space-x-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                <span class="text-[10px] font-bold text-white uppercase tracking-widest">Live Tracking</span>
                            </div>
                            
                            {{-- Filters --}}
                            <div class="bg-white/95 backdrop-blur-xl p-2 rounded-xl shadow-lg border border-white/20">
                                <div class="flex flex-col space-y-1">
                                    <button @click="toggleFilter('all')" 
                                            :class="{'bg-slate-800 text-white': filters.all, 'bg-gray-100 text-gray-600': !filters.all}"
                                            class="map-filter-btn text-left px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        All Orders
                                    </button>
                                    <button @click="toggleFilter('transit')" 
                                            :class="{'bg-blue-500 text-white': filters.transit, 'bg-gray-100 text-gray-600': !filters.transit}"
                                            class="map-filter-btn text-left px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        🚚 In Transit
                                    </button>
                                    <button @click="toggleFilter('pending')" 
                                            :class="{'bg-amber-500 text-white': filters.pending, 'bg-gray-100 text-gray-600': !filters.pending}"
                                            class="map-filter-btn text-left px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        ⏳ Pending
                                    </button>
                                    <button @click="toggleFilter('delivered')" 
                                            :class="{'bg-emerald-500 text-white': filters.delivered, 'bg-gray-100 text-gray-600': !filters.delivered}"
                                            class="map-filter-btn text-left px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        ✅ Delivered
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- LEGEND --}}
                        <div class="absolute bottom-6 left-6 p-4 bg-white/95 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 z-[1000]">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Map Legend</h4>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-indigo-500 border-2 border-white shadow-sm"></div>
                                    <span class="text-[11px] font-bold text-gray-600">Depot</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-amber-500 shadow-sm"></div>
                                    <span class="text-[11px] font-bold text-gray-600">Pending</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-blue-500 shadow-sm"></div>
                                    <span class="text-[11px] font-bold text-gray-600">In Transit</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm"></div>
                                    <span class="text-[11px] font-bold text-gray-600">Delivered</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            {{-- Weekly Chart Card (Unchanged) --}}
            <x-admin.card class="h-[600px] flex flex-col">
                <div class="mb-6">
                    <h3 class="font-bold text-gray-800">Weekly Volume</h3>
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">Orders per day</p>
                </div>

                <div class="flex-1 flex flex-col justify-end space-y-4">
                    <div class="flex items-end justify-between h-64 space-x-2">
                        @foreach($weeklyData as $data)
                            @php $height = ($data['count'] / $maxCount) * 100; @endphp
                            <div class="w-full bg-primary/10 rounded-t-xl hover:bg-primary transition-all duration-500 cursor-pointer group relative" 
                                style="height: {{ max($height, 5) }}%">
                                <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] px-2.5 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-30 shadow-xl whitespace-nowrap">
                                    <span class="font-bold">{{ $data['count'] }}</span> orders
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-[10px] text-gray-400 font-bold uppercase pt-4 border-t border-gray-50">
                        @foreach($weeklyData as $data)
                            <span class="w-full text-center">{{ $data['day'] }}</span>
                        @endforeach
                    </div>
                </div>
            </x-admin.card>
        </div>

        <livewire:admin.orders-table />
    </div>

    @push('scripts')
    {{-- Include Leaflet JS (assumed already in layout) --}}
    <script>
        window.dashboardMap = function(ordersJson, depotCoords) {
            return {
                map: null,
                orders: ordersJson,
                depot: depotCoords,
                markers: [],
                routes: [],
                filters: {
                    all: true,
                    pending: true,
                    transit: true,
                    delivered: true
                },
                
                init() {
                    this.$nextTick(() => this.setupMap());
                    
                    // SIMULATION: "Live" movement for In-Transit orders
                    // In a real app, this would be a WebSocket listener
                    setInterval(() => this.simulateMovement(), 3000);
                },

                setupMap() {
                    if(this.map) this.map.remove();

                    this.map = L.map(this.$refs.map, {
                        zoomControl: false,
                        attributionControl: false
                    }).setView(this.depot, 13);

                    // Innovative Dark Tile Layer
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                        maxZoom: 19
                    }).addTo(this.map);

                    // Add Depot Marker
                    L.marker(this.depot, {
                        icon: L.divIcon({
                            className: 'custom-marker marker-depot',
                            html: '<span>HQ</span>',
                            iconSize: [36, 36],
                            iconAnchor: [18, 18]
                        })
                    }).addTo(this.map).bindPopup("<b>Central Depot</b>");

                    // Render initial markers
                    this.renderMarkers();
                },

                renderMarkers() {
                    // Clear existing
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.routes.forEach(r => this.map.removeLayer(r));
                    this.markers = [];
                    this.routes = [];

                    this.orders.forEach(order => {
                        if (!order.lat || !order.lng) return;

                        // Check Filters
                        const statusKey = order.status === 'in transit' ? 'transit' : order.status;
                        if (!this.filters.all && !this.filters[statusKey]) return;

                        // Create Icon
                        const iconClass = `custom-marker marker-${order.status === 'in transit' ? 'transit' : order.status}`;
                        const icon = L.divIcon({
                            className: iconClass,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });

                        // Create Marker
                        const marker = L.marker([order.lat, order.lng], { icon })
                            .bindPopup(`
                                <div class="p-1 min-w-[150px]">
                                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Order #${order.code}</div>
                                    <div class="font-bold text-gray-900">${order.client}</div>
                                    <div class="text-xs text-gray-500 mt-1 truncate">${order.location}</div>
                                    <div class="mt-2 flex justify-between items-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-${this.getColor(order.status)}-100 text-${this.getColor(order.status)}-600">${order.status}</span>
                                    </div>
                                </div>
                            `)
                            .addTo(this.map);

                        this.markers.push(marker);

                        // TRACE: Draw route for In-Transit orders
                        if (order.status === 'in transit') {
                            const route = L.polyline([this.depot, [order.lat, order.lng]], {
                                className: 'animated-route',
                                weight: 2,
                                color: '#3b82f6',
                                dashArray: '5, 10'
                            }).addTo(this.map);
                            
                            this.routes.push(route);
                        }
                    });
                },

                toggleFilter(type) {
                    if (type === 'all') {
                        this.filters.all = !this.filters.all;
                        // If activating 'all', activate others. If deactivating, clear others (optional UX choice)
                        if(this.filters.all) {
                            this.filters.pending = true;
                            this.filters.transit = true;
                            this.filters.delivered = true;
                        }
                    } else {
                        // If clicking a specific filter while 'all' is active, switch to exclusive mode
                        if(this.filters.all) {
                            this.filters.all = false;
                            // Reset others to false, then toggle the clicked one
                            this.filters.pending = false;
                            this.filters.transit = false;
                            this.filters.delivered = false;
                            this.filters[type] = true; 
                        } else {
                             this.filters[type] = !this.filters[type];
                        }
                        
                        // If all specific filters are on, turn 'all' back on
                        if (this.filters.pending && this.filters.transit && this.filters.delivered) {
                            this.filters.all = true;
                        }
                    }
                    
                    this.renderMarkers();
                },

                // Helper for color class
                getColor(status) {
                    if(status === 'pending') return 'amber';
                    if(status === 'in transit') return 'blue';
                    if(status === 'delivered') return 'emerald';
                    return 'gray';
                },

                // INNOVATION: Simulation of live GPS updates
                simulateMovement() {
                    let needsUpdate = false;
                    
                    this.orders = this.orders.map(o => {
                        if (o.status === 'in transit') {
                            // Move slightly towards destination (simulation logic)
                            // In real app, this data comes from backend push
                            needsUpdate = true;
                            return {
                                ...o,
                                // Tiny random jitter to simulate GPS drift/movement
                                lat: o.lat + (Math.random() - 0.5) * 0.001,
                                lng: o.lng + (Math.random() - 0.5) * 0.001
                            };
                        }
                        return o;
                    });

                    // Efficiently update position without destroying popups
                    if(needsUpdate) {
                        this.markers.forEach((marker, idx) => {
                            const order = this.orders[idx];
                            if(order && order.status === 'in transit') {
                                marker.setLatLng([order.lat, order.lng]);
                            }
                        });
                        
                        this.routes.forEach((route, idx) => {
                             const order = this.orders[idx]; // Simple mapping assumption
                             if(order && order.status === 'in transit') {
                                 route.setLatLngs([this.depot, [order.lat, order.lng]]);
                             }
                        });
                    }
                }
            };
        };
    </script>
@endpush
</x-admin-layout>