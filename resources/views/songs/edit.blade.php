<x-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Editar canción</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $song->title }}</p>
        </div>
        <a href="{{ route('songs.show', $song) }}" class="text-sm text-violet-400 hover:text-violet-300">Ver cifrado →</a>
    </div>

    <form method="POST" action="{{ route('songs.update', $song) }}" class="admin-form">
        @csrf
        @method('PUT')

        @include('songs._chordpro-form', ['song' => $song])

        <div class="mt-6 flex items-center justify-end gap-4 rounded-2xl border border-white/5 bg-[#141c2e] px-6 py-4">
            <a href="{{ route('songs.show', $song) }}" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</a>
            <x-primary-button>Actualizar canción</x-primary-button>
        </div>
    </form>
</x-app-layout>
