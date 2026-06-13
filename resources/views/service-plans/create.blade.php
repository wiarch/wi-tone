@push('head')
    @vite(['resources/js/service-plan-builder.js'])
@endpush

<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Nuevo plan de dirección</h1>
        <p class="mt-1 text-sm text-slate-500">Arma el orden del servicio con subtítulos, canciones y asignaciones</p>
    </div>

    <form method="POST" action="{{ route('service-plans.store') }}" class="max-w-5xl space-y-6 admin-form">
        @csrf

        <x-admin-card>
            <div class="space-y-6 p-6 sm:p-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="title" value="Título del servicio" class="text-slate-300" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full admin-input" :value="old('title')" placeholder="Ej: Domingo Central" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>
                    <div>
                        <x-input-label for="date" value="Fecha" class="text-slate-300" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full admin-input" :value="old('date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="notes" value="Notas (opcional)" class="text-slate-300" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full admin-input" placeholder="Indicaciones generales">{{ old('notes') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>
            </div>
        </x-admin-card>

        <x-admin-card title="Setlist del servicio">
            <div class="p-6 sm:p-8">
                @if ($contacts->isEmpty())
                    <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-sm text-amber-200">
                        Sin personal registrado.
                        <a href="{{ route('contacts.create') }}" class="text-violet-300 hover:underline">Agrega vocalistas o director</a>
                        para asignarlos por canción.
                    </div>
                @endif

                @include('service-plans._setlist-builder', [
                    'mode' => 'create',
                    'contacts' => $contacts,
                    'musicalKeys' => $musicalKeys,
                    'initialEntries' => old('entries') ? json_decode(old('entries'), true) ?? [] : [],
                    'directorContactId' => old('director_contact_id'),
                ])

                <x-input-error class="mt-4" :messages="$errors->get('director_contact_id')" />
            </div>
        </x-admin-card>

        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('service-plans.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</a>
            <x-primary-button>Crear plan</x-primary-button>
        </div>
    </form>
</x-app-layout>
