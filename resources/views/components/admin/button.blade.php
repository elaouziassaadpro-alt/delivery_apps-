@props(['variant' => 'primary'])

@php
    $classes = 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 ';
    
    $variants = [
        'primary' => 'text-white bg-primary hover:bg-primary/90 focus:ring-primary',
        'secondary' => 'text-white bg-secondary hover:bg-secondary/90 focus:ring-secondary',
        'outline' => 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary',
        'ghost' => 'text-gray-600 bg-transparent hover:bg-gray-100 focus:ring-gray-400 border-none shadow-none',
    ];

    $classes .= $variants[$variant] ?? $variants['primary'];
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
