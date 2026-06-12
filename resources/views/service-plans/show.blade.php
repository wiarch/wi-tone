@push('head')
    @vite(['resources/js/service-plan-show.js'])
@endpush

<x-app-layout>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $servicePlan->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $servicePlan->date->format('d/m/Y') }}</p>
            @if ($servicePlan->notes)
                <p class="mt-2 text-sm text-slate-400">{{ $servicePlan->notes }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('service-plans.share', $servicePlan) }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/5">Compartir</a>
            @if ($servicePlan->isPublished())
                <a href="{{ $servicePlan->publicUrl() }}" target="_blank" class="rounded-xl border border-amber-500/30 px-4 py-2 text-sm text-amber-300 hover:bg-amber-500/10">Enlace público</a>
            @endif
            <a href="{{ route('service-plans.export', $servicePlan) }}" target="_blank" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-500">Exportar PDF</a>
            <a href="{{ route('service-plans.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-300 hover:bg-white/5">← Planes</a>
        </div>
    </div>

    <div class="max-w-5xl space-y-6">
        @foreach (['plan-created' => 'Plan creado.', 'song-attached' => 'Canción añadida.', 'song-detached' => 'Canción quitada.', 'entry-updated' => 'Entrada actualizada.', 'member-added' => 'Integrante añadido.'] as $key => $message)
            @if (session('status') === $key)
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ $message }}</div>
            @endif
        @endforeach

        <x-admin-card title="Equipo vocal">
            @if ($servicePlan->teamMembers->isEmpty())
                <div class="p-6 text-sm text-slate-500">Sin integrantes. Añade vocalistas para asignarlos a cada momento.</div>
            @else
                <ul class="divide-y divide-white/5">
                    @foreach ($servicePlan->teamMembers as $member)
                        <li class="flex items-center justify-between px-5 py-3">
                            <span class="font-medium text-slate-200">{{ $member->name }}</span>
                            <span class="rounded-full bg-violet-600/20 px-2.5 py-0.5 text-xs font-medium text-violet-300">{{ $member->voice_tone }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="border-t border-white/5 p-5">
                <form method="POST" action="{{ route('service-plans.members.store', $servicePlan) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs text-slate-500">Nombre</label>
                        <input type="text" name="name" required class="mt-1 block w-full admin-input text-sm" placeholder="Nuevo integrante" />
                    </div>
                    <div class="sm:w-44">
                        <label class="block text-xs text-slate-500">Tono de voz</label>
                        <select name="voice_tone" required class="mt-1 block w-full admin-input text-sm">
                            @foreach ($voiceTones as $tone)
                                <option value="{{ $tone }}">{{ $tone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/15">Añadir</button>
                </form>
            </div>
        </x-admin-card>

        <x-admin-card title="Setlist del servicio">
            <x-slot:action>
                <span class="text-sm text-slate-500">{{ $servicePlan->songs->count() }} canción(es) · arrastra para reordenar</span>
            </x-slot:action>

            @if ($servicePlan->songs->isEmpty())
                <div class="p-10 text-center text-sm text-slate-500">Aún no hay canciones. Añade momentos abajo.</div>
            @else
                <ol
                    data-plan-setlist
                    data-reorder-url="{{ route('service-plans.reorder', $servicePlan) }}"
                    data-csrf="{{ csrf_token() }}"
                    class="divide-y divide-white/5"
                >
                    @foreach ($servicePlan->songs as $song)
                        <li data-song-id="{{ $song->id }}" class="group px-5 py-4 hover:bg-white/[0.02]">
                            <div class="flex items-start gap-3">
                                <button type="button" data-drag-handle class="mt-1 cursor-grab text-slate-600 hover:text-slate-400 active:cursor-grabbing" aria-label="Reordenar">⠿</button>
                                <span data-order-badge class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-600/20 text-sm font-semibold text-violet-300">{{ $song->pivot->order }}</span>
                                <div class="min-w-0 flex-1 space-y-3">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div>
                                            <a href="{{ route('songs.show', $song) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $song->title }}</a>
                                            <p class="text-sm text-slate-500">
                                                {{ $song->artist }}
                                                @php $entryCategory = $categoryMap[$song->pivot->category_id] ?? $song->category; @endphp
                                                @if ($entryCategory)
                                                    · <span class="text-amber-400/90">{{ $entryCategory->name }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('service-plans.songs.detach', [$servicePlan, $song]) }}" onsubmit="return confirm('¿Quitar esta canción del plan?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-slate-500 hover:text-red-300">Quitar</button>
                                        </form>
                                    </div>

                                    <form method="POST" action="{{ route('service-plans.songs.update', [$servicePlan, $song]) }}" class="grid gap-3 sm:grid-cols-3">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="block text-xs text-slate-500">Categoría</label>
                                            <select name="category_id" class="mt-1 block w-full admin-input text-sm">
                                                <option value="">—</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected((int) $song->pivot->category_id === $category->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-500">Tono en servicio</label>
                                            <select name="performance_key" class="mt-1 block w-full admin-input text-sm font-mono">
                                                @foreach ($musicalKeys as $key)
                                                    <option value="{{ $key }}" @selected(($song->pivot->performance_key ?? $song->key) === $key)>{{ $key }}</option>
                                                @endforeach
                                                @if ($song->key && ! in_array($song->key, $musicalKeys, true))
                                                    <option value="{{ $song->key }}" @selected(($song->pivot->performance_key ?? $song->key) === $song->key)>{{ $song->key }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-500">Asignar a</label>
                                            <select name="team_member_id" class="mt-1 block w-full admin-input text-sm">
                                                <option value="">Sin asignar</option>
                                                @foreach ($servicePlan->teamMembers as $member)
                                                    <option value="{{ $member->id }}" @selected((int) $song->pivot->team_member_id === $member->id)>{{ $member->name }} ({{ $member->voice_tone }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3 flex justify-end">
                                            <button type="submit" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-slate-300 hover:bg-white/5">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-admin-card>

        <x-admin-card title="Añadir canción al plan">
            <div class="p-6">
                @if ($availableSongs->isEmpty() && $search === '')
                    <p class="text-sm text-slate-500">
                        @if ($servicePlan->songs->isEmpty())
                            No hay canciones en la base de datos.
                            <a href="{{ route('songs.create') }}" class="text-violet-400 hover:underline">Registrar una</a>
                        @else
                            Todas las canciones ya están en este plan.
                        @endif
                    </p>
                @else
                    <form method="GET" action="{{ route('service-plans.show', $servicePlan) }}" class="mb-4 flex flex-wrap gap-2">
                        <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por título, artista o tono..." class="block min-w-[12rem] flex-1 admin-input" />
                        <select name="category" class="admin-input text-sm">
                            <option value="">Todas las categorías</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($categoryFilter === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="shrink-0 rounded-xl bg-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/15">Buscar</button>
                        @if ($search !== '' || $categoryFilter > 0)
                            <a href="{{ route('service-plans.show', $servicePlan) }}" class="shrink-0 rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-400 hover:text-slate-200">Limpiar</a>
                        @endif
                    </form>

                    @if ($availableSongs->isEmpty())
                        <p class="text-sm text-slate-500">Sin resultados para «{{ $search }}».</p>
                    @else
                        <form method="POST" action="{{ route('service-plans.songs.attach', $servicePlan) }}" class="space-y-4">
                            @csrf
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="song_id" class="block text-sm font-medium text-slate-300">Canción</label>
                                    <select id="song_id" name="song_id" required class="mt-1 block w-full admin-input" size="6">
                                        <option value="" disabled selected>Seleccionar canción…</option>
                                        @foreach ($availableSongs as $song)
                                            <option value="{{ $song->id }}" data-key="{{ $song->key }}" data-category="{{ $song->category_id }}" @selected((string) old('song_id') === (string) $song->id)>
                                                {{ $song->title }} — {{ $song->artist }} ({{ $song->key }})@if ($song->category) · {{ $song->category->name }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('song_id')" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Categoría</label>
                                    <select id="attach-category-id" name="category_id" class="mt-1 block w-full admin-input">
                                        <option value="">De la canción</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Tono en servicio</label>
                                    <select id="attach-performance-key" name="performance_key" class="mt-1 block w-full admin-input font-mono">
                                        <option value="">Tono de la canción</option>
                                        @foreach ($musicalKeys as $key)
                                            <option value="{{ $key }}">{{ $key }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-300">Asignar a</label>
                                    <select name="team_member_id" class="mt-1 block w-full admin-input">
                                        <option value="">Sin asignar</option>
                                        @foreach ($servicePlan->teamMembers as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->voice_tone }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <x-primary-button>Añadir al plan</x-primary-button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>
        </x-admin-card>
    </div>

    @push('scripts')
        <script>
            document.getElementById('song_id')?.addEventListener('change', (e) => {
                const option = e.target.selectedOptions[0];
                const keySelect = document.getElementById('attach-performance-key');
                const categorySelect = document.getElementById('attach-category-id');
                if (!option) return;
                if (keySelect && option.dataset.key) {
                    keySelect.value = option.dataset.key;
                }
                if (categorySelect && option.dataset.category) {
                    categorySelect.value = option.dataset.category;
                }
            });
        </script>
    @endpush
</x-app-layout>
