<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Editar persona</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $contact->name }}</p>
    </div>

    <div class="max-w-3xl">
        <x-admin-card>
            <form method="POST" action="{{ route('contacts.update', $contact) }}" enctype="multipart/form-data" class="space-y-6 p-6 sm:p-8 admin-form">
                @csrf
                @method('PATCH')
                @include('contacts._form', ['contact' => $contact])
                <div class="flex justify-end gap-4 border-t border-white/5 pt-4">
                    <a href="{{ route('contacts.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</a>
                    <x-primary-button>Actualizar</x-primary-button>
                </div>
            </form>
        </x-admin-card>
    </div>
</x-app-layout>
