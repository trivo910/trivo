@props(['strong' => false])
<section {{ $attributes->class(['glass admin-card', 'glass-strong' => $strong]) }}>{{ $slot }}</section>
