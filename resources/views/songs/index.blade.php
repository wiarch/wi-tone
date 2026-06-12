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
            <ul class="divide-y divide-white/5">
                @foreach ($songs as $song)
                    <li class="flex items-center justify-between px-5 py-4 hover:bg-white/[0.02]">
                        <div class="min-w-0">
                            <a href="{{ route('songs.show', $song) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $song->title }}</a>
                            <p class="text-sm text-slate-500">
                                {{ $song->artist }} · Tono: <span class="font-mono text-slate-400">{{ $song->key }}</span>
                                @if ($song->category)
                                    · <span class="text-amber-400/90">{{ $song->category->name }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-4 text-sm">
                            <a href="{{ route('songs.show', $song) }}" class="text-violet-400 hover:text-violet-300">Ver</a>
                            <a href="{{ route('songs.edit', $song) }}" class="text-slate-500 hover:text-slate-300">Editar</a>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-white/5 px-5 py-4">{{ $songs->links() }}</div>
        @endif
    </x-admin-card>
</x-app-layout>
