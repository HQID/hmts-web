<div class="sticky top-2 sm:top-2.5 z-40 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full" x-data="{ mobileOpen: false }">
    <header class="bg-[#121217]/95 backdrop-blur-md border border-white/10 rounded-full px-4 sm:px-6 py-2 shadow-2xl flex items-center justify-between gap-4">

        <!-- Logo & Brand (HMTS FT-UNTAD) -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3 group min-w-0">
            <img src="{{ asset('images/hmts.png') }}" alt="Logo HMTS FT-UNTAD" class="w-8 h-8 sm:w-9 sm:h-9 object-contain drop-shadow group-hover:scale-105 transition-transform shrink-0">
            <div class="min-w-0">
                <span class="font-display font-bold text-xs sm:text-sm tracking-tight text-white uppercase block leading-none whitespace-nowrap">HMTS FT-UNTAD</span>
                <span class="font-mono text-[9px] sm:text-[10px] text-zinc-400 block whitespace-nowrap">Teknik Sipil • Univ. Tadulako</span>
            </div>
        </a>

        <!-- 4 Menu Navigasi Utama (Desktop) -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-medium">
            <a href="{{ route('home') }}" 
               class="px-4 py-2 rounded-full transition-all {{ request()->routeIs('home') ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-300 hover:text-white hover:bg-white/5' }}">
                Beranda
            </a>
            <a href="{{ route('about') }}" 
               class="px-4 py-2 rounded-full transition-all {{ request()->routeIs('about') ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-300 hover:text-white hover:bg-white/5' }}">
                Tentang Kami
            </a>
            <a href="{{ route('programs.index') }}" 
               class="px-4 py-2 rounded-full transition-all {{ request()->routeIs('programs.index') ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-300 hover:text-white hover:bg-white/5' }}">
                Kegiatan
            </a>
            <a href="{{ route('services.hub') }}" 
               class="px-4 py-2 rounded-full transition-all {{ request()->routeIs('services.hub') ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-300 hover:text-white hover:bg-white/5' }}">
                Layanan
            </a>
        </nav>

        <!-- Aksi Kanan: Tombol Login Pengurus & Mobile Hamburger -->
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" 
                    @click="$dispatch('open-login-modal')" 
                    class="hidden md:inline-flex items-center gap-2 bg-brand-orange hover:bg-brand-orangeHover text-white px-4 sm:px-5 py-2 rounded-full text-xs font-mono font-bold uppercase tracking-wider transition-all duration-150 active:scale-[0.98] shadow-lg shadow-brand-orange/20 cursor-pointer">
                <span>Login Pengurus</span>
                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[11px]">↗</span>
            </button>

            <!-- Mobile Menu Toggle Button -->
            <button type="button" 
                    @click="mobileOpen = !mobileOpen" 
                    class="md:hidden p-2 rounded-full bg-white/5 border border-white/10 text-zinc-300 hover:text-white"
                    aria-label="Toggle Menu">
                <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </header>

    <!-- Mobile Dropdown Menu -->
    <div x-show="mobileOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.away="mobileOpen = false" 
         x-cloak
         class="md:hidden mt-2 p-3 rounded-2xl bg-[#121217]/95 border border-white/10 backdrop-blur-xl shadow-2xl flex flex-col gap-1.5">
        <a href="{{ route('home') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-medium {{ request()->routeIs('home') ? 'bg-brand-orange text-white font-bold' : 'text-zinc-300 hover:bg-white/5' }}">
            Beranda
        </a>
        <a href="{{ route('about') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-medium {{ request()->routeIs('about') ? 'bg-brand-orange text-white font-bold' : 'text-zinc-300 hover:bg-white/5' }}">
            Tentang Kami
        </a>
        <a href="{{ route('programs.index') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-medium {{ request()->routeIs('programs.index') ? 'bg-brand-orange text-white font-bold' : 'text-zinc-300 hover:bg-white/5' }}">
            Kegiatan
        </a>
        <a href="{{ route('services.hub') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-medium {{ request()->routeIs('services.hub') ? 'bg-brand-orange text-white font-bold' : 'text-zinc-300 hover:bg-white/5' }}">
            Layanan
        </a>

        <div class="h-px bg-white/10 my-1"></div>

        <button type="button" 
                @click="$dispatch('open-login-modal'); mobileOpen = false" 
                class="w-full flex items-center justify-center gap-2 bg-brand-orange hover:bg-brand-orangeHover text-white px-4 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider transition-all duration-150 active:scale-[0.98] shadow-lg shadow-brand-orange/20 cursor-pointer">
            <span>Login Pengurus</span>
            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[11px]">↗</span>
        </button>
    </div>
</div>
