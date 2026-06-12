<x-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Planes de dirección</h1>
            <p class="mt-1 text-sm text-slate-500">Setlists y servicios programados</p>
        </div>
        <a href="{{ route('service-plans.create') }}" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-500">+ Nuevo plan</a>
    </div>

    <x-admin-card>
        @if ($servicePlans->isEmpty())
            <div class="p-12 text-center text-sm text-slate-500">
                No hay planes creados.
                <a href="{{ route('service-plans.create') }}" class="mt-2 block text-violet-400 hover:underline">Crear uno</a>
            </div>
        @else
            <ul class="divide-y divide-white/5">
                @foreach ($servicePlans as $plan)
                    <li class="flex items-center justify-between px-5 py-4 hover:bg-white/[0.02]">
                        <div class="min-w-0">
                            <a href="{{ route('service-plans.show', $plan) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $plan->title }}</a>
                            <p class="text-sm text-slate-500">{{ $plan->date->format('d/m/Y') }} · {{ $plan->songs_count }} canción(es)</p>
                        </div>
                        <a href="{{ route('service-plans.show', $plan) }}" class="shrink-0 text-sm text-violet-400 hover:text-violet-300">Abrir →</a>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-white/5 px-5 py-4">{{ $servicePlans->links() }}</div>
        @endif
    </x-admin-card>
</x-app-layout>
