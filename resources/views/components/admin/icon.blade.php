@props(['name'])
@php
    $path = match($name) {
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'home' => 'm3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z M9 21v-8h6v8',
        'activity' => 'M3 12h4l3-8 4 16 3-8h4',
        'grid' => 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
        'logout' => 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9',
        default => 'M12 3v18 M3 12h18',
    };
@endphp
<svg {{ $attributes->class(['admin-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $path }}" /></svg>
