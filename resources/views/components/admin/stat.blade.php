@props(['label', 'value', 'icon', 'trend' => null, 'trendUp' => true])

<x-admin.card class="flex items-center space-x-4">
    <div class="p-3 rounded-xl bg-primary/10 text-primary">
        <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
    </div>
    <div class="flex-1">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        <h3 class="text-2xl font-bold text-text-main">{{ $value }}</h3>
        @if($trend)
            <p class="text-xs mt-1 {{ $trendUp ? 'text-success' : 'text-error' }} flex items-center">
                <i data-lucide="{{ $trendUp ? 'trending-up' : 'trending-down' }}" class="w-3 h-3 mr-1"></i>
                {{ $trend }}
            </p>
        @endif
    </div>
</x-admin.card>
