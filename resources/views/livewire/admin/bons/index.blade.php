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

    public function updatingSearch() { $this->resetPage(); }

    public function showBon($id)
    {
        return redirect()->route('admin.bons.show', $id);
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
                ->with(['client.user', 'orders'])
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('code', 'like', '%' . $this->search . '%')
                          ->orWhereHas('client.user', function($q2) {
                              $q2->where('name', 'like', '%' . $this->search . '%');
                          });
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
                                <p class="text-sm font-bold text-gray-900">{{ $bon->client->user->name ?? 'N/A' }}</p>
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
                                    <button wire:click="showBon({{ $bon->id }})" class="p-2.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-all" title="{{ __('View') }}">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                    <button wire:click="editBon({{ $bon->id }})" class="p-2.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-xl transition-all" title="{{ __('Edit') }}">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </button>
                                    <button wire:click="deleteBon({{ $bon->id }})" wire:confirm="{{ __('Are you sure you want to delete this bon?') }}" class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="{{ __('Delete') }}">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
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

    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-8 right-8 p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl flex items-center z-50 animate-in slide-in-from-bottom-8">
            <i data-lucide="check-circle" class="w-6 h-6 me-3"></i>
            <span class="font-bold">{{ session('status') }}</span>
        </div>
    @endif
</div>