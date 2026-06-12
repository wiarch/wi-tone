@php
    $song = $song ?? null;
    $guitarContent = old('guitar_content', $song?->chords?->firstWhere('instrument', 'guitar')?->content ?? '');
    $keyboardContent = old('keyboard_content', $song?->chords?->firstWhere('instrument', 'keyboard')?->content ?? '');
@endphp

<div class="space-y-6">
  <div class="grid gap-6 sm:grid-cols-2">
    <div>
      <x-input-label for="title" value="Título" />
      <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $song?->title ?? '')" required autofocus />
      <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
      <x-input-label for="artist" value="Artista / Autor" />
      <x-text-input id="artist" name="artist" type="text" class="mt-1 block w-full" :value="old('artist', $song?->artist ?? '')" required />
      <x-input-error class="mt-2" :messages="$errors->get('artist')" />
    </div>
  </div>

  <div class="sm:w-48">
    <x-input-label for="key" value="Tono principal" />
    <x-text-input id="key" name="key" type="text" class="mt-1 block w-full" :value="old('key', $song?->key ?? '')" placeholder="Ej: G, D, Bm" required />
    <x-input-error class="mt-2" :messages="$errors->get('key')" />
  </div>

  <div x-data="{ tab: 'guitar' }">
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex gap-6" aria-label="Instrumentos">
        <button
          type="button"
          @click="tab = 'guitar'"
          :class="tab === 'guitar' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
          class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition"
        >
          Guitarra
        </button>
        <button
          type="button"
          @click="tab = 'keyboard'"
          :class="tab === 'keyboard' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
          class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition"
        >
          Teclado
        </button>
      </nav>
    </div>

    <div class="mt-4">
      <div x-show="tab === 'guitar'" x-cloak>
        <x-input-label for="guitar_content" value="Cifrado / letra — Guitarra" />
        <textarea
          id="guitar_content"
          name="guitar_content"
          rows="16"
          class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          placeholder="Pega aquí el cifrado con acordes sobre la letra..."
        >{{ $guitarContent }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('guitar_content')" />
      </div>

      <div x-show="tab === 'keyboard'" x-cloak>
        <x-input-label for="keyboard_content" value="Cifrado / letra — Teclado" />
        <textarea
          id="keyboard_content"
          name="keyboard_content"
          rows="16"
          class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          placeholder="Pega aquí los acordes y voicings para teclado..."
        >{{ $keyboardContent }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('keyboard_content')" />
      </div>
    </div>

    <p class="mt-2 text-sm text-gray-500">Puedes completar uno o ambos instrumentos. Las pestañas solo organizan la vista; ambos campos se envían al guardar.</p>
  </div>
</div>
