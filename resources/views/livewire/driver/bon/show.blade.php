<?php

use App\Models\Bon;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
new #[Layout('layouts.driver')] class extends Component
{
    public Bon $bon;
    public $orders;
    public $qrCode;
    public $order;

    public $activeBonId = null;

    public function mount(Bon $bon)
    {
        $this->bon = $bon;
        $this->activeBonId = $bon->id;
        $this->loadOrders();
    }

    public function loadOrders()
    {
        if (!$this->activeBonId) {
            $this->orders = collect();
            return;
        }

        $this->orders = Order::with(['recipient', 'bon'])
            ->where('bon_driver_id', $this->activeBonId)
            ->latest()
            ->get();
    }

    public function findOrder()
    {
        if (!$this->qrCode) {
            $this->order = null;
            return;
        }

        $this->order = Order::with(['recipient', 'bon'])
            ->where('code', $this->qrCode)
            ->first();

        if (!$this->order) {
            session()->flash('error', 'Order not found.');
        } else {
            session()->forget('error');
        }
    }

    public function updatedQrCode($value)
    {
        $this->findOrder();
    }

    public function searchOrder()
    {
        $this->findOrder();
    }

    public function assignToMe()
    {
        $driver = Auth::user()->driver;
        
        if (!$driver) {
            session()->flash('error', 'Driver profile not found.');
            return;
        }

        if ($this->order && strtolower($this->order->status) === 'pending') {
            $this->order->update([
                'driver_id' => $driver->id,
                'status' => 'assigned',
                'bon_driver_id' => $this->activeBonId ?? null,
            ]);
            
            session()->flash('success', 'Order #' . $this->order->code . ' has been assigned to you.');
            
            $this->loadOrders();
            $this->order->refresh();
        } else {
            session()->flash('error', 'Order cannot be assigned. It might not be pending.');
        }
    }

    public function markAsDelivered()
    {
        if (!$this->order || strtolower($this->order->status) === 'delivered') {
            return;
        }

        $this->order->update(['status' => 'delivered']);
        
        session()->flash('success', 'Order #' . $this->order->code . ' marked as delivered.');
        
        $this->loadOrders();
        $this->order->refresh();
    }
}
?>
<div class="p-4 md:p-6 lg:p-8 space-y-8" 
    x-data="{ 
    scanner: null,
    scanning: false,

    async startScan() {
        this.scanning = true;

        this.scanner = new Html5Qrcode('qr-reader');

        try {
            await this.scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 15,
                    qrbox: { width: 250, height: 250 },
                    formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ] // ✅ ONLY QR
                },
                (decodedText) => {
                    let cleanCode = decodedText.replace(/^(Order:\s*|Bon:\s*)/i, '').trim();
                    @this.set('qrCode', cleanCode);

                    // Optional: small delay to avoid double scan
                    setTimeout(() => {
                        this.stopScan();
                    }, 300);
                },
                (errorMessage) => {
                    // ignore scan errors (normal)
                }
            );
        } catch (err) {
            console.error('Scan error:', err);
            this.scanning = false;
            alert('Camera access failed. Check permissions.');
        }
    },

    stopScan() {
        if (this.scanner) {
            this.scanner.stop()
                .then(() => {
                    this.scanning = false;
                    this.scanner.clear(); // ✅ clean UI
                })
                .catch(err => console.error(err));
        } else {
            this.scanning = false;
        }
    }
}"

    x-init="lucide.createIcons(); startScan()"

>
<!-- layout.blade.php -->

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 transition-all duration-500">
        <div class="space-y-1">
            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-black uppercase tracking-widest mb-2">
                <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                <span>Real-time Scanner</span>
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">Bon <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">#{{ $bon->code }}</span></h1>
            <p class="text-gray-500 font-medium text-lg">Scan orders to add them to this delivery run.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
            <button 
                @click="startScan()" 
                x-show="!scanning"
                class="group relative flex items-center justify-center space-x-3 w-full sm:w-auto px-6 py-4 bg-primary text-white rounded-[1.5rem] font-bold text-sm shadow-xl shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
            >
                <i data-lucide="scan-line" class="w-5 h-5 group-hover:rotate-90 transition-transform duration-500"></i>
                <span>Initialize Scanner</span>
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center">
                    <div class="w-2 h-2 bg-primary rounded-full animate-ping"></div>
                </div>
            </button>

            <button 
                @click="stopScan()" 
                x-show="scanning" 
                x-cloak
                class="flex items-center justify-center space-x-3 w-full sm:w-auto px-6 py-4 bg-red-500 text-white rounded-[1.5rem] font-bold text-sm shadow-xl shadow-red-500/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
            >
                <i data-lucide="stop-circle" class="w-5 h-5"></i>
                <span>Disable Scanner</span>
            </button>
        </div>
    </div>

    <!-- Alert Messaging -->
    @if (session()->has('error'))
        <div class="p-6 bg-red-50 border-l-4 border-red-500 rounded-3xl flex items-center space-x-4 animate-in slide-in-from-right-4 duration-500">
            <div class="w-12 h-12 bg-red-500/10 rounded-2xl flex items-center justify-center text-red-500 shadow-inner">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-black text-red-600 uppercase tracking-widest">Identification Error</p>
                <p class="text-red-500 font-medium mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
        <!-- Left: Scanning Interface -->
        <div class="space-y-6">
            <div class="bg-white p-2 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-100/40 group overflow-hidden">
                <div 
                    id="qr-reader" 
                    wire:ignore 
                    class="overflow-hidden rounded-[2rem] bg-[#f8fafc] border-4 border-dashed border-gray-100 aspect-square flex flex-col items-center justify-center relative transition-all duration-500"
                    :class="scanning ? 'border-primary/20 scale-[1.01] shadow-2xl shadow-primary/10' : ''"
                >
                    <div x-show="!scanning" class="text-center p-6 sm:p-12 space-y-6">
                        <div class="relative inline-block">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded-3xl shadow-lg shadow-gray-200/50 flex items-center justify-center mx-auto text-gray-200 group-hover:text-primary transition-colors duration-500">
                                <i data-lucide="aperture" class="w-12 h-12 group-hover:rotate-180 transition-transform duration-1000"></i>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-primary/5 rounded-full blur-xl"></div>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Hardware Ready</p>
                            <p class="text-gray-400 font-medium max-w-[240px] mx-auto text-sm">Click the button above to activate the wide-angle camera scanner.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Entry Field -->
            <div class="bg-gray-50/80 backdrop-blur-sm p-6 rounded-[2rem] border border-gray-100 space-y-4">
                
                <div class="flex items-center justify-between px-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
                        Can't scan? Enter code
                    </label>
                    <i data-lucide="keyboard" class="w-4 h-4 text-gray-300"></i>
                </div>

                <div class="relative group flex items-center">
                    
                    <input 
                        type="text" 
                        wire:model.debounce.500ms="qrCode"
                        class="w-full pl-14 pr-28 py-5 bg-white border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary shadow-sm hover:shadow-md transition-all text-base font-semibold tracking-tight placeholder-gray-300"
                        placeholder="TRACK-001-XYZ"
                    >

                    <!-- Icon -->
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                        <i data-lucide="hash" class="w-6 h-6"></i>
                    </div>

                    <!-- Button -->
                    <button 
                        type="button"
                        wire:click="searchOrder"
                        class="absolute right-3 px-4 py-2 text-sm font-semibold bg-primary text-white rounded-xl shadow hover:bg-primary/90 transition"
                    >
                        Search
                    </button>

                </div>
            </div>
        </div>

        <!-- Right: Intelligent Result Display -->
        <div class="lg:h-full flex flex-col">
            @if($order)
                <div class="bg-gradient-to-br from-white to-gray-50/30 p-6 md:p-10 rounded-3xl md:rounded-[3rem] border border-gray-100 shadow-2xl shadow-primary/5 animate-in zoom-in-95 duration-500 flex-1 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full -mr-32 -mt-32 blur-3xl group-hover:bg-primary/10 transition-colors duration-1000"></div>
                    
                    <div class="relative z-10 h-full flex flex-col">
                        <div class="flex items-start justify-between mb-10">
                            <div>
                                <span class="px-4 py-1.5 bg-green-50 text-green-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-green-100">Live Identification</span>
                                <h2 class="text-4xl font-black text-gray-900 mt-4 tracking-tighter">#{{ $order->code }}</h2>
                            </div>
                            <div class="w-20 h-20 bg-white rounded-3xl shadow-xl shadow-gray-200/50 flex items-center justify-center text-primary border border-gray-50 transform hover:rotate-6 transition-transform duration-500">
                                <i data-lucide="box" class="w-10 h-10"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:gap-8 mb-6 sm:mb-10 text-pretty">
                            <div class="space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Recipient Name</p>
                                <p class="text-xl font-bold text-gray-800">{{ $order->recipient->name ?? 'None' }}</p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Current Status</p>
                                <div>
                                    <span class="inline-flex px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest
                                        @if($order->status == 'pending') bg-yellow-400/10 text-yellow-600
                                        @elseif($order->status == 'delivered') bg-green-400/10 text-green-600
                                        @else bg-gray-400/10 text-gray-600 @endif">
                                        {{ $order->status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-white/50 backdrop-blur-sm rounded-[2rem] border border-white shadow-inner mb-auto group/adr cursor-pointer hover:bg-white transition-colors">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Delivery Coordinate</p>
                            <div class="flex items-center space-x-3 text-gray-700">
                                <div class="w-8 h-8 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover/adr:scale-110 transition-transform">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </div>
                                <p class="font-bold text-lg leading-tight">{{ $order->location }}</p>
                            </div>
                        </div>

                        <div class="mt-10 pt-8 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Price & Fee</p>
                                <p class="text-3xl font-black text-gray-900 mt-1">{{ number_format($order->price, 2) }} <span class="text-sm font-medium text-gray-400">DH</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Driver Earning</p>
                                <p class="text-3xl font-black text-primary mt-1">+{{ number_format($order->driver_commission, 2) }} <span class="text-sm font-medium text-primary/50">DH</span></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col gap-3">
                            @if($order->driver_id !== auth()->user()->driver?->id && strtolower($order->status) === 'pending')
                                <button wire:click="assignToMe" wire:loading.attr="disabled" class="w-full py-4 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center space-x-2">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    <span>Accept Delivery & Assign to Route</span>
                                </button>
                            @elseif($order->driver_id === auth()->user()->driver?->id && strtolower($order->status) !== 'delivered')
                                <button wire:click="markAsDelivered" wire:confirm="Confirm delivery completion?" class="w-full py-4 bg-green-500 text-white font-bold rounded-2xl shadow-lg shadow-green-500/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center space-x-2">
                                    <i data-lucide="package-check" class="w-5 h-5"></i>
                                    <span>Mark as Delivered</span>
                                </button>
                            @elseif(strtolower($order->status) === 'delivered')
                                <div class="w-full py-4 bg-gray-50 text-green-600 font-bold rounded-2xl flex items-center justify-center space-x-2 border border-green-100">
                                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                                    <span>Already Delivered</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="flex-1 bg-gray-50/40 border-4 border-dashed border-gray-100 p-8 sm:p-16 rounded-3xl md:rounded-[3rem] flex flex-col items-center justify-center text-center group">
                    <div class="relative mb-8">
                        <div class="w-32 h-32 bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/50 flex items-center justify-center text-gray-100 group-hover:scale-110 transition-transform duration-700">
                            <i data-lucide="search" class="w-16 h-16 opacity-30"></i>
                        </div>
                        <div class="absolute -top-4 -right-4 w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center animate-bounce">
                            <i data-lucide="help-circle" class="w-6 h-6 text-gray-200"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-gray-400 tracking-tight">No Data Streamed</h3>
                    <p class="text-gray-400 font-medium max-w-[280px] mt-4 leading-relaxed">System is awaiting valid QR code or tracking ID input to fetch the cryptographic order record.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Active Stream Table -->
    <div class="space-y-6 pt-12">
        <div class="flex items-center justify-between px-2">
            <div>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">Active Deliveries</h3>
                <p class="text-sm text-gray-400 font-medium">Items currently assigned to your shift</p>
            </div>
            <div class="px-5 py-2 bg-gray-900 text-white rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-black/10">
                {{ $orders->count() }} Records Found
            </div>
        </div>

        <div class="overflow-hidden bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-gray-100/20">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Order Unit</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Client Details</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Destination</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Valuation</th>
                            <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Logistics Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50/60" x-init="$nextTick(() => lucide.createIcons())">
                        @forelse($orders as $order)
                            <tr class="hover:bg-primary/[0.02] transition-colors group cursor-pointer" wire:click="$set('qrCode', '{{ $order->code }}')">
                                <td class="px-8 py-7 whitespace-nowrap">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                            <i data-lucide="package" class="w-5 h-5"></i>
                                        </div>
                                        <span class="text-base font-black text-gray-900">#{{ $order->code }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-7 whitespace-nowrap">
                                    <p class="text-sm font-black text-gray-800">{{ $order->recipient->name ?? 'N/A' }}</p>
                                    <p class="text-[10px] text-gray-300 font-bold uppercase tracking-widest mt-1">Verified Recipient</p>
                                </td>
                                <td class="px-8 py-7 whitespace-nowrap">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center">
                                            <i data-lucide="navigation" class="w-3.5 h-3.5 text-gray-300 group-hover:text-primary transition-colors"></i>
                                        </div>
                                        <span class="text-sm font-bold text-gray-500">{{ Str::limit($order->location, 30) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-7 whitespace-nowrap">
                                    <div class="space-y-0.5">
                                        <p class="text-base font-black text-gray-900">{{ number_format($order->price, 2) }} DH</p>
                                        <p class="text-[10px] font-black text-primary uppercase">+{{ number_format($order->driver_commission, 2) }} Fee</p>
                                    </div>
                                </td>
                                <td class="px-8 py-7 whitespace-nowrap">
                                    <span class="inline-flex px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest
                                        @if($order->status == 'pending') bg-yellow-400/10 text-yellow-600
                                        @elseif($order->status == 'delivered') bg-green-400/10 text-green-600
                                        @else bg-gray-400/10 text-gray-600 @endif transition-all group-hover:scale-105">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center text-gray-400">
                                    <div class="flex flex-col items-center space-y-4">
                                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                                            <i data-lucide="ghost" class="w-10 h-10 text-gray-100"></i>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-lg font-black text-gray-300 uppercase tracking-tighter">Silence in the Hub</p>
                                            <p class="text-sm font-medium text-gray-300">No active delivery assignments found for this account.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Stacked Card View -->
            <div class="md:hidden divide-y divide-gray-50/60">
                @forelse($orders as $order)
                    <div wire:click="$set('qrCode', '{{ $order->code }}')" class="p-5 flex flex-col gap-4 hover:bg-primary/[0.02] transition-colors cursor-pointer group active:scale-[0.99]">
                        <!-- Header -->
                        <div class="flex flex-row justify-between items-start gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-12 w-12 flex-shrink-0 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                    <i data-lucide="package" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-gray-900">#{{ $order->code }}</h4>
                                    <p class="text-[10px] font-bold text-gray-800 mt-0.5">{{ $order->recipient->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="px-3 py-1 inline-flex text-[10px] font-black rounded-full uppercase tracking-widest
                                    @if($order->status == 'pending') bg-yellow-400/10 text-yellow-600
                                    @elseif($order->status == 'delivered') bg-green-400/10 text-green-600
                                    @else bg-gray-400/10 text-gray-600 @endif group-hover:scale-105 transition-all">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>

                        <!-- Data Grid -->
                        <div class="grid grid-cols-[1fr_auto] gap-4 py-3 border-t border-dashed border-gray-100">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Destination</p>
                                <div class="flex items-start space-x-2 mt-1.5 pr-2">
                                    <i data-lucide="navigation" class="w-3.5 h-3.5 text-gray-300 mt-0.5 flex-shrink-0"></i>
                                    <span class="text-xs font-bold text-gray-600 leading-tight">{{ Str::limit($order->location, 35) }}</span>
                                </div>
                            </div>
                            <div class="text-right border-l border-gray-100 pl-4 w-28">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pricing</p>
                                <p class="text-sm font-black text-gray-900 mt-1">{{ number_format($order->price, 2) }}<span class="text-[10px] text-gray-400 ml-1">DH</span></p>
                                <p class="text-[9px] font-black text-primary uppercase mt-0.5">+{{ number_format($order->driver_commission, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-8 py-16 text-center text-gray-400">
                        <div class="flex flex-col items-center space-y-4">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center">
                                <i data-lucide="ghost" class="w-8 h-8 text-gray-200"></i>
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-black text-gray-300 uppercase tracking-tighter">Silence in the Hub</p>
                                <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest">No active delivery assignments.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>