<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Planes de dirección</h2>
            <a href="{{ route('service-plans.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Nuevo plan</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($servicePlans->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500">
                        No hay planes creados.
                        <a href="{{ route('service-plans.create') }}" class="block mt-2 text-indigo-600 hover:underline">Crear uno</a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($servicePlans as $plan)
                            <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                                <div class="min-w-0">
                                    <a href="{{ route('service-plans.show', $plan) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $plan->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">
                                        {{ $plan->date->format('d/m/Y') }} · {{ $plan->songs_count }} canción(es)
                                    </p>
                                </div>
                                <a href="{{ route('service-plans.show', $plan) }}" class="shrink-0 text-sm text-indigo-600 hover:text-indigo-800">
                                    Abrir
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $servicePlans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
