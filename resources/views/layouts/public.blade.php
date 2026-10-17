<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HMTS FT-UNTAD - Himpunan Mahasiswa Teknik Sipil Fakultas Teknik Universitas Tadulako. Kokoh, Inovatif, Membangun Peradaban.">
    <title>{{ $title ?? 'HMTS FT-UNTAD | Himpunan Mahasiswa Teknik Sipil Universitas Tadulako' }}</title>

    <!-- Google Fonts: Poppins, Inter, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/hmts.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="ambient-canvas min-h-screen text-zinc-100 flex flex-col font-sans antialiased selection:bg-brand-orange selection:text-white relative overflow-x-hidden">

    <!-- Ambient Glow Lighting System (Fixed Diffused Light Orbs) -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        <!-- Top Center Glow (Steel Orange) -->
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[750px] h-[500px] rounded-full bg-brand-orange/15 blur-[140px]"></div>
        <!-- Upper Right Accent (High-Vis Yellow) -->
        <div class="absolute top-1/4 -right-20 w-[450px] h-[450px] rounded-full bg-brand-yellow/10 blur-[150px]"></div>
        <!-- Mid Left Warmth (Steel Orange) -->
        <div class="absolute top-1/2 -left-20 w-[550px] h-[550px] rounded-full bg-brand-orange/10 blur-[160px]"></div>
        <!-- Bottom Right Subtle (High-Vis Yellow) -->
        <div class="absolute bottom-10 right-10 w-[500px] h-[500px] rounded-full bg-brand-yellow/6 blur-[160px]"></div>
    </div>

    <div class="relative z-10 flex flex-col min-h-screen">
        <!-- TOP MINIMAL UTILITY BAR -->
        <div class="border-b border-white/5 bg-[#0A0A0E]/80 backdrop-blur-sm text-xs font-mono text-zinc-400 py-1.5 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 min-w-0">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 text-[11px] sm:text-xs">
                    <span class="inline-flex items-center gap-1.5 text-zinc-300 font-semibold shrink-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        HMTS FT-UNTAD
                    </span>
                    <span class="hidden sm:inline text-white/20">•</span>
                    <span class="hidden sm:inline truncate">Jurusan Teknik Sipil Universitas Tadulako</span>
                    <span class="hidden md:inline text-white/20">•</span>
                    <span class="hidden md:inline text-brand-yellow font-semibold shrink-0">Akreditasi Unggul</span>
                </div>
                <div class="hidden sm:flex items-center gap-4 text-[11px] shrink-0">
                    <span class="text-brand-yellow font-bold">#WeAreTheChampions</span>
                    <span class="text-white/20">•</span>
                    <span>Palu, Sulawesi Tengah (WITA)</span>
                    <span class="text-white/20">•</span>
                    <a href="{{ route('services.hub') }}?tab=aspiration" class="text-brand-yellow hover:underline flex items-center gap-1">
                        <span>Kotak Aspirasi Mahasiswa</span>
                        <span>↗</span>
                    </a>
                </div>
                <div class="sm:hidden text-[10px] text-brand-yellow font-semibold shrink-0">
                    Akreditasi Unggul
                </div>
            </div>
        </div>

        <!-- FLOATING ISLAND NAVBAR -->
        <x-navbar />

        <!-- MAIN CONTENT AREA -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- FOOTER -->
        <x-footer />
    </div>

    <!-- MODAL LOGIN PENGURUS -->
    <x-admin-login-modal />

    @livewireScripts
    @stack('scripts')
</body>
</html>
