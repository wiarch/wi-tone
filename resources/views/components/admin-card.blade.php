@props(['title' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-white/5 bg-[#141c2e] overflow-hidden']) }}>
    @if ($title)
        <div class="flex items-center justify-between border-b border-white/5 px-5 py-4">
            <h3 class="font-semibold text-white">{{ $title }}</h3>
            @if ($action)
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
