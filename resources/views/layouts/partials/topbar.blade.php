<header class="admin-topbar sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-white/5 bg-[#0a0f1a]/90 px-4 backdrop-blur-md sm:px-6">
    <label for="sidebar-toggle" class="cursor-pointer rounded-lg p-2 text-slate-400 hover:bg-white/5 hover:text-white lg:hidden" aria-label="Abrir menú">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </label>

    <div class="hidden max-w-xl flex-1 sm:block">
        <form method="GET" action="{{ route('songs.index') }}" class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Buscar canciones..."
                class="w-full rounded-xl border border-white/10 bg-white/5 py-2 pl-10 pr-4 text-sm text-slate-200 placeholder-slate-500 focus:border-violet-500/50 focus:outline-none focus:ring-1 focus:ring-violet-500/50"
            />
        </form>
    </div>

    <div class="ml-auto flex items-center gap-3">
        <div class="hidden items-center gap-3 sm:flex">
            <div class="text-right">
                <p class="text-sm font-medium text-slate-200">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
            </div>
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-600/30 text-sm font-semibold text-violet-300">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-lg p-2 text-slate-500 hover:bg-white/5 hover:text-slate-300" title="Cerrar sesión">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
            </button>
        </form>
    </div>
</header>
