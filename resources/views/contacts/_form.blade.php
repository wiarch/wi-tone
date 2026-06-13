@php
    $contact = $contact ?? null;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
        <div class="shrink-0">
            <label class="mb-2 block text-xs font-medium uppercase tracking-wider text-slate-500">Foto de perfil</label>
            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-[#0a0f1a]">
                @if ($contact?->photo_path)
                    <img src="{{ $contact->photoUrl() }}" alt="" class="h-full w-full object-cover" data-photo-preview />
                @else
                    <span data-photo-placeholder class="text-2xl font-semibold text-violet-400">{{ $contact ? $contact->initials() : '?' }}</span>
                    <img src="" alt="" class="hidden h-full w-full object-cover" data-photo-preview />
                @endif
            </div>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-xs text-slate-400 file:mr-2 file:rounded-lg file:border-0 file:bg-violet-600 file:px-3 file:py-1.5 file:text-xs file:text-white hover:file:bg-violet-500" data-photo-input />
            @if ($contact?->photo_path)
                <label class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                    <input type="checkbox" name="remove_photo" value="1" class="rounded border-white/20 bg-white/5 text-violet-500" />
                    Quitar foto
                </label>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div class="grid flex-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="name" value="Nombre" class="text-slate-300" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full admin-input" :value="old('name', $contact?->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="role" value="Rol" class="text-slate-300" />
                <select id="role" name="role" class="mt-1 block w-full admin-input text-sm">
                    <option value="">Seleccionar…</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $contact?->role) === $role)>{{ $role }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </div>

            <div>
                <x-input-label for="email" value="Correo" class="text-slate-300" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full admin-input" :value="old('email', $contact?->email)" placeholder="correo@ejemplo.com" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="vocal_range" value="Rango vocal" class="text-slate-300" />
                <select id="vocal_range" name="vocal_range" class="mt-1 block w-full admin-input text-sm">
                    <option value="">Seleccionar…</option>
                    @foreach ($vocalRanges as $range)
                        <option value="{{ $range }}" @selected(old('vocal_range', $contact?->vocal_range) === $range)>{{ $range }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('vocal_range')" />
            </div>

            <div>
                <x-input-label for="phone" value="Teléfono" class="text-slate-300" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full admin-input" :value="old('phone', $contact?->phone)" placeholder="+52 …" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="vocal_tone" value="Tono vocal" class="text-slate-300" />
                <select id="vocal_tone" name="vocal_tone" class="mt-1 block w-full admin-input text-sm">
                    <option value="">Seleccionar…</option>
                    @foreach ($vocalTones as $tone)
                        <option value="{{ $tone }}" @selected(old('vocal_tone', $contact?->vocal_tone) === $tone)>{{ $tone }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('vocal_tone')" />
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-photo-input]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const file = input.files?.[0];
                        const wrap = input.closest('.shrink-0');
                        const preview = wrap?.querySelector('[data-photo-preview]');
                        const placeholder = wrap?.querySelector('[data-photo-placeholder]');
                        if (!file || !preview) return;
                        preview.src = URL.createObjectURL(file);
                        preview.classList.remove('hidden');
                        placeholder?.classList.add('hidden');
                    });
                });
            });
        </script>
    @endpush
@endonce
