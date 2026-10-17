<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Command Center HMTS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ambient-canvas min-h-[100dvh] font-sans text-zinc-200">
    <div class="min-h-[100dvh] lg:grid lg:grid-cols-[280px_1fr]">

        <!-- MOBILE TOP BAR (STICKY) -->
        <header class="lg:hidden sticky top-0 z-30 bg-[#0d0d12]/95 backdrop-blur-xl border-b border-white/10 px-4 py-3 flex items-center justify-between gap-3 shadow-lg">
            <a href="{{ route('app.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                <img src="{{ asset('images/hmts.png') }}" alt="HMTS FT-UNTAD" class="h-8 w-8 object-contain drop-shadow shrink-0">
                <div class="min-w-0">
                    <span class="font-display text-xs font-bold text-white tracking-wide block truncate">COMMAND CENTER</span>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                        <span class="font-mono text-[9px] text-zinc-400 uppercase tracking-wider truncate">HMTS FT-UNTAD</span>
                    </div>
                </div>
            </a>

            <button type="button" 
                    id="mobile-sidebar-open-btn" 
                    class="p-2 rounded-xl bg-white/5 border border-white/10 text-zinc-300 hover:text-white hover:bg-white/10 transition-colors shrink-0 cursor-pointer"
                    aria-label="Buka Menu Navigasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </header>

        <!-- BACKDROP OVERLAY UNTUK MOBILE DRAWER -->
        <div id="mobile-sidebar-backdrop" 
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden cursor-pointer"
             aria-hidden="true"></div>

        <!-- SIDEBAR COMMAND CENTER (DRAWER DI MOBILE, STATIC/STICKY DI DESKTOP) -->
        <aside id="sidebar-drawer" 
               class="fixed inset-y-0 left-0 z-50 w-[280px] -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:h-screen lg:sticky lg:top-0 border-r border-white/10 bg-[#0d0d12] lg:bg-[#0d0d12]/95 backdrop-blur-xl flex flex-col justify-between overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden shadow-2xl lg:shadow-none">
            <div class="overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                <!-- Header Branding Sidebar -->
                <div class="p-5 border-b border-white/5 flex items-center justify-between gap-3">
                    <a href="{{ route('app.dashboard') }}" class="flex items-center gap-3 group min-w-0">
                        <img src="{{ asset('images/hmts.png') }}" alt="HMTS FT-UNTAD" class="h-10 w-10 object-contain drop-shadow shrink-0">
                        <div class="min-w-0">
                            <span class="font-display text-sm font-bold text-white tracking-wide block group-hover:text-brand-yellow transition-colors truncate">COMMAND CENTER</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                <span class="font-mono text-[10px] text-zinc-400 uppercase tracking-wider truncate">HMTS FT-UNTAD</span>
                            </div>
                        </div>
                    </a>

                    <!-- Tombol Tutup Khusus Mobile -->
                    <button type="button" 
                            id="mobile-sidebar-close-btn" 
                            class="lg:hidden p-1.5 rounded-xl bg-white/5 border border-white/10 text-zinc-400 hover:text-white hover:bg-white/10 transition-colors shrink-0 cursor-pointer"
                            aria-label="Tutup Menu Navigasi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @php
                    $navSections = [
                        [
                            'title' => 'Menu Utama',
                            'items' => [
                                [
                                    'label' => 'Ringkasan',
                                    'route' => route('app.dashboard'),
                                    'active' => request()->routeIs('app.dashboard'),
                                    'icon' => 'dashboard',
                                ],
                                [
                                    'label' => 'Bagan Kepengurusan',
                                    'route' => route('app.members.index'),
                                    'active' => request()->routeIs('app.members.*'),
                                    'icon' => 'members',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Kegiatan & Administrasi',
                            'items' => [
                                [
                                    'label' => 'Program Kerja',
                                    'route' => route('app.programs.index'),
                                    'active' => request()->routeIs('app.programs.*'),
                                    'icon' => 'programs',
                                ],
                                [
                                    'label' => 'Panitia Program',
                                    'route' => route('app.operations.index', 'committees'),
                                    'active' => request()->routeIs('app.operations.index') && request()->route('module') === 'committees',
                                    'icon' => 'committees',
                                ],
                                [
                                    'label' => 'Notulensi Rapat',
                                    'route' => route('app.meeting-notes.index'),
                                    'active' => request()->routeIs('app.meeting-notes.*'),
                                    'icon' => 'meeting-notes',
                                ],
                                [
                                    'label' => 'Surat & Disposisi',
                                    'route' => route('app.letters.index'),
                                    'active' => request()->routeIs('app.letters.*'),
                                    'icon' => 'letters',
                                ],
                                [
                                    'label' => 'Presensi Mandiri QR',
                                    'route' => route('app.attendance.index'),
                                    'active' => request()->routeIs('app.attendance.*'),
                                    'icon' => 'attendance',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Logistik & Keuangan',
                            'items' => [
                                [
                                    'label' => 'Inventaris & Stok Alat',
                                    'route' => route('app.inventory.index'),
                                    'active' => request()->routeIs('app.inventory.*'),
                                    'icon' => 'inventory',
                                ],
                                [
                                    'label' => 'Peminjaman Alat',
                                    'route' => route('app.borrowings.index'),
                                    'active' => request()->routeIs('app.borrowings.*') || (request()->routeIs('app.operations.index') && request()->route('module') === 'borrowings'),
                                    'icon' => 'borrowings',
                                ],
                                [
                                    'label' => 'Kas & Kuitansi',
                                    'route' => route('app.cash.index'),
                                    'active' => request()->routeIs('app.cash.*'),
                                    'icon' => 'cash',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Layanan & Aset',
                            'items' => [
                                [
                                    'label' => 'Kotak Aspirasi',
                                    'route' => route('app.aspirations.index'),
                                    'active' => request()->routeIs('app.aspirations.*') || (request()->routeIs('app.operations.index') && request()->route('module') === 'aspirations'),
                                    'icon' => 'aspirations',
                                ],
                                [
                                    'label' => 'Bank Aset Digital',
                                    'route' => route('app.assets.index'),
                                    'active' => request()->routeIs('app.assets.*') || (request()->routeIs('app.operations.index') && request()->route('module') === 'assets'),
                                    'icon' => 'assets',
                                ],
                            ],
                        ],
                    ];

                    if (auth()->check() && auth()->user()->isSuperAdmin()) {
                        $navSections[] = [
                            'title' => 'Pengaturan Sistem',
                            'items' => [
                                [
                                    'label' => 'Manajemen Periode',
                                    'route' => route('app.operations.index', 'periods'),
                                    'active' => request()->routeIs('app.operations.index') && request()->route('module') === 'periods',
                                    'icon' => 'periods',
                                ],
                                [
                                    'label' => 'Kelola Akun Admin',
                                    'route' => route('app.operations.index', 'admins'),
                                    'active' => request()->routeIs('app.operations.index') && request()->route('module') === 'admins',
                                    'icon' => 'admins',
                                ],
                            ],
                        ];
                    }
                @endphp

                <!-- Navigasi Menu Dikelompokkan Kategori dengan Komponen Buka Tutup (Collapsible) -->
                <nav class="p-3.5 space-y-3">
                    @foreach($navSections as $section)
                        @php
                            $hasActive = collect($section['items'])->contains('active', true);
                            $sectionId = str($section['title'])->slug();
                        @endphp
                        <details class="group/cat select-none" data-nav-group="{{ $sectionId }}" {{ $hasActive ? 'open' : '' }}>
                            <!-- Header Kategori Bergaya Markdown / Technical (Bisa Diklik Buka Tutup) -->
                            <summary class="px-2.5 py-1.5 flex items-center justify-between text-zinc-500 font-mono text-[11px] font-bold uppercase tracking-wider cursor-pointer hover:text-zinc-200 transition-colors list-none rounded-lg hover:bg-white/[0.04] group-open/cat:text-zinc-400">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-brand-yellow font-semibold">//</span>
                                    <span>{{ $section['title'] }}</span>
                                </div>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-zinc-500 transition-transform duration-200 group-open/cat:rotate-90">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </summary>

                            <div class="mt-1 space-y-1 pl-1">
                                @foreach($section['items'] as $item)
                                    <a href="{{ $item['route'] }}" 
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-all group {{ $item['active'] ? 'bg-brand-orange text-white font-semibold shadow-md shadow-brand-orange/20 border-l-2 border-brand-yellow' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                                        
                                        <!-- Ikon Menu SVG -->
                                        <span class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-110 {{ $item['active'] ? 'text-brand-yellow' : 'text-zinc-400 group-hover:text-white' }}">
                                            @if($item['icon'] === 'dashboard')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                            @elseif($item['icon'] === 'members')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            @elseif($item['icon'] === 'programs')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                                            @elseif($item['icon'] === 'committees')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg>
                                            @elseif($item['icon'] === 'meeting-notes')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                                            @elseif($item['icon'] === 'letters')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                            @elseif($item['icon'] === 'attendance')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                                            @elseif($item['icon'] === 'inventory')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                            @elseif($item['icon'] === 'borrowings')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1"/><path d="m9 14 2 2 4-4"/></svg>
                                            @elseif($item['icon'] === 'cash')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M6 15h.01"/><path d="M10 15h.01"/></svg>
                                            @elseif($item['icon'] === 'aspirations')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            @elseif($item['icon'] === 'assets')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/><circle cx="12" cy="13" r="3"/></svg>
                                            @elseif($item['icon'] === 'periods')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            @elseif($item['icon'] === 'admins')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                            @endif
                                        </span>

                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </nav>
            </div>

            <!-- Bagian Bawah: Profil Pengguna & Keluar -->
            <div class="p-4 border-t border-white/10 bg-[#0A0A0E]/80">
                @if(auth()->check())
                    <div class="flex items-center gap-3 mb-3 px-1">
                        <div class="w-8 h-8 rounded-full bg-brand-orange/20 border border-brand-orange/40 flex items-center justify-center font-display font-bold text-xs text-brand-orange">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-mono uppercase {{ auth()->user()->isSuperAdmin() ? 'bg-brand-yellow/10 text-brand-yellow border border-brand-yellow/30' : 'bg-white/5 text-zinc-400 border border-white/10' }}">
                                {{ auth()->user()->role ?? 'Admin' }}
                            </span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full flex items-center justify-center gap-2 rounded-xl border border-white/10 px-3 py-2 text-xs font-medium text-zinc-400 hover:border-red-500/30 hover:bg-red-500/10 hover:text-red-300 transition-colors cursor-pointer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        <span>Keluar dari Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="p-5 sm:p-8 lg:p-10 min-w-0 overflow-x-hidden">{{ $slot }}</main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Navigation Group Persistence
            document.querySelectorAll('details[data-nav-group]').forEach(el => {
                const key = 'sidebar_nav_' + el.getAttribute('data-nav-group');
                const saved = localStorage.getItem(key);
                if (saved !== null) {
                    el.open = saved === 'true';
                }
                if (el.querySelector('.bg-brand-orange')) {
                    el.open = true;
                }
                el.addEventListener('toggle', () => {
                    localStorage.setItem(key, el.open);
                });
            });

            // Mobile Sidebar Drawer Functionality
            const drawer = document.getElementById('sidebar-drawer');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');
            const openBtn = document.getElementById('mobile-sidebar-open-btn');
            const closeBtn = document.getElementById('mobile-sidebar-close-btn');

            function openSidebar() {
                if (!drawer || !backdrop) return;
                backdrop.classList.remove('hidden');
                requestAnimationFrame(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    drawer.classList.remove('-translate-x-full');
                });
                document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
            }

            function closeSidebar() {
                if (!drawer || !backdrop) return;
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                drawer.classList.add('-translate-x-full');
                setTimeout(() => {
                    if (drawer.classList.contains('-translate-x-full')) {
                        backdrop.classList.add('hidden');
                    }
                }, 300);
                document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
            }

            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);

            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && drawer && !drawer.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
                    closeSidebar();
                }
            });

            // Close on clicking nav links on mobile
            if (drawer) {
                drawer.querySelectorAll('nav a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) {
                            closeSidebar();
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
