@props([
    'mode' => 'create',
    'plan' => null,
    'contacts' => collect(),
    'musicalKeys' => [],
    'initialEntries' => [],
    'directorContactId' => null,
])

@php
    $builderConfig = [
        'contacts' => $contacts->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'role' => $c->role,
            'vocal_tone' => $c->vocal_tone,
        ])->values(),
        'musicalKeys' => $musicalKeys,
    ];
@endphp

<div
    data-service-plan-builder
    data-mode="{{ $mode }}"
    data-search-url="{{ route('service-plans.songs.search') }}"
    @if ($plan)
        data-sync-url="{{ route('service-plans.setlist.sync', $plan) }}"
        data-plan-id="{{ $plan->id }}"
    @endif
    data-csrf="{{ csrf_token() }}"
    class="space-y-4"
>
    <script type="application/json" data-builder-config>@json($builderConfig)</script>
    <script type="application/json" data-initial-entries>@json($initialEntries)</script>
    <input type="hidden" name="entries" data-entries-json value="">

    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <label class="mb-2 block text-xs font-medium uppercase tracking-wider text-slate-500">Director del servicio</label>
            <select
                name="{{ $mode === 'create' ? 'director_contact_id' : '' }}"
                data-director-select
                class="block w-full admin-input text-sm"
            >
                <option value="">Sin asignar</option>
                @foreach ($contacts as $contact)
                    <option value="{{ $contact->id }}" @selected((int) ($directorContactId ?? 0) === $contact->id)>
                        {{ $contact->name }}@if ($contact->role) · {{ $contact->role }}@endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            @if ($mode === 'edit')
                <div class="flex w-full items-center justify-end gap-3">
                    <span data-save-status class="text-xs"></span>
                    <button type="button" data-save-setlist class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-500">
                        Guardar setlist
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-white/10 bg-[#0a0f1a] p-4">
        <div class="mb-3 flex flex-wrap items-end gap-2">
            <div class="min-w-[12rem] flex-1">
                <label class="mb-1 block text-xs text-slate-500">Subtítulo / sección</label>
                <input
                    type="text"
                    data-section-title
                    placeholder="Ej: Adoraciones, Coros del medio tiempo…"
                    class="block w-full admin-input text-sm"
                />
            </div>
            <button type="button" data-add-section class="shrink-0 rounded-lg border border-amber-500/40 bg-amber-600/15 px-4 py-2 text-sm font-medium text-amber-200 hover:bg-amber-600/25">
                + Subtítulo
            </button>
        </div>

        <div class="mb-2">
            <label class="mb-1 block text-xs text-slate-500">Buscar canciones del repertorio</label>
            <input
                type="search"
                data-song-search
                placeholder="Título, artista o tono…"
                class="block w-full admin-input text-sm"
                autocomplete="off"
            />
        </div>
        <div data-song-results class="max-h-48 overflow-y-auto rounded-lg border border-white/5"></div>
    </div>

    <div>
        <div class="mb-2 flex items-center justify-between">
            <h4 class="text-xs font-medium uppercase tracking-wider text-slate-500">Orden del servicio</h4>
            <span class="text-xs text-slate-600">Arrastra ⠿ para reordenar</span>
        </div>
        <ol data-setlist class="space-y-3"></ol>
    </div>
</div>
