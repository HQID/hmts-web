<footer class="bg-black/90 py-12 px-4 sm:px-6 lg:px-8 border-t border-white/10 mt-16 relative z-10">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 font-mono text-xs text-zinc-500">

        <!-- Logo & Identitas Organisasi -->
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/hmts.png') }}" alt="Logo HMTS FT-UNTAD" class="w-8 h-8 object-contain">
            <div>
                <span class="text-white font-semibold">HMTS FT-UNTAD</span> — Periode 2026/2027
                <div class="text-[11px] text-zinc-500">Jurusan Teknik Sipil, Fakultas Teknik Universitas Tadulako</div>
            </div>
        </div>

        <!-- Tautan Navigasi & Sosial Media -->
        <div class="flex flex-col items-center gap-3">
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                <a href="{{ route('home') }}" class="hover:text-brand-yellow transition-colors">Beranda</a>
                <a href="{{ route('about') }}" class="hover:text-brand-yellow transition-colors">Tentang Kami</a>
                <a href="{{ route('programs.index') }}" class="hover:text-brand-yellow transition-colors">Kegiatan</a>
                <a href="{{ route('services.hub') }}" class="hover:text-brand-yellow transition-colors">Layanan Mahasiswa</a>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-zinc-400">
                <a href="https://www.instagram.com/hmts.tadulako/" target="_blank" rel="noopener noreferrer" class="hover:text-brand-yellow transition-colors">Instagram</a>
                <span>•</span>
                <a href="https://youtube.com/@hmtstadulako?si=PJiOm9hgBSJuHWC3" target="_blank" rel="noopener noreferrer" class="hover:text-brand-yellow transition-colors">YouTube</a>
                <span>•</span>
                <a href="https://www.tiktok.com/@hmts.tadulako?_t=ZS-8zkVbiC04jl&_r=1" target="_blank" rel="noopener noreferrer" class="hover:text-brand-yellow transition-colors">TikTok</a>
                <span>•</span>
                <a href="https://x.com/hmtstadulako?s=21" target="_blank" rel="noopener noreferrer" class="hover:text-brand-yellow transition-colors">X</a>
            </div>
        </div>

        <!-- Hak Cipta -->
        <div class="text-[11px] text-zinc-500 text-center md:text-right">
            © {{ date('Y') }} HMTS FT-UNTAD. Kokoh, Inovatif, Membangun Peradaban. <span class="text-brand-yellow font-bold">#WeAreTheChampions</span>
        </div>

    </div>
</footer>
