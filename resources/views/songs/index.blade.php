<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Canciones</h2>
            <a href="{{ route('songs.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Nueva canción</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($songs->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500">
                        No hay canciones registradas.
                        <a href="{{ route('songs.create') }}" class="block mt-2 text-indigo-600 hover:underline">Registrar una</a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($songs as $song)
                            <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                                <div class="min-w-0">
                                    <a href="{{ route('songs.show', $song) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $song->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">{{ $song->artist }} · Tono: <span class="font-mono">{{ $song->key }}</span></p>
                                </div>
                                <div class="flex shrink-0 gap-3 text-sm">
                                    <a href="{{ route('songs.show', $song) }}" class="text-indigo-600 hover:text-indigo-800">Ver</a>
                                    <a href="{{ route('songs.edit', $song) }}" class="text-gray-500 hover:text-gray-700">Editar</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $songs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
