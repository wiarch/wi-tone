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
            <x-responsive-data-list search-placeholder="Buscar plan…">
                <x-slot:cards>
                    @foreach ($servicePlans as $plan)
                        <x-data-list-card :search="$plan->title . ' ' . $plan->date->format('d/m/Y')">
                            <a href="{{ route('service-plans.show', $plan) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $plan->title }}</a>
                            <p class="mt-1 text-sm text-slate-500">{{ $plan->date->format('d/m/Y') }}</p>
                            <p class="mt-2 text-xs text-slate-600">{{ $plan->songs_count }} canción(es)</p>
                            <a href="{{ route('service-plans.show', $plan) }}" class="mt-3 inline-block text-sm text-violet-400 hover:text-violet-300">Abrir →</a>
                        </x-data-list-card>
                    @endforeach
                </x-slot:cards>

                <x-slot:table>
                    <table data-datatable class="w-full">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Fecha</th>
                                <th>Canciones</th>
                                <th class="!text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($servicePlans as $plan)
                                <tr>
                                    <td>
                                        <a href="{{ route('service-plans.show', $plan) }}" class="font-medium text-slate-200 hover:text-violet-300">{{ $plan->title }}</a>
                                    </td>
                                    <td class="text-slate-400">{{ $plan->date->format('d/m/Y') }}</td>
                                    <td class="text-slate-400">{{ $plan->songs_count }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('service-plans.show', $plan) }}" class="text-violet-400 hover:text-violet-300">Abrir →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>

                <x-slot:footer>
                    <div class="px-5 py-4">{{ $servicePlans->links() }}</div>
                </x-slot:footer>
            </x-responsive-data-list>
        @endif
    </x-admin-card>
</x-app-layout>
