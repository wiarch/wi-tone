<x-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Canciones</h1>
            <p class="mt-1 text-sm text-slate-500">Tu repertorio de alabanza</p>
        </div>
        <a href="{{ route('songs.create') }}" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-500">+ Nueva canción</a>
    </div>

    <form method="GET" action="{{ route('songs.index') }}" class="mb-4 flex flex-wrap gap-2">
        <select name="category" class="admin-input text-sm" onchange="this.form.submit()">
            <option value="">Todas las categorías</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </form>

    <x-admin-card>
        @if ($songs->isEmpty())
            <div class="p-12 text-center text-sm text-slate-500">
                No hay canciones registradas.
                <a href="{{ route('songs.create') }}" class="mt-2 block text-violet-400 hover:underline">Registrar una</a>
            </div>
        @else
            <x-responsive-data-list search-placeholder="Buscar canción…">
                <x-slot:cards>
                    @foreach ($songs as $song)
                        <x-data-list-card :search="$song->title . ' ' . $song->artist . ' ' . $song->key . ' ' . ($song->category?->name ?? '')">
                            <a href="{{ route('songs.show', $song) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $song->title }}</a>
                            <p class="mt-1 text-sm text-slate-500">{{ $song->artist }}</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-md bg-amber-500/10 px-2 py-0.5 font-mono text-amber-300">{{ $song->key }}</span>
                                @if ($song->category)
                                    <span class="rounded-md bg-white/5 px-2 py-0.5 text-amber-400/90">{{ $song->category->name }}</span>
                                @endif
                            </div>
                            <div class="mt-3 flex gap-3 text-sm">
                                <a href="{{ route('songs.show', $song) }}" class="text-violet-400 hover:text-violet-300">Ver</a>
                                <a href="{{ route('songs.edit', $song) }}" class="text-slate-500 hover:text-slate-300">Editar</a>
                            </div>
                        </x-data-list-card>
                    @endforeach
                </x-slot:cards>

                <x-slot:table>
                    <table data-datatable class="w-full">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Artista</th>
                                <th>Tono</th>
                                <th>Categoría</th>
                                <th class="!text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($songs as $song)
                                <tr>
                                    <td>
                                        <a href="{{ route('songs.show', $song) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $song->title }}</a>
                                    </td>
                                    <td class="text-slate-400">{{ $song->artist }}</td>
                                    <td><span class="font-mono text-amber-300">{{ $song->key }}</span></td>
                                    <td class="text-slate-400">{{ $song->category?->name ?? '—' }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('songs.show', $song) }}" class="text-violet-400 hover:text-violet-300">Ver</a>
                                        <span class="mx-2 text-slate-700">·</span>
                                        <a href="{{ route('songs.edit', $song) }}" class="text-slate-500 hover:text-slate-300">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>

                <x-slot:footer>
                    <div class="px-5 py-4">{{ $songs->links() }}</div>
                </x-slot:footer>
            </x-responsive-data-list>
        @endif
    </x-admin-card>
</x-app-layout>
