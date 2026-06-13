@props([
    'searchPlaceholder' => 'Buscar…',
])

<div data-responsive-data-list {{ $attributes->merge(['class' => '']) }}>
    <div class="border-b border-white/5 p-4 lg:hidden">
        <input
            type="search"
            data-card-search
            placeholder="{{ $searchPlaceholder }}"
            class="admin-input w-full text-sm"
            autocomplete="off"
        />
    </div>

    <div class="grid gap-3 p-4 sm:grid-cols-2 lg:hidden" data-card-list>
        {{ $cards }}
    </div>

    <div class="hidden lg:block" data-table-wrap>
        {{ $table }}
    </div>

    @isset($footer)
        <div class="border-t border-white/5">{{ $footer }}</div>
    @endisset
</div>
