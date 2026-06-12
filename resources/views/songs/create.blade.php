<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Nueva canción</h1>
        <p class="mt-1 text-sm text-slate-500">Editor visual — arrastra acordes sobre la letra</p>
    </div>

    <form method="POST" action="{{ route('songs.store') }}" class="admin-form">
        @csrf

        @include('songs._chordpro-form')

        <div class="mt-6 flex items-center justify-end gap-4 rounded-2xl border border-white/5 bg-[#141c2e] px-6 py-4">
            <a href="{{ route('songs.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</a>
            <x-primary-button>Guardar canción</x-primary-button>
        </div>
    </form>
</x-app-layout>
