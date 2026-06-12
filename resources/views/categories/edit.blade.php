<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Editar categoría</h1>
    </div>

    <div class="max-w-xl">
        <x-admin-card>
            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-6 p-6 sm:p-8 admin-form">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="name" value="Nombre" class="text-slate-300" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full admin-input" :value="old('name', $category->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div class="flex justify-end gap-4 border-t border-white/5 pt-4">
                    <a href="{{ route('categories.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</a>
                    <x-primary-button>Actualizar</x-primary-button>
                </div>
            </form>
        </x-admin-card>
    </div>
</x-app-layout>
