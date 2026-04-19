<?php

use App\Models\Bon;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component 
{   
    use WithPagination;

    #[Url(history: true)]
    public $search = '';
    public $showBonDetails = false;
    public $bon = null;
    public $is_completed = '';
    public $showBonDriver = true;
    public $role = 'driver';

    
    public function updatingSearch() { $this->resetPage(); }
    public function updatingIsCompleted() { $this->resetPage(); }

    public function showBon($id)
    {
        $this->bon = Bon::with(['user', 'orders.recipient', 'orders_driver.recipient'])->findOrFail($id);
        $this->showBonDetails = true;
    }
    public function generateQrCode()
    {
        if (!$this->bon || !$this->bon->code) {
            return '';
        }

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($this->bon->code);

        return $qrUrl;
    }

    public function closeBonDetails()
    {
        $this->showBonDetails = false;
        $this->bon = null;
        $this->showBonDriver = true;
    }
    

    

   

    public function editBon($id)
    {
        return redirect()->route('admin.bons.driver.edit', $id);
    }

    public function deleteBon($id)
    {
        $bon = Bon::findOrFail($id);
        $bon->delete();
        
        session()->flash('status', __('Bon deleted successfully.'));
    }

    public function with(): array
    {
        return [
            'bons' => Bon::query()
                ->with(['user', 'orders_driver'])
                ->whereHas('user', function ($q) {
                    $q->where('role', $this->role);
                })
                ->where(function ($query) {
                    $searchTerm = '%' . $this->search . '%';
                    $query->where('code', 'like', $searchTerm)
                          ->orWhereHas('user', function ($q2) use ($searchTerm) {
                              $q2->where('name', 'like', $searchTerm);
                          });
                })
                ->when($this->is_completed !== '', function ($query) {
                    $query->where('is_completed', $this->is_completed);
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">
    <x-slot name="header">
        {{ __('Delivery Notes') }}
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">{{ __('Bons') }}</span>
        </span>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.bons.driver.create') }}" class="px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center">
            <i data-lucide="plus-circle" class="w-4 h-4 me-2"></i>
            {{ __('Create Bon') }}
        </a>
    </x-slot>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="text-gray-400 w-5 h-5"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium" placeholder="{{ __('Search bons') }}...">
            </div>
            <div class="relative w-full md:w-48">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="filter" class="text-gray-400 w-4 h-4"></i>
                    </span>
                    <select wire:model.live="is_completed" class="block w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium appearance-none">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="1">{{ __('Completed') }}</option>
                        <option value="0">{{ __('Not Completed') }}</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i data-lucide="chevron-down" class="text-gray-400 w-4 h-4"></i>
                    </div>
                </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2 text-sm text-gray-500 font-medium border-l pl-4 border-gray-100">
                    <i data-lucide="package" class="w-5 h-5 text-primary"></i>
                    <span>{{ __('Total') }}: {{ $bons->total() }}</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>

                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('user') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Code') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Date') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Client') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Orders') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Amount') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Status') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Completed') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">{{ __('Actions') }}</th>
                    </tr>

                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bons as $bon)
                        @php
                            $status_color = match($bon->status) {
                                'delivered'  => 'bg-green-100 text-green-800 outline-green-200',
                                'pending'    => 'bg-yellow-100 text-yellow-800 outline-yellow-200',
                                'processing' => 'bg-blue-100 text-blue-800 outline-blue-200',
                                'completed'  => 'bg-green-100 text-green-800 outline-green-200',
                                'cancelled'  => 'bg-red-100 text-red-800 outline-red-200',
                                default      => 'bg-gray-100 text-gray-800 outline-gray-200',
                            };
                        @endphp
                        
                        <tr class="hover:bg-gray-50/30 transition-colors group" wire:key="driver-{{ $bon->id }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    
                                    <!-- Avatar -->
                                    <div class="relative">
                                        <x-user-avatar 
                                            :user="$bon->user" 
                                            class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm transition-transform duration-200 hover:scale-105"
                                        />
                                    </div>

                                    <!-- User Info -->
                                    <div class="leading-tight">
                                        <p class="font-semibold text-gray-900">
                                            {{ $bon->user?->name ?? 'Unknown User' }}
                                        </p>

                                        <p class="text-xs text-gray-400">
                                            ID: 
                                            #USR-{{ $bon->user ? str_pad($bon->user->id, 4, '0', STR_PAD_LEFT) : '----' }}
                                        </p>
                                    </div>

                                </div>
                            </td>
 

                            <td class="px-8 py-5 font-black text-gray-900">#{{ $bon->code }}</td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-bold text-gray-600">{{ $bon->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900">{{ $bon->user?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400">{{ $bon->user?->email ?? '' }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center space-x-1 text-sm text-gray-500">
                                    <span class="font-black text-gray-900">{{ $bon->orders_driver->count() }}</span>
                                    <span>orders</span>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-sm font-black text-gray-900">
                                {{ number_format($bon->orders_driver->sum('price') ?? 0, 2) }} DH
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest outline outline-2 outline-offset-1 {{ $status_color }}">
                                    {{ $bon->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 flex items-center gap-2 mt-6">
                                @php
                                    $status = boolval($bon->is_completed); // "completed" or "pending"/"cancelled"
                                @endphp

                                @if($status === true)
                                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-[10px] font-extrabold">
                                        Completed
                                    </span>
                                @else
                                    <i data-lucide="x-circle" class="w-4 h-4 text-red-600"></i>
                                    <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-[10px] font-extrabold">
                                        Not Completed
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="showBon({{ $bon->id }})" title="View" class="p-2 text-gray-400 hover:text-primary transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button wire:click="editBon({{ $bon->id }})" title="Edit" class="p-2 text-gray-400 hover:text-blue-500 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    <button wire:click="deleteBon({{ $bon->id }})" wire:confirm="Are you sure?" title="Delete" class="p-2 text-gray-400 hover:text-red-500 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        

                        
                        
                        

                    @empty
                        <tr>
                            <td colspan="7" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-300">
                                        <i data-lucide="package" class="w-10 h-10"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ __('No bons found') }}</h3>
                                    <p class="text-gray-400 mt-1">{{ __('Try adjusting your search.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bons->hasPages())
            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50">
                {{ $bons->links() }}
            </div>
        @endif
    </div>
    @if($showBonDetails && $bon)
    <div class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeBonDetails"></div>

        <div class="flex items-center justify-center min-h-screen p-4 pointer-events-none">
            <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-4xl w-full overflow-hidden pointer-events-auto">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-10 pt-8 pb-6 border-b border-gray-100">
                    <div>
                        <div class="inline-flex items-center space-x-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-black uppercase tracking-widest mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse inline-block"></span>
                            <span>{{ __('Bon') }} #{{ $bon->code }}</span>
                        </div>
                        <p class="text-gray-400 text-xs">{{ __('Created on') }} {{ $bon->created_at->format('d M Y, H:i') }}</p>
                    </div>

                </div>

                {{-- ============================================================
                     CLIENT BON VIEW
                     ============================================================ --}}
                @if($showBonDriver)
                <div class="p-10 space-y-8">
                    
                    {{-- Top: QR + Key Stats --}}
                    <div class="flex flex-col sm:flex-row items-center gap-8">
                        <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100 flex flex-col items-center flex-shrink-0">
                            <img src="{{ $this->generateQrCode() }}" alt="QR Code" class="w-28 h-28">
                            <p class="mt-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Scan to Track') }}</p>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('Client') }}</label>
                                <p class="font-bold text-gray-900 truncate">{{ $bon->user?->name ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('Total Amount') }}</label>
                                <p class="font-black text-gray-900 text-lg">{{ number_format($bon->price ?? 0, 2) }} DH</p>
                            </div>
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">{{ __('Status') }}</label>
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest
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
                    </div>

                    {{-- Orders list for client (shows code, destination, price, status) --}}
                    <div>
                        <h3 class="text-base font-black text-gray-900 mb-4 flex items-center space-x-2">
                            <i data-lucide="package" class="w-4 h-4 text-primary"></i>
                            <span>{{ __('Orders') }} ({{ $bon->orders_driver->count() }})</span>
                        </h3>
                        <div class="bg-gray-50 rounded-2xl overflow-hidden border border-gray-100">
                            @if($bon->orders_driver->isNotEmpty())
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-100/50">
                                        <tr>
                                            <th class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">{{ __('Code') }}</th>
                                            <th class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">{{ __('Destination') }}</th>
                                            <th class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">{{ __('Price') }}</th>
                                            <th class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">{{ __('Payment') }}</th>
                                            <th class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($bon->orders_driver as $order)
                                            <tr class="hover:bg-white transition-colors">
                                                <td class="px-5 py-3 font-black text-gray-900">#{{ $order->code }}</td>
                                                <td class="px-5 py-3 text-gray-500 max-w-[160px] truncate">{{ $order->location ?? '-' }}</td>
                                                <td class="px-5 py-3 font-bold text-gray-900">{{ number_format($order->price ?? 0, 2) }} DH</td>
                                                <td class="px-5 py-3">
                                                    <span class="text-xs font-bold {{ ($order->payment_status ?? '') === 'paid' ? 'text-green-600' : 'text-red-500' }}">
                                                        {{ ucfirst($order->payment_status ?? 'pending') }}
                                                    </span>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <span class="px-2 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest
                                                        {{ $order->color() }}">
                                                        {{ $order->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-400 py-8 text-center text-sm">{{ __('No orders associated with this bon.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
    @endif

    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-8 right-8 p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl flex items-center z-50 animate-in slide-in-from-bottom-8">
            <i data-lucide="check-circle" class="w-6 h-6 me-3"></i>
            <span class="font-bold">{{ session('status') }}</span>
        </div>
    @endif
</div>