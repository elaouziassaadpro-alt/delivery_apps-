@props(['user' => null])

@php
    $userName = $user->name ?? 'Guest';
    $initials = collect(explode(' ', trim($userName)))
        ->filter()
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden']) }}>

    @if (($user?->photo ?? null) instanceof \Livewire\TemporaryUploadedFile)
        <img 
            src="{{ $user->photo->temporaryUrl() }}" 
            class="w-full h-full object-cover" 
            alt="Preview"
        >
    @elseif (!empty($user?->photo) && Storage::disk('private')->exists($user->photo))
        <img 
            src="{{  route('profile.photo', ['filename' => basename($user->photo)]) }}" 
            class="w-full h-full object-cover" 
            alt="Profile"
        >
    @else
        <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold text-xl uppercase">
            {{ $initials ?: 'NA' }}
        </div>
    @endif

</div>
