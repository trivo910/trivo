@props(['label', 'value' => null, 'note' => null])
<x-admin.glass-card strong>
    <p class="eyebrow">{{ $label }}</p><p class="stat-value">{{ $value ?? '—' }}</p>
    @if($note)<p class="helper">{{ $note }}</p>@endif
</x-admin.glass-card>
