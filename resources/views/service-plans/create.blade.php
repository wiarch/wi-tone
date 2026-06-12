<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Nuevo plan de dirección</h1>
        <p class="mt-1 text-sm text-slate-500">Define título y fecha del servicio</p>
    </div>

    <div class="max-w-xl">
        <x-admin-card>
            <form method="POST" action="{{ route('service-plans.store') }}" class="p-6 sm:p-8 space-y-6 admin-form">
                @csrf
                <div>
                    <x-input-label for="title" value="Título del servicio" class="text-slate-300" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full admin-input" :value="old('title')" placeholder="Ej: Domingo Central" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>
                <div>
                    <x-input-label for="date" value="Fecha del servicio" class="text-slate-300" />
                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full admin-input" :value="old('date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date')" />
                </div>
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-white/5">
                    <a href="{{ route('service-plans.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</a>
                    <x-primary-button>Crear plan</x-primary-button>
                </div>
            </form>
        </x-admin-card>
    </div>
</x-app-layout>
