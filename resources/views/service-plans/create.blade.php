<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Nuevo plan de dirección</h1>
        <p class="mt-1 text-sm text-slate-500">Título, fecha, equipo vocal y notas del servicio</p>
    </div>

    <div class="max-w-2xl">
        <x-admin-card>
            <form method="POST" action="{{ route('service-plans.store') }}" class="space-y-6 p-6 sm:p-8 admin-form">
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

                <div>
                    <x-input-label for="notes" value="Notas (opcional)" class="text-slate-300" />
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full admin-input" placeholder="Indicaciones generales del servicio">{{ old('notes') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>

                <div class="border-t border-white/5 pt-6">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-slate-200">Equipo vocal</h3>
                            <p class="text-xs text-slate-500">Añade integrantes con su tono de voz para asignarlos después</p>
                        </div>
                        <button type="button" data-add-member class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-violet-300 hover:bg-white/5">+ Integrante</button>
                    </div>

                    <div data-members-list class="space-y-3">
                        @php $oldMembers = old('members', [['name' => '', 'voice_tone' => '']]); @endphp
                        @foreach ($oldMembers as $index => $member)
                            <div data-member-row class="flex flex-col gap-2 rounded-xl border border-white/10 bg-[#0a0f1a] p-3 sm:flex-row sm:items-end">
                                <div class="flex-1">
                                    <label class="block text-xs text-slate-500">Nombre</label>
                                    <input type="text" name="members[{{ $index }}][name]" value="{{ $member['name'] ?? '' }}" class="mt-1 block w-full admin-input text-sm" placeholder="Ej: María" />
                                </div>
                                <div class="sm:w-44">
                                    <label class="block text-xs text-slate-500">Tono de voz</label>
                                    <select name="members[{{ $index }}][voice_tone]" class="mt-1 block w-full admin-input text-sm">
                                        <option value="">Seleccionar…</option>
                                        @foreach ($voiceTones as $tone)
                                            <option value="{{ $tone }}" @selected(($member['voice_tone'] ?? '') === $tone)>{{ $tone }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" data-remove-member class="rounded-lg px-3 py-2 text-xs text-slate-500 hover:bg-white/5 hover:text-red-300">Quitar</button>
                            </div>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('members.*.name')" />
                    <x-input-error class="mt-2" :messages="$errors->get('members.*.voice_tone')" />
                </div>

                <div class="flex items-center justify-end gap-4 border-t border-white/5 pt-4">
                    <a href="{{ route('service-plans.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</a>
                    <x-primary-button>Crear plan</x-primary-button>
                </div>
            </form>
        </x-admin-card>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const list = document.querySelector('[data-members-list]');
                const addBtn = document.querySelector('[data-add-member]');
                const voiceTones = @json($voiceTones);

                function reindexRows() {
                    list.querySelectorAll('[data-member-row]').forEach((row, index) => {
                        row.querySelectorAll('input, select').forEach((input) => {
                            input.name = input.name.replace(/members\[\d+\]/, `members[${index}]`);
                        });
                    });
                }

                function createRow() {
                    const index = list.querySelectorAll('[data-member-row]').length;
                    const row = document.createElement('div');
                    row.dataset.memberRow = '';
                    row.className = 'flex flex-col gap-2 rounded-xl border border-white/10 bg-[#0a0f1a] p-3 sm:flex-row sm:items-end';
                    row.innerHTML = `
                        <div class="flex-1">
                            <label class="block text-xs text-slate-500">Nombre</label>
                            <input type="text" name="members[${index}][name]" class="mt-1 block w-full admin-input text-sm" placeholder="Ej: María" />
                        </div>
                        <div class="sm:w-44">
                            <label class="block text-xs text-slate-500">Tono de voz</label>
                            <select name="members[${index}][voice_tone]" class="mt-1 block w-full admin-input text-sm">
                                <option value="">Seleccionar…</option>
                                ${voiceTones.map((tone) => `<option value="${tone}">${tone}</option>`).join('')}
                            </select>
                        </div>
                        <button type="button" data-remove-member class="rounded-lg px-3 py-2 text-xs text-slate-500 hover:bg-white/5 hover:text-red-300">Quitar</button>
                    `;
                    list.appendChild(row);
                }

                addBtn?.addEventListener('click', createRow);

                list?.addEventListener('click', (event) => {
                    const btn = event.target.closest('[data-remove-member]');
                    if (!btn) return;
                    const rows = list.querySelectorAll('[data-member-row]');
                    if (rows.length <= 1) {
                        rows[0].querySelector('input').value = '';
                        rows[0].querySelector('select').value = '';
                        return;
                    }
                    btn.closest('[data-member-row]')?.remove();
                    reindexRows();
                });
            });
        </script>
    @endpush
</x-app-layout>
