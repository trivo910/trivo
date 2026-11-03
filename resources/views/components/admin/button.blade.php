@props(['variant' => 'primary', 'type' => 'button', 'href' => null])
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['admin-button', 'button-'.$variant]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['admin-button', 'button-'.$variant]) }}>{{ $slot }}</button>
@endif
