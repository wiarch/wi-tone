@push('head')
    @vite(['resources/js/service-plan-builder.js'])
@endpush

<x-app-layout>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $servicePlan->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $servicePlan->date->format('d/m/Y') }}</p>
            @if ($servicePlan->notes)
                <p class="mt-2 text-sm text-slate-400">{{ $servicePlan->notes }}</p>
            @endif
            @if ($servicePlan->director)
                <p class="mt-2 text-sm text-violet-300">Director: {{ $servicePlan->director->name }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('service-plans.share', $servicePlan) }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/5">Compartir</a>
            @if ($servicePlan->isPublished())
                <a href="{{ $servicePlan->publicUrl() }}" target="_blank" class="rounded-xl border border-amber-500/30 px-4 py-2 text-sm text-amber-300 hover:bg-amber-500/10">Enlace público</a>
            @endif
            <a href="{{ route('service-plans.export', $servicePlan) }}" target="_blank" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-500">Exportar PDF</a>
            <a href="{{ route('service-plans.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-300 hover:bg-white/5">← Planes</a>
        </div>
    </div>

    <div class="max-w-5xl space-y-6">
        @foreach (['plan-created' => 'Plan creado.', 'setlist-updated' => 'Setlist guardado.'] as $key => $message)
            @if (session('status') === $key)
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ $message }}</div>
            @endif
        @endforeach

        @if ($contacts->isEmpty())
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-200">
                <a href="{{ route('contacts.create') }}" class="text-violet-300 hover:underline">Registra personal</a>
                para asignar director y vocalistas por canción.
            </div>
        @endif

        <x-admin-card title="Setlist del servicio">
            <x-slot:action>
                <a href="{{ route('contacts.index') }}" class="text-xs text-violet-400 hover:text-violet-300">Gestionar personal →</a>
            </x-slot:action>
            <div class="p-6 sm:p-8">
                @include('service-plans._setlist-builder', [
                    'mode' => 'edit',
                    'plan' => $servicePlan,
                    'contacts' => $contacts,
                    'musicalKeys' => $musicalKeys,
                    'initialEntries' => $builderEntries,
                    'directorContactId' => $servicePlan->director_contact_id,
                ])
            </div>
        </x-admin-card>
    </div>
</x-app-layout>
