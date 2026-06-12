<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo plan de dirección
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('service-plans.store') }}" class="p-6 sm:p-8 space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="title" value="Título del servicio" />
                        <x-text-input
                            id="title"
                            name="title"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('title')"
                            placeholder="Ej: Domingo Central"
                            required
                            autofocus
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="date" value="Fecha del servicio" />
                        <x-text-input
                            id="date"
                            name="date"
                            type="date"
                            class="mt-1 block w-full"
                            :value="old('date')"
                            required
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('date')" />
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        <x-primary-button>Crear plan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
