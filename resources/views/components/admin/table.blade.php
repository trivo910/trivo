@props(['caption'])
<div {{ $attributes->class(['glass glass-strong glass-table table-scroll']) }} tabindex="0" role="region" aria-label="{{ $caption }}">
    <table class="admin-table"><caption class="sr-only">{{ $caption }}</caption><thead>{{ $head }}</thead><tbody>{{ $slot }}</tbody></table>
</div>
