<x-layouts.public title="Beranda | HMTS FT-UNTAD">

    <!-- 1. FRAMED HERO CONTAINER (KOMPAK UNTUK VIEWPORT LAPTOP 150%) -->
    <section class="pt-3 sm:pt-4 pb-8 sm:pb-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="relative rounded-[2rem] sm:rounded-[2.5rem] border border-white/10 overflow-hidden bg-[#101017] min-h-[440px] sm:min-h-[460px] p-6 sm:p-8 lg:p-10 flex flex-col justify-between">

                <!-- Background Image dengan Vignette -->
                <div class="absolute inset-0 z-0">
                    <img src="https://images.unsplash.com/photo-1541888946425-d0fbb186c5f7?auto=format&fit=crop&w=1600&q=80"
                         alt="Teknik Sipil UNTAD"
                         class="w-full h-full object-cover object-center opacity-25 contrast-125">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#0A0A0E] via-[#0A0A0E]/85 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0A0A0E] via-transparent to-transparent"></div>
                </div>

                <!-- Hero Konten: Grid 2 Kolom (Teks & Logo Besar Bersebelahan) -->
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center pt-2">

                    <!-- Kolom Kiri: Headline & CTA -->
                    <div class="lg:col-span-8">
                        <h1 class="font-display font-black text-2xl sm:text-4xl lg:text-5xl text-white leading-[1.08] tracking-tight uppercase mb-3 animate-hero-slide animate-delay-75">
                            Himpunan Mahasiswa <br>
                            <span class="text-brand-yellow">Teknik Sipil</span>
                        </h1>

                        <!-- Tagar Resmi HMTS FT-UNTAD -->
                        <div class="badge-shimmer-container inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-brand-yellow/10 border border-brand-yellow/30 text-brand-yellow font-mono text-xs font-bold tracking-wider mb-4 animate-hero-slide animate-delay-150">
                            <span>#WeAreTheChampions</span>
                        </div>

                        <p class="text-zinc-300 text-xs sm:text-sm leading-relaxed max-w-xl mb-6 font-sans animate-hero-slide animate-delay-200">
                            Pusat informasi resmi kegiatan akademik, riset kebencanaan, advokasi kemahasiswaan, dan layanan penunjang civitas Teknik Sipil Fakultas Teknik Universitas Tadulako.
                        </p>

                        <div class="flex flex-wrap items-center gap-3 animate-hero-slide animate-delay-300">
                            <a href="{{ route('programs.index') }}" 
                               class="group inline-flex items-center gap-2.5 bg-brand-orange hover:bg-brand-orangeHover text-white px-6 py-3 rounded-full font-mono text-xs sm:text-sm font-bold uppercase tracking-wider transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-brand-orange/25">
                                <span>Jelajahi Kegiatan</span>
                                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform">↗</span>
                            </a>

                            <a href="{{ route('services.hub') }}" 
                               class="inline-flex items-center gap-2 bg-[#181820]/80 hover:bg-[#1e1e28] border border-white/15 hover:border-white/30 text-white px-5 py-3 rounded-full font-mono text-xs sm:text-sm font-semibold uppercase tracking-wider transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                                <span>Layanan Mahasiswa</span>
                            </a>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Lambang Resmi Besar (Floating & Glowing) -->
                    <div class="lg:col-span-4 flex justify-center lg:justify-end animate-hero-scale animate-delay-200">
                        <div class="relative flex items-center justify-center animate-emblem-float">
                            <div class="absolute -inset-6 rounded-full bg-gradient-to-tr from-brand-orange/25 to-brand-yellow/20 blur-3xl opacity-75 animate-ambient-glow"></div>
                            <img src="{{ asset('images/hmts.png') }}" 
                                 alt="Lambang Resmi HMTS FT-UNTAD" 
                                 class="relative w-44 h-44 sm:w-56 sm:h-56 lg:w-64 lg:h-64 object-contain drop-shadow-[0_25px_35px_rgba(0,0,0,0.85)] hover:scale-105 transition-transform duration-300">
                        </div>
                    </div>

                </div>

                <!-- Quick Access Strip di Dasar Hero -->
                <div class="relative z-10 pt-8 mt-8 border-t border-white/10 animate-hero-slide animate-delay-400">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-mono text-zinc-400">
                            <span class="text-brand-yellow font-semibold">HMTS FT-UNTAD</span>
                            <span class="text-white/20">•</span>
                            <span>Periode {{ $periodName }}</span>
                            <span class="text-white/20">•</span>
                            <span class="text-brand-yellow font-bold">#WeAreTheChampions</span>
                        </div>

                        <!-- 4 Shortcut Layanan Cepat -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 w-full lg:w-auto">
                            <a href="{{ route('services.hub') }}?tab=borrow" 
                               class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-brand-yellow/30 text-xs font-mono text-zinc-300 hover:text-white transition-all flex items-center justify-between gap-2 group">
                                <span>Pinjam Alat Lab</span>
                                <span class="text-brand-yellow group-hover:translate-x-0.5 transition-transform">→</span>
                            </a>
                            <a href="{{ route('services.hub') }}?tab=qr" 
                               class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-brand-yellow/30 text-xs font-mono text-zinc-300 hover:text-white transition-all flex items-center justify-between gap-2 group">
                                <span>Presensi QR</span>
                                <span class="text-brand-yellow group-hover:translate-x-0.5 transition-transform">→</span>
                            </a>
                            <a href="{{ route('services.hub') }}?tab=aspiration" 
                               class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-brand-yellow/30 text-xs font-mono text-zinc-300 hover:text-white transition-all flex items-center justify-between gap-2 group">
                                <span>Kotak Aspirasi</span>
                                <span class="text-brand-yellow group-hover:translate-x-0.5 transition-transform">→</span>
                            </a>
                            <a href="{{ route('services.hub') }}?tab=assets" 
                               class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-brand-yellow/30 text-xs font-mono text-zinc-300 hover:text-white transition-all flex items-center justify-between gap-2 group">
                                <span>Bank Aset</span>
                                <span class="text-brand-yellow group-hover:translate-x-0.5 transition-transform">→</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- STRIP AFILIASI & AKREDITASI (DYNAMIC MARQUEE TICKER) -->
    <section class="py-3 px-4 sm:px-6 lg:px-8 border-y border-white/5 bg-[#0D0D12]/80 backdrop-blur-sm overflow-hidden select-none">
        <div class="max-w-7xl mx-auto flex items-center gap-4">
            <div class="shrink-0 flex items-center gap-2 pr-4 border-r border-white/10">
                <span class="w-2 h-2 rounded-full bg-brand-orange animate-ping"></span>
                <span class="text-zinc-400 uppercase font-mono tracking-widest text-[10px] font-bold">Afiliasi & Kemitraan:</span>
            </div>
            <div class="marquee-wrapper relative flex-1 overflow-hidden">
                <div class="marquee-track text-xs font-mono text-zinc-400">
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">LAM-Teknik (Akreditasi Unggul)</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">BMPTTSSI Wilayah VIII</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">HAKI Indonesia</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">LPJK Sulawesi Tengah</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">Kementerian PUPR RI</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">Biro Konstruksi Sulawesi Tengah</span>
                    <span class="text-white/20">•</span>
                </div>
                <div class="marquee-track text-xs font-mono text-zinc-400" aria-hidden="true">
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">LAM-Teknik (Akreditasi Unggul)</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">BMPTTSSI Wilayah VIII</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">HAKI Indonesia</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">LPJK Sulawesi Tengah</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">Kementerian PUPR RI</span>
                    <span class="text-white/20">•</span>
                    <span class="hover:text-brand-yellow transition-colors whitespace-nowrap">Biro Konstruksi Sulawesi Tengah</span>
                    <span class="text-white/20">•</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. TENTANG HMTS & METRIK KUNCI -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

                <!-- Kolom Kiri: Narasi & Metrik -->
                <div class="lg:col-span-7 space-y-6 motion-reveal">
                    <div>
                        <span class="text-brand-yellow font-mono text-xs font-semibold block mb-1">Mengenal Organisasi</span>
                        <h2 class="font-display font-black text-2xl sm:text-4xl text-white uppercase tracking-tight">
                            Wadah Aspirasi & Prestasi Mahasiswa Sipil Bumi Tadulako
                        </h2>
                    </div>

                    <p class="text-zinc-300 text-sm sm:text-base leading-relaxed font-sans">
                        Sejak 1994, Himpunan Mahasiswa Teknik Sipil Universitas Tadulako (HMTS UNTAD) berdedikasi membangun keilmuan keteknikan yang adaptif, kepemimpinan berintegritas, serta kontribusi aktif dalam rekayasa infrastruktur tahan gempa dan mitigasi pasca-bencana likuefaksi di Sulawesi Tengah.
                    </p>

                    <!-- 4 Kartu Metrik Kunci -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                        <div class="p-4 rounded-2xl bg-[#121217] border border-white/5 motion-reveal delay-75 hover:border-brand-yellow/30 hover:scale-[1.02] transition-all">
                            <span class="font-display font-black text-2xl text-white block">640+</span>
                            <span class="font-mono text-xs text-zinc-400">Mahasiswa Aktif</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#121217] border border-white/5 motion-reveal delay-150 hover:border-brand-yellow/30 hover:scale-[1.02] transition-all">
                            <span class="font-display font-black text-2xl text-white block">1994</span>
                            <span class="font-mono text-xs text-zinc-400">Tahun Berdiri</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#121217] border border-white/5 motion-reveal delay-225 hover:border-brand-yellow/30 hover:scale-[1.02] transition-all">
                            <span class="font-display font-black text-2xl text-brand-yellow block">Unggul</span>
                            <span class="font-mono text-xs text-zinc-400">LAM-Teknik</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#121217] border border-white/5 motion-reveal delay-300 hover:border-brand-yellow/30 hover:scale-[1.02] transition-all">
                            <span class="font-display font-black text-2xl text-white block">1.200+</span>
                            <span class="font-mono text-xs text-zinc-400">Jejaring Alumni</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('about') }}" 
                           class="inline-flex items-center gap-2 bg-brand-orange hover:bg-brand-orangeHover text-white px-6 py-3 rounded-full font-mono text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-brand-orange/20 active:scale-[0.98]">
                            <span>Pelajari Profil Organisasi</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>

                <!-- Kolom Kanan: Card Nilai Utama -->
                <div class="lg:col-span-5 motion-reveal delay-200">
                    <div class="clean-card p-6 sm:p-8 bg-gradient-to-br from-[#15151e] to-[#0d0d12]">
                        <span class="text-xs font-mono text-brand-yellow font-bold uppercase block mb-2">Semboyan Utama</span>
                        <h3 class="font-display font-black text-xl text-white uppercase mb-3">
                            "Kokoh, Inovatif, Membangun Peradaban"
                        </h3>
                        <p class="text-xs text-zinc-400 leading-relaxed mb-6 font-sans">
                            Fondasi moral yang teguh, kapabilitas perancangan infrastruktur yang adaptif, dan kontribusi nyata yang bermanfaat bagi kesejahteraan masyarakat.
                        </p>

                        <div class="border-t border-white/10 pt-4 space-y-2.5 text-xs font-mono text-zinc-300">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Masa Bakti</span>
                                <span class="text-white font-semibold">Periode {{ $periodName }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Forum Nasional</span>
                                <span class="text-white font-semibold">BMPTTSSI Wilayah VIII</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Sekretariat</span>
                                <span class="text-white font-semibold">Kampus Tondo, Palu</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 3. BIDANG KEILMUAN TEKNIK SIPIL (5 PILAR) -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8 border-t border-white/5 bg-[#0D0D12]/70 backdrop-blur-sm relative z-10">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-6 mb-8 border-b border-white/10 motion-reveal">
                <div>
                    <span class="text-brand-yellow font-mono text-xs font-semibold block mb-1">Fokus Keteknikan</span>
                    <h2 class="font-display font-black text-2xl sm:text-3xl text-white uppercase">5 Konsentrasi Keilmuan Teknik Sipil</h2>
                </div>
                <p class="text-xs font-mono text-zinc-400 max-w-md">
                    Pilar kurikulum dan riset terapan yang ditekuni mahasiswa Departemen Teknik Sipil UNTAD.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($fields as $index => $field)
                    <div class="{{ $field['active'] ? 'clean-card-active' : 'clean-card' }} p-5 flex flex-col justify-between motion-reveal delay-{{ ($index + 1) * 75 }}">
                        <div>
                            <span class="text-brand-yellow font-mono font-bold text-xs block mb-2">{{ $field['number'] }} // {{ $field['tag'] }}</span>
                            <h4 class="font-display font-bold text-sm text-white uppercase mb-2">{{ $field['title'] }}</h4>
                            <p class="text-xs {{ $field['active'] ? 'text-zinc-300' : 'text-zinc-400' }} leading-relaxed">{{ $field['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 4. AKSES MANDIRI MAHASISWA -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8 border-t border-white/5 bg-[#0D0D12]/70 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-12 motion-reveal">
                <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Layanan Civitas</span>
                <h2 class="font-display font-black text-2xl sm:text-3xl text-white uppercase">Akses Mandiri Mahasiswa</h2>
                <p class="text-xs font-mono text-zinc-400 mt-2">Pusat fasilitas digital penunjang akademik, kegiatan lapangan, dan aspirasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('services.hub') }}?tab=borrow" class="clean-card p-6 flex flex-col justify-between group cursor-pointer hover:border-brand-orange/50 motion-reveal delay-75">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-300 mb-4 group-hover:border-brand-orange group-hover:text-brand-orange transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.67 2.67 0 0021 17.25l-5.83-5.83m-3.75 3.75a5.98 5.98 0 01-1.67-.34l-5.74 5.74a1.5 1.5 0 01-2.12-2.12l5.74-5.74A5.98 5.98 0 013 11.42C3 8.09 5.69 5.4 9.02 5.4c1.19 0 2.29.35 3.22.95l3.41-3.41a1.5 1.5 0 012.12 2.12l-3.41 3.41c.6.93.95 2.03.95 3.22a5.98 5.98 0 01-.34 1.67z"></path></svg>
                        </div>
                        <h4 class="font-display font-bold text-base text-white uppercase mb-1">Peminjaman Alat Lab</h4>
                        <p class="text-xs text-zinc-400 leading-relaxed font-sans">Katalog instrumen ukur (Total Station, Theodolite, Waterpass) & form pengajuan online.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs text-brand-orange font-semibold flex items-center justify-between">
                        <span>Buka Layanan</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </div>
                </a>

                <a href="{{ route('services.hub') }}?tab=qr" class="clean-card p-6 flex flex-col justify-between group cursor-pointer hover:border-brand-orange/50 motion-reveal delay-150">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-300 mb-4 group-hover:border-brand-orange group-hover:text-brand-orange transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM16.5 13.5h3.75m-3.75 3.75h3.75m-3.75 3.75h3.75M13.5 13.5v7.5"></path></svg>
                        </div>
                        <h4 class="font-display font-bold text-base text-white uppercase mb-1">Presensi Mandiri QR</h4>
                        <p class="text-xs text-zinc-400 leading-relaxed font-sans">Pemindai kamera QR dan input token 6-digit untuk seminar, rapat, & praktikum.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs text-brand-orange font-semibold flex items-center justify-between">
                        <span>Buka Layanan</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </div>
                </a>

                <a href="{{ route('services.hub') }}?tab=aspiration" class="clean-card p-6 flex flex-col justify-between group cursor-pointer hover:border-brand-orange/50 motion-reveal delay-225">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-300 mb-4 group-hover:border-brand-orange group-hover:text-brand-orange transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path></svg>
                        </div>
                        <h4 class="font-display font-bold text-base text-white uppercase mb-1">Kotak Aspirasi</h4>
                        <p class="text-xs text-zinc-400 leading-relaxed font-sans">Kanal aduan anonim bebas pencatatan identitas dengan nomor tiket pelacak acak.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs text-brand-orange font-semibold flex items-center justify-between">
                        <span>Buka Layanan</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </div>
                </a>

                <a href="{{ route('services.hub') }}?tab=assets" class="clean-card p-6 flex flex-col justify-between group cursor-pointer hover:border-brand-orange/50 motion-reveal delay-300">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-300 mb-4 group-hover:border-brand-orange group-hover:text-brand-orange transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path></svg>
                        </div>
                        <h4 class="font-display font-bold text-base text-white uppercase mb-1">Bank Aset & Repositori</h4>
                        <p class="text-xs text-zinc-400 leading-relaxed font-sans">Unduhan terbuka berkas TOR, modul praktikum, blueprint CAD, dan master logo.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs text-brand-orange font-semibold flex items-center justify-between">
                        <span>Buka Layanan</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- 6. SEKRETARIAT & PETA LOKASI INTERAKTIF -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8 border-t border-white/5">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

                <div class="lg:col-span-5 clean-card p-6 sm:p-8 flex flex-col justify-between space-y-6 rounded-3xl border border-white/10 motion-reveal delay-75">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300 mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-yellow"></span>
                            <span>Titik Temu & Koordinasi</span>
                        </div>
                        <h3 class="font-display font-bold text-2xl sm:text-3xl text-white uppercase tracking-tight">Sekretariat HMTS FT-UNTAD</h3>
                        <p class="text-xs text-zinc-400 font-sans mt-2 leading-relaxed">
                            Pusat administrasi, koordinasi kegiatan mahasiswa, dan sekretariat resmi Himpunan Mahasiswa Teknik Sipil Universitas Tadulako.
                        </p>
                    </div>

                    <div class="space-y-3.5 font-mono text-xs text-zinc-300">
                        <div class="p-4 rounded-2xl bg-black/40 border border-white/5 space-y-1">
                            <span class="text-zinc-500 text-[11px] uppercase tracking-wider block">Lokasi Sekretariat:</span>
                            <p class="text-white font-sans text-xs leading-relaxed font-medium">
                                Sekret Luar HMTS-FT UNTAD, Kampus Bumi Tadulako, Jl. Soekarno-Hatta Km. 9, Tondo, Kota Palu, Sulawesi Tengah 94118.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5">
                                <span class="text-zinc-500 text-[11px] uppercase tracking-wider block mb-1">Instagram</span>
                                <a href="https://www.instagram.com/hmts.tadulako/" target="_blank" rel="noopener noreferrer" class="text-white hover:text-brand-orange font-sans text-xs font-semibold transition-colors flex items-center justify-between">
                                    <span>@hmts.tadulako</span>
                                    <span class="text-brand-orange text-[11px]">↗</span>
                                </a>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5">
                                <span class="text-zinc-500 text-[11px] uppercase tracking-wider block mb-1">YouTube</span>
                                <a href="https://youtube.com/@hmtstadulako?si=PJiOm9hgBSJuHWC3" target="_blank" rel="noopener noreferrer" class="text-white hover:text-brand-orange font-sans text-xs font-semibold transition-colors flex items-center justify-between">
                                    <span>@hmtstadulako</span>
                                    <span class="text-brand-orange text-[11px]">↗</span>
                                </a>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5">
                                <span class="text-zinc-500 text-[11px] uppercase tracking-wider block mb-1">TikTok</span>
                                <a href="https://www.tiktok.com/@hmts.tadulako?_t=ZS-8zkVbiC04jl&_r=1" target="_blank" rel="noopener noreferrer" class="text-white hover:text-brand-orange font-sans text-xs font-semibold transition-colors flex items-center justify-between">
                                    <span>@hmts.tadulako</span>
                                    <span class="text-brand-orange text-[11px]">↗</span>
                                </a>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5">
                                <span class="text-zinc-500 text-[11px] uppercase tracking-wider block mb-1">X (Twitter)</span>
                                <a href="https://x.com/hmtstadulako?s=21" target="_blank" rel="noopener noreferrer" class="text-white hover:text-brand-orange font-sans text-xs font-semibold transition-colors flex items-center justify-between">
                                    <span>@hmtstadulako</span>
                                    <span class="text-brand-orange text-[11px]">↗</span>
                                </a>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5">
                            <span class="text-zinc-500 text-[11px] uppercase tracking-wider block mb-1">Email Resmi</span>
                            <a href="mailto:sekretariat@hmts-untad.ac.id" class="text-white hover:text-brand-yellow font-sans text-xs font-semibold transition-colors truncate block">
                                sekretariat@hmts-untad.ac.id
                            </a>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-white/5">
                        <a href="https://www.google.com/maps/search/?api=1&query=Sekret+luar+HMTS-FT+UNTAD"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 text-white px-5 py-3 font-mono text-xs font-bold uppercase tracking-wider transition-all shadow-md group">
                            <span>Buka Rute di Google Maps</span>
                            <span class="text-brand-yellow group-hover:translate-x-0.5 transition-transform">↗</span>
                        </a>
                    </div>
                </div>

                <!-- Embed Google Maps -->
                <div class="lg:col-span-7 clean-card p-3 sm:p-4 overflow-hidden rounded-3xl border border-white/10 flex flex-col min-h-[420px] lg:min-h-[480px] motion-reveal delay-150">
                    <div class="flex items-center justify-between pb-3 px-1 border-b border-white/10 mb-3 text-xs font-mono">
                        <div class="flex items-center gap-2">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="font-bold text-white uppercase tracking-wider">Peta Lokasi Interaktif</span>
                        </div>
                        <span class="text-zinc-400 text-[11px]">Palu, Sulawesi Tengah</span>
                    </div>

                    <div class="relative w-full flex-1 rounded-2xl overflow-hidden border border-white/10 bg-[#121217]">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63829.427779882484!2d119.79293552167968!3d-0.887914799999987!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2d8bedd4f9ce9685%3A0xc78c66430365c5e3!2sSekret%20luar%20HMTS-FT%20UNTAD!5e0!3m2!1sid!2sid!4v1792241738768!5m2!1sid!2sid"
                            class="w-full h-full border-0 filter grayscale-[20%] contrast-[1.05] brightness-[0.92] hover:grayscale-0 hover:brightness-100 transition-all duration-300"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>

                        <!-- Floating Location Badge -->
                        <div class="absolute bottom-4 left-4 pointer-events-none rounded-xl border border-white/10 bg-[#0D0D12]/90 backdrop-blur-md px-3.5 py-2 text-xs font-mono shadow-xl hidden sm:block">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-yellow"></span>
                                <span class="text-white font-bold">Sekret Luar HMTS-FT UNTAD</span>
                                <span class="text-zinc-500 text-[11px]">• Kampus Bumi Tadulako</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</x-layouts.public>
