<x-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Personal</h1>
            <p class="mt-1 text-sm text-slate-500">Equipo, vocalistas y contactos del ministerio</p>
        </div>
        <a href="{{ route('contacts.create') }}" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-500">+ Nueva persona</a>
    </div>

    @foreach (['contact-created' => 'Persona registrada.', 'contact-updated' => 'Persona actualizada.', 'contact-deleted' => 'Persona eliminada.'] as $key => $message)
        @if (session('status') === $key)
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ $message }}</div>
        @endif
    @endforeach

    <x-admin-card>
        @if ($contacts->isEmpty())
            <div class="p-12 text-center text-sm text-slate-500">
                Sin personal registrado.
                <a href="{{ route('contacts.create') }}" class="mt-2 block text-violet-400 hover:underline">Agregar</a>
            </div>
        @else
            <x-responsive-data-list search-placeholder="Buscar persona…">
                <x-slot:cards>
                    @foreach ($contacts as $contact)
                        <x-data-list-card :search="$contact->name . ' ' . ($contact->role ?? '') . ' ' . ($contact->email ?? '') . ' ' . ($contact->phone ?? '')">
                            <div class="flex items-start gap-3">
                                @if ($contact->photo_path)
                                    <img src="{{ $contact->photoUrl() }}" alt="" class="h-12 w-12 shrink-0 rounded-xl object-cover ring-1 ring-white/10" />
                                @else
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-600/20 text-sm font-semibold text-violet-300 ring-1 ring-white/10">
                                        {{ $contact->initials() }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-slate-200">{{ $contact->name }}</p>
                                    <p class="text-sm text-slate-500">
                                        @if ($contact->role){{ $contact->role }}@endif
                                        @if ($contact->vocal_range) · {{ $contact->vocal_range }}@endif
                                        @if ($contact->vocal_tone) · {{ $contact->vocal_tone }}@endif
                                    </p>
                                    @if ($contact->email || $contact->phone)
                                        <p class="mt-1 text-xs text-slate-600">
                                            @if ($contact->email){{ $contact->email }}@endif
                                            @if ($contact->email && $contact->phone) · @endif
                                            @if ($contact->phone){{ $contact->phone }}@endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 flex gap-3 text-sm">
                                <a href="{{ route('contacts.edit', $contact) }}" class="text-violet-400 hover:text-violet-300">Editar</a>
                                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('¿Eliminar a {{ $contact->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-500 hover:text-red-300">Eliminar</button>
                                </form>
                            </div>
                        </x-data-list-card>
                    @endforeach
                </x-slot:cards>

                <x-slot:table>
                    <table data-datatable class="w-full">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Voz</th>
                                <th>Contacto</th>
                                <th class="!text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contacts as $contact)
                                <tr>
                                    <td class="w-14">
                                        @if ($contact->photo_path)
                                            <img src="{{ $contact->photoUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover ring-1 ring-white/10" />
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-600/20 text-xs font-semibold text-violet-300 ring-1 ring-white/10">
                                                {{ $contact->initials() }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="font-medium text-slate-200">{{ $contact->name }}</td>
                                    <td class="text-slate-400">{{ $contact->role ?? '—' }}</td>
                                    <td class="text-slate-400">
                                        @if ($contact->vocal_range || $contact->vocal_tone)
                                            {{ trim(($contact->vocal_range ?? '') . ' ' . ($contact->vocal_tone ?? '')) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-slate-500 text-xs">
                                        @if ($contact->email)<div>{{ $contact->email }}</div>@endif
                                        @if ($contact->phone)<div>{{ $contact->phone }}</div>@endif
                                        @if (! $contact->email && ! $contact->phone)—@endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('contacts.edit', $contact) }}" class="text-violet-400 hover:text-violet-300">Editar</a>
                                        <span class="mx-2 text-slate-700">·</span>
                                        <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="inline" onsubmit="return confirm('¿Eliminar a {{ $contact->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-500 hover:text-red-300">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>
            </x-responsive-data-list>
        @endif
    </x-admin-card>
</x-app-layout>
