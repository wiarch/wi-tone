<x-app-layout>
    <div class="space-y-8">
        {{-- Page header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500 capitalize">{{ now()->locale('es')->isoFormat('MMMM [de] YYYY') }}</p>
                <p class="mt-2 text-sm text-slate-400">Vista general de tu repertorio y planes de dirección.</p>
            </div>
            <div class="flex rounded-xl border border-white/10 bg-[#141c2e] p-1 text-sm">
                <span class="rounded-lg bg-violet-600 px-4 py-2 font-medium text-white">Repertorio</span>
                <span class="px-4 py-2 text-slate-500">Planes</span>
            </div>
        </div>

        {{-- Stats row 1 --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-white/5 bg-[#141c2e] p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Mis canciones</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $songsCount }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $repertoireCount }} en el repertorio global</p>
            </div>
            <div class="rounded-2xl border border-white/5 bg-[#141c2e] p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Planes de dirección</p>
                <p class="mt-2 text-3xl font-bold text-emerald-400">{{ $plansCount }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $upcomingPlansCount }} próximos</p>
            </div>
            <div class="rounded-2xl border border-white/5 bg-[#141c2e] p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Canciones en setlists</p>
                <p class="mt-2 text-3xl font-bold text-amber-400">{{ $setlistSongsCount }}</p>
                <p class="mt-2 text-xs text-slate-500">En todos tus planes</p>
            </div>
            <div class="rounded-2xl border border-white/5 bg-[#141c2e] p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Próximo servicio</p>
                @if ($upcomingPlan)
                    <p class="mt-2 text-xl font-bold text-white truncate">{{ $upcomingPlan->title }}</p>
                    <p class="mt-2 text-xs text-violet-400">{{ $upcomingPlan->date->format('d/m/Y') }}</p>
                @else
                    <p class="mt-2 text-xl font-bold text-slate-600">—</p>
                    <p class="mt-2 text-xs text-slate-500">Sin planes próximos</p>
                @endif
            </div>
        </div>

        {{-- Stats row 2 --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('songs.create') }}" class="group rounded-2xl border border-white/5 bg-[#141c2e] p-5 transition hover:border-violet-500/30 hover:bg-violet-600/5">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Acción rápida</p>
                <p class="mt-2 text-lg font-semibold text-violet-400 group-hover:text-violet-300">+ Nueva canción</p>
            </a>
            <a href="{{ route('service-plans.create') }}" class="group rounded-2xl border border-white/5 bg-[#141c2e] p-5 transition hover:border-emerald-500/30 hover:bg-emerald-600/5">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Acción rápida</p>
                <p class="mt-2 text-lg font-semibold text-emerald-400 group-hover:text-emerald-300">+ Nuevo plan</p>
            </a>
            @if ($upcomingPlan)
                <a href="{{ route('service-plans.show', $upcomingPlan) }}" class="group rounded-2xl border border-white/5 bg-[#141c2e] p-5 transition hover:border-amber-500/30">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Abrir setlist</p>
                    <p class="mt-2 text-lg font-semibold text-amber-400 truncate">{{ $upcomingPlan->title }}</p>
                </a>
            @else
                <div class="rounded-2xl border border-white/5 bg-[#141c2e] p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Setlist activo</p>
                    <p class="mt-2 text-lg font-semibold text-slate-600">Ninguno</p>
                </div>
            @endif
        </div>

        {{-- Recent activity --}}
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Actividad reciente</h2>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-admin-card title="Canciones recientes">
                    <x-slot:action>
                        <a href="{{ route('songs.index') }}" class="text-sm text-violet-400 hover:text-violet-300">Ver todas →</a>
                    </x-slot:action>
                    @if ($recentSongs->isEmpty())
                        <div class="p-8 text-center text-sm text-slate-500">
                            Sin canciones aún.
                            <a href="{{ route('songs.create') }}" class="mt-2 block text-violet-400 hover:underline">Crear la primera</a>
                        </div>
                    @else
                        <ul class="divide-y divide-white/5">
                            @foreach ($recentSongs as $song)
                                <li class="flex items-center justify-between px-5 py-3.5 hover:bg-white/[0.02]">
                                    <div class="min-w-0">
                                        <a href="{{ route('songs.show', $song) }}" class="font-medium text-slate-200 hover:text-violet-300 truncate block">
                                            {{ $song->title }}
                                        </a>
                                        <p class="text-sm text-slate-500">{{ $song->artist }} · <span class="font-mono text-slate-400">{{ $song->key }}</span></p>
                                    </div>
                                    <a href="{{ route('songs.edit', $song) }}" class="shrink-0 text-xs text-slate-500 hover:text-violet-400">Editar</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin-card>

                <x-admin-card title="Planes de dirección">
                    <x-slot:action>
                        <a href="{{ route('service-plans.index') }}" class="text-sm text-violet-400 hover:text-violet-300">Ver todos →</a>
                    </x-slot:action>
                    @if ($recentPlans->isEmpty())
                        <div class="p-8 text-center text-sm text-slate-500">
                            Sin planes aún.
                            <a href="{{ route('service-plans.create') }}" class="mt-2 block text-violet-400 hover:underline">Crear el primero</a>
                        </div>
                    @else
                        <ul class="divide-y divide-white/5">
                            @foreach ($recentPlans as $plan)
                                <li class="flex items-center justify-between px-5 py-3.5 hover:bg-white/[0.02]">
                                    <div class="min-w-0">
                                        <a href="{{ route('service-plans.show', $plan) }}" class="font-medium text-slate-200 hover:text-violet-300 truncate block">
                                            {{ $plan->title }}
                                        </a>
                                        <p class="text-sm text-slate-500">{{ $plan->date->format('d/m/Y') }} · {{ $plan->songs_count }} canciones</p>
                                    </div>
                                    @if ($plan->date->isToday())
                                        <span class="shrink-0 rounded-full bg-amber-500/15 px-2.5 py-0.5 text-xs font-medium text-amber-400">Hoy</span>
                                    @elseif ($plan->date->isFuture())
                                        <span class="shrink-0 rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-medium text-emerald-400">Próximo</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin-card>
            </div>
        </div>
    </div>
</x-app-layout>
