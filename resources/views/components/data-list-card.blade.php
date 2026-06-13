@props([
    'search' => null,
])

<div
    data-list-card
    @if ($search) data-search-text="{{ $search }}" @endif
    {{ $attributes->merge(['class' => 'rounded-xl border border-white/10 bg-[#0a0f1a] p-4 transition hover:border-white/15']) }}
>
    {{ $slot }}
</div>
