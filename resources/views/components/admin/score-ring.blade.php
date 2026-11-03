@props(['score' => null])
@php $value = $score === null ? null : max(0, min(100, (int) $score)); @endphp
<div class="score-ring {{ $value === null ? 'score-empty' : ($value < 50 ? 'score-low' : ($value < 80 ? 'score-medium' : 'score-high')) }}" role="img" aria-label="{{ $value === null ? 'No published post score available' : 'SEO score '.$value.' out of 100' }}">
    <svg viewBox="0 0 100 100" aria-hidden="true"><circle class="score-track" cx="50" cy="50" r="42"/><circle class="score-progress" cx="50" cy="50" r="42" pathLength="100" stroke-dasharray="{{ $value ?? 0 }} 100"/></svg>
    <span>{{ $value ?? '—' }}</span>
</div>
