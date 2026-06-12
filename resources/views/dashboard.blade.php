<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Panel de administración
                </h2>
                <p class="text-sm text-gray-500 mt-1">Hola, {{ Auth::user()->name }}</p>
            </div>
            <span class="text-sm text-gray-400">{{ now()->format('d/m/Y') }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Stats --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.5.375a2.25 2.25 0 01-2.163-1.632L12.75 15v-3.75m0 0l-10.5-3m10.5 3l-10.5 3" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Mis canciones</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $songsCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Planes de dirección</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $plansCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-500">Próximo servicio</p>
                            @if ($upcomingPlan)
                                <p class="text-lg font-semibold text-gray-900 truncate">{{ $upcomingPlan->title }}</p>
                                <p class="text-xs text-gray-500">{{ $upcomingPlan->date->format('d/m/Y') }}</p>
                            @else
                                <p class="text-sm text-gray-400">Sin planes próximos</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <a href="{{ route('songs.create') }}" class="group flex items-center gap-4 rounded-xl bg-indigo-600 px-6 py-5 text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                    <div class="rounded-lg bg-white/15 p-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold">Nueva canción</p>
                        <p class="text-sm text-indigo-200">Registrar cifrado y letra</p>
                    </div>
                </a>

                <a href="{{ route('service-plans.create') }}" class="group flex items-center gap-4 rounded-xl bg-violet-600 px-6 py-5 text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-500">
                    <div class="rounded-lg bg-white/15 p-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold">Nuevo plan</p>
                        <p class="text-sm text-violet-200">Armar setlist de servicio</p>
                    </div>
                </a>
            </div>

            {{-- Listas recientes --}}
            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Canciones --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">Canciones recientes</h3>
                        <a href="{{ route('songs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todas</a>
                    </div>
                    @if ($recentSongs->isEmpty())
                        <div class="p-8 text-center text-sm text-gray-500">
                            Aún no registraste canciones.
                            <a href="{{ route('songs.create') }}" class="block mt-2 text-indigo-600 hover:underline">Crear la primera</a>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($recentSongs as $song)
                                <li class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
                                    <div class="min-w-0">
                                        <a href="{{ route('songs.show', $song) }}" class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                            {{ $song->title }}
                                        </a>
                                        <p class="text-sm text-gray-500">{{ $song->artist }} · <span class="font-mono">{{ $song->key }}</span></p>
                                    </div>
                                    <a href="{{ route('songs.edit', $song) }}" class="shrink-0 text-xs text-gray-400 hover:text-indigo-600">Editar</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Planes --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">Planes de dirección</h3>
                        <a href="{{ route('service-plans.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todos</a>
                    </div>
                    @if ($recentPlans->isEmpty())
                        <div class="p-8 text-center text-sm text-gray-500">
                            Aún no creaste planes.
                            <a href="{{ route('service-plans.create') }}" class="block mt-2 text-indigo-600 hover:underline">Crear el primero</a>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($recentPlans as $plan)
                                <li class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
                                    <div class="min-w-0">
                                        <a href="{{ route('service-plans.show', $plan) }}" class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                            {{ $plan->title }}
                                        </a>
                                        <p class="text-sm text-gray-500">
                                            {{ $plan->date->format('d/m/Y') }} · {{ $plan->songs_count }} canción(es)
                                        </p>
                                    </div>
                                    @if ($plan->date->isToday())
                                        <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Hoy</span>
                                    @elseif ($plan->date->isFuture())
                                        <span class="shrink-0 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Próximo</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
