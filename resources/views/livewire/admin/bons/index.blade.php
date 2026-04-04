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


    public function updatingSearch() { $this->resetPage(); }

    public function showBon($id)
    {
        $bon = Bon::with(['user', 'orders'])->findOrFail($id);
        $this->bon = $bon;
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
    }

    public function editBon($id)
    {
        return redirect()->route('admin.bons.edit', $id);
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
                ->with(['user', 'orders'])
                ->where(function($query) {
                    $searchTerm = '%' . $this->search . '%';
                    $query->where('code', 'like', $searchTerm)
                          ->orWhereHas('user', function($q2) use ($searchTerm) {
                              $q2->where('name', 'like', $searchTerm);
                          });
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
        <a href="{{ route('admin.bons.create') }}" class="px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center">
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
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Code') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Date') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Client') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Orders') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Amount') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Status') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bons as $bon)
                        <tr class="hover:bg-gray-50/30 transition-colors group" wire:key="{{ $bon->id }}">
                            <td class="px-8 py-5 font-black text-gray-900">#{{ $bon->code }}</td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-bold text-gray-600">{{ $bon->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900">{{ $bon->user->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-8 py-5 text-sm text-gray-500">
                                {{ $bon->orders->count() }} orders
                            </td>
                            <td class="px-8 py-5 text-sm font-black text-gray-900">
                                {{ number_format($bon->price ?? 0, 2) }} DH
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $status_color = match($bon->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800 outline-yellow-200',
                                        'processing' => 'bg-blue-100 text-blue-800 outline-blue-200',
                                        'completed' => 'bg-green-100 text-green-800 outline-green-200',
                                        'cancelled' => 'bg-red-100 text-red-800 outline-red-200',
                                        default => 'bg-gray-100 text-gray-800 outline-gray-200',
                                    };
                                @endphp
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest outline outline-2 outline-offset-1 {{ $status_color }}">
                                    {{ $bon->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="showBon({{ $bon->id }})" 
                                        class="p-2 text-gray-400 hover:text-primary transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>

                                <button wire:click="deleteBon({{ $bon->id }})" 
                                        wire:confirm="Are you sure?" 
                                        class="p-2 text-gray-400 hover:text-red-500 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>
                                    </svg>
                                </button>

                                <button wire:click="editBon({{ $bon->id }})" 
                                        class="p-2 text-gray-400 hover:text-blue-500 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                    </svg>
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
            <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-4xl w-full p-10 overflow-hidden pointer-events-auto">
                <button wire:click="closeBonDetails" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600">
                    <i data-lucide="x"></i>
                </button>

                <div class="space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold">{{ __('Bon Details') }} <span class="text-primary">#{{ $bon->code }}</span></h2>
                        <p class="text-gray-400 text-sm mt-1">{{ __('Created on') }} {{ $bon->created_at->format('d M Y, H:i') }}</p>

                    </div>
                    <div class="flex justify-center">
                        <img src="{{ $this->generateQrCode() }}" alt="QR Code" class="w-32 h-32">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('Client') }}</label>
                            <p class="font-bold text-gray-900">{{ $bon->user->name ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">{{ __('Total Amount') }}</label>
                            <p class="font-black text-gray-900 text-lg">{{ number_format($bon->price ?? 0, 2) }} DH</p>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">{{ __('Status') }}</label>
                            <span class="px-3 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-widest bg-gray-100 text-gray-800">
                                {{ $bon->status }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold mb-4">{{ __('Orders') }} ({{ $bon->orders->count() }})</h3>
                        <div class="bg-gray-50 rounded-2xl p-4 overflow-x-auto border border-gray-100">
                            @if($bon->orders->isNotEmpty())
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr>
                                            <th class="pb-3 text-gray-400 font-bold">{{ __('Code') }}</th>
                                            <th class="pb-3 text-gray-400 font-bold">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($bon->orders as $order)
                                            <tr>
                                                <td class="py-3 font-bold text-gray-900">{{ $order->code }}</td>
                                                <td class="py-3">{{ $order->status }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-400 py-4 text-center">{{ __('No orders associated with this bon.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
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