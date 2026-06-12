<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar canción
            </h2>
            <a href="{{ route('songs.show', $song) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver canción</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('songs.update', $song) }}" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    @include('songs._form', ['song' => $song])

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('songs.show', $song) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        <x-primary-button>Actualizar canción</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
