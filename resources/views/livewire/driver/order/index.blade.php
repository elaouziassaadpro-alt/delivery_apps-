<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.driver')] class extends Component
{
    public $orders;
    public string $scanCode = '';
    public ?string $scanError = null;

    public function mount()
    {
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $this->orders = Order::with(['recipient', 'bon'])
            ->where('driver_id', Auth::user()->driver->id)
            ->latest()
            ->get();
    }

    public function findOrderByCode()
    {
        $this->scanError = null;
        $code = trim($this->scanCode);

        if (empty($code)) {
            $this->scanError = 'Please enter or scan an order code.';
            return;
        }

        $order = Order::where('code', $code)->first();

        if (!$order) {
            $this->scanError = 'No order found with code: ' . $code;
            return;
        }

        $this->redirect(route('driver.orders.show', $order), navigate: true);
    }
}
?>

<div class="p-4 md:p-6 lg:p-8 space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 transition-all duration-500">
        <div class="space-y-1">
            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-black uppercase tracking-widest mb-2">
                <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                <span>My Deliveries</span>
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">Orders <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">Dashboard</span></h1>
            <p class="text-gray-500 font-medium text-lg">Manage your delivery assignments and track progress.</p>
        </div>
        
        <div class="flex items-center gap-4 w-full md:w-auto">
            <button 
                wire:click="$dispatch('open-order-scanner')" 
                class="group relative flex items-center justify-center space-x-3 px-6 py-4 bg-primary text-white rounded-[1.5rem] font-bold text-sm shadow-xl shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
            >
                <i data-lucide="scan-line" class="w-5 h-5 group-hover:rotate-90 transition-transform duration-500"></i>
                <span>Scan Order</span>
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center">
                    <div class="w-2 h-2 bg-primary rounded-full animate-ping"></div>
                </div>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Total Orders</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $orders->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="package" class="w-7 h-7"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Pending</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $orders->where('status', 'pending')->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-yellow-500/10 rounded-2xl flex items-center justify-center text-yellow-500 group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="clock" class="w-7 h-7"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">In Transit</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $orders->where('status', 'In Transit')->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="truck" class="w-7 h-7"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Delivered</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $orders->where('status', 'delivered')->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-green-500/10 rounded-2xl flex items-center justify-center text-green-500 group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="check-circle" class="w-7 h-7"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i data-lucide="list-ordered" class="w-5 h-5 me-2 text-primary"></i>
                All Orders
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/60 hidden md:table-header-group">
                    <tr>
                        <th class="px-4 md:px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Order Code</th>
                        <th class="px-4 md:px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Bon</th>
                        <th class="px-4 md:px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Client</th>
                        <th class="px-4 md:px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-4 md:px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest hidden lg:table-cell">Payment</th>
                        <th class="px-4 md:px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 flex flex-col md:table-row-group">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/50 transition-colors flex flex-col md:table-row p-4 md:p-0">
                            <!-- Mobile format -->
                            <td class="px-2 md:px-8 py-2 md:py-5 flex justify-between items-center md:table-cell border-b border-gray-100 md:border-none">
                                <span class="md:hidden text-xs font-bold text-gray-400 uppercase tracking-widest">Order Code</span>
                                <span class="font-bold text-gray-900">#{{ $order->code }}</span>
                            </td>
                            <td class="px-2 md:px-8 py-2 md:py-5 flex justify-between items-center md:table-cell border-b border-gray-100 md:border-none">
                                <span class="md:hidden text-xs font-bold text-gray-400 uppercase tracking-widest">Bon</span>
                                <a href="{{ route('driver.bons.show', $order->bonDriver->code) }}" class="font-bold text-primary hover:text-primary/80 transition-colors">
                                    #{{ $order->bonDriver->code ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="px-2 md:px-8 py-2 md:py-5 flex justify-between items-center md:table-cell border-b border-gray-100 md:border-none">
                                <span class="md:hidden text-xs font-bold text-gray-400 uppercase tracking-widest">Client</span>
                                <div class="text-right md:text-left">
                                    <div class="font-bold text-gray-900">{{ $order->client->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->client->phone ?? '' }}</div>
                                </div>
                            </td>
                            <td class="px-2 md:px-8 py-2 md:py-5 flex justify-between items-center md:table-cell border-b border-gray-100 md:border-none">
                                <span class="md:hidden text-xs font-bold text-gray-400 uppercase tracking-widest">Status</span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                                    @if(strtolower($order->status) === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif(strtolower($order->status) === 'assigned') bg-blue-100 text-blue-700
                                    @elseif(strtolower($order->status) === 'delivered') bg-green-100 text-green-700
                                    @elseif(strtolower($order->status) === 'returned') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-2 md:px-8 py-2 md:py-5 flex justify-between items-center md:table-cell hidden lg:table-cell border-b border-gray-100 md:border-none">
                                <span class="md:hidden text-xs font-bold text-gray-400 uppercase tracking-widest">Payment</span>
                                <div class="text-right md:text-left">
                                    <span class="font-bold {{ strtolower($order->payment_status) === 'paid' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                    <div class="text-sm text-gray-500">{{ $order->price }} DH</div>
                                </div>
                            </td>
                            <td class="px-2 md:px-8 py-3 md:py-5 flex justify-end items-center md:table-cell">
                                <a href="{{ route('driver.orders.show', ['order' => $order]) }}" class="inline-flex items-center justify-center space-x-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 hover:bg-primary hover:text-white transition-all duration-300 md:w-10 md:h-10 md:px-0">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <span class="md:hidden font-bold text-sm">View Details</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-12 text-center text-gray-500 block md:table-cell">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <i data-lucide="package-x" class="w-12 h-12 text-gray-300"></i>
                                    <span class="text-lg font-medium">No orders found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Scanner Modal -->
    <div
        x-data="{
            open: false,
            scanner: null,
            scanning: false,

            async startScan() {
                this.scanning = true;
                this.scanner = new Html5Qrcode('order-qr-reader');
                try {
                    await this.scanner.start(
                        { facingMode: 'environment' },
                        { fps: 15, qrbox: { width: 220, height: 220 } },
                        (decodedText) => {
                            let cleanCode = decodedText.replace(/^(Order:\s*|Bon:\s*)/i, '').trim();
                            $wire.set('scanCode', cleanCode);
                            this.stopScan();
                            $wire.findOrderByCode();
                        },
                        () => {}
                    );
                } catch (err) {
                    this.scanning = false;
                    alert('Camera access failed. Check permissions.');
                }
            },

            stopScan() {
                if (this.scanner) {
                    this.scanner.stop()
                        .then(() => { this.scanning = false; this.scanner.clear(); })
                        .catch(err => console.error(err));
                } else {
                    this.scanning = false;
                }
            },

            closeModal() {
                this.stopScan();
                this.open = false;
                $wire.set('scanCode', '');
                $wire.set('scanError', null);
            }
        }"
        @open-order-scanner.window="open = true; $nextTick(() => startScan())"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
    >
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
            @click="closeModal()"
        ></div>

        <!-- Modal Card -->
        <div
            class="relative w-full max-w-md bg-white rounded-[2rem] shadow-2xl p-6 space-y-5 z-10"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-8"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="inline-flex items-center space-x-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-black uppercase tracking-widest mb-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                        <span>Order Scanner</span>
                    </div>
                    <h2 class="text-xl font-black text-gray-900">Scan an Order</h2>
                </div>
                <button @click="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- QR Viewfinder -->
            <div class="bg-gray-50 rounded-2xl overflow-hidden border-2 border-dashed border-gray-200 aspect-square flex items-center justify-center relative">
                <div id="order-qr-reader" wire:ignore class="w-full h-full"></div>
                <div x-show="!scanning" class="absolute inset-0 flex flex-col items-center justify-center space-y-3 text-gray-300">
                    <i data-lucide="scan-line" class="w-16 h-16"></i>
                    <p class="text-xs font-bold uppercase tracking-widest">Camera starting...</p>
                </div>
            </div>

            <!-- Stop / Restart scan button -->
            <div class="flex gap-2">
                <button
                    x-show="scanning"
                    @click="stopScan()"
                    class="flex-1 flex items-center justify-center space-x-2 py-3 bg-red-50 text-red-500 border border-red-100 rounded-xl font-bold text-sm hover:bg-red-100 transition-colors"
                >
                    <i data-lucide="stop-circle" class="w-4 h-4"></i>
                    <span>Stop Camera</span>
                </button>
                <button
                    x-show="!scanning"
                    @click="startScan()"
                    class="flex-1 flex items-center justify-center space-x-2 py-3 bg-primary/10 text-primary rounded-xl font-bold text-sm hover:bg-primary/20 transition-colors"
                >
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Restart Camera</span>
                </button>
            </div>

            <!-- Manual entry -->
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Can't scan? Enter code manually</label>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i data-lucide="hash" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input
                            type="text"
                            wire:model="scanCode"
                            wire:keydown.enter="findOrderByCode"
                            placeholder="e.g. ORD-001-XYZ"
                            class="w-full pl-9 pr-3 py-3 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                        >
                    </div>
                    <button
                        wire:click="findOrderByCode"
                        wire:loading.attr="disabled"
                        class="px-4 py-3 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20"
                    >
                        <span wire:loading.remove wire:target="findOrderByCode">Go</span>
                        <span wire:loading wire:target="findOrderByCode"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i></span>
                    </button>
                </div>

                @if($scanError)
                    <div class="flex items-center space-x-2 text-red-500 text-sm font-bold bg-red-50 px-3 py-2 rounded-xl border border-red-100">
                        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                        <span>{{ $scanError }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>