<x-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Categorías</h1>
            <p class="mt-1 text-sm text-slate-500">Clasifica tu repertorio y planes de dirección</p>
        </div>
        <a href="{{ route('categories.create') }}" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-500">+ Nueva categoría</a>
    </div>

    @if (session('status') === 'category-created')
        <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">Categoría creada.</div>
    @endif
    @if (session('status') === 'category-updated')
        <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">Categoría actualizada.</div>
    @endif
    @if (session('status') === 'category-deleted')
        <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">Categoría eliminada.</div>
    @endif

    <x-admin-card>
        <ul class="divide-y divide-white/5">
            @foreach ($categories as $category)
                <li class="flex items-center justify-between px-5 py-4 hover:bg-white/[0.02]">
                    <div>
                        <p class="font-medium text-slate-200">{{ $category->name }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $category->songs_count }} canción(es)
                            @if ($category->is_system)
                                · <span class="text-amber-400/80">Predeterminada</span>
                            @endif
                        </p>
                    </div>
                    @if ($category->isEditableBy(auth()->id()))
                        <div class="flex gap-3 text-sm">
                            <a href="{{ route('categories.edit', $category) }}" class="text-violet-400 hover:text-violet-300">Editar</a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-500 hover:text-red-300">Eliminar</button>
                            </form>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </x-admin-card>
</x-app-layout>
