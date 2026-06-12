@php
    $navItem = fn (bool $active) => $active
        ? 'bg-violet-600/15 text-white border-l-2 border-violet-500'
        : 'text-slate-400 hover:bg-white/5 hover:text-slate-200 border-l-2 border-transparent';
@endphp

<aside class="sidebar-panel fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-white/5 bg-[#0c1222] transition-transform duration-200 peer-checked:translate-x-0 lg:translate-x-0">
    <div class="flex h-16 items-center gap-2.5 border-b border-white/5 px-5">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-600 text-xs font-bold text-white">Wi</span>
        <span class="text-lg font-semibold tracking-tight text-white">{{ config('app.name') }}</span>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        <div>
            <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Principal</p>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('dashboard')) }}">
                <svg class="h-5 w-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Dashboard
            </a>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Gestión</p>
            <div class="space-y-0.5">
                <a href="{{ route('songs.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('songs.*')) }}">
                    <svg class="h-5 w-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.5.375a2.25 2.25 0 01-2.163-1.632L12.75 15v-3.75m0 0l-10.5-3m10.5 3l-10.5 3" /></svg>
                    Canciones
                </a>
                <a href="{{ route('categories.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('categories.*')) }}">
                    <svg class="h-5 w-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                    Categorías
                </a>
                <a href="{{ route('service-plans.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('service-plans.*')) }}">
                    <svg class="h-5 w-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                    Planes de dirección
                </a>
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Herramientas</p>
            <div class="space-y-0.5">
                <a href="{{ route('chords.guitar') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('chords.guitar')) }}">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center text-base opacity-80">🎸</span>
                    Acordes guitarra
                </a>
                <a href="{{ route('tools.tuner') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('tools.tuner')) }}">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center text-base opacity-80">🎯</span>
                    Afinador
                </a>
                <a href="{{ route('tools.metronome') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('tools.metronome')) }}">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center text-base opacity-80">⏱</span>
                    Metrónomo
                </a>
                <a href="{{ route('tools.circle-of-fifths') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('tools.circle-of-fifths')) }}">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center text-base opacity-80">⭕</span>
                    Círculo de quintas
                </a>
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Administración</p>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $navItem(request()->routeIs('profile.*')) }}">
                <svg class="h-5 w-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                Mi perfil
            </a>
        </div>
    </nav>

    <div class="border-t border-white/5 p-4">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-white/5">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-600/30 text-sm font-semibold text-violet-300">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-slate-200">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
            </div>
        </a>
    </div>
</aside>
