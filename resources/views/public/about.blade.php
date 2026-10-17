<x-layouts.public title="Tentang Kami | HMTS FT-UNTAD">

    <!-- HEADER / HERO SECTION TENTANG KAMI -->
    <section class="pt-10 pb-12 px-4 sm:px-6 lg:px-8 border-b border-white/10">
        <div class="max-w-7xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300 mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-yellow"></span>
                <span>Profil Organisasi</span>
            </div>
            <h1 class="font-display font-black text-3xl sm:text-5xl text-white uppercase tracking-tight mb-3">
                Sejarah, Visi Misi, & <span class="text-brand-yellow">Struktur Pengurus</span>
            </h1>
            <p class="text-zinc-300 text-sm sm:text-base max-w-2xl leading-relaxed font-sans">
                Mengenal tonggak sejarah, prinsip nilai perjuangan, dan susunan kepengurusan HMTS FT-UNTAD Periode {{ $periodName }}.
            </p>
        </div>
    </section>

    <!-- 1. SEJARAH & LOGO RESMI -->
    <section class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            <!-- Sejarah Singkat -->
            <div class="lg:col-span-7 clean-card p-6 sm:p-8 flex flex-col justify-between">
                <div>
                    <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-2">Lintas Sejarah</span>
                    <h3 class="font-display font-bold text-xl text-white uppercase mb-4">Sejarah Singkat HMTS FT-UNTAD</h3>
                    <div class="space-y-4 text-xs sm:text-sm text-zinc-300 leading-relaxed font-sans">
                        <p>
                            Himpunan Mahasiswa Teknik Sipil Fakultas Teknik Universitas Tadulako didirikan seiring dengan berdirinya Program Studi Teknik Sipil pada tahun 1994 di Kampus Bumi Tadulako, Kota Palu, Sulawesi Tengah.
                        </p>
                        <p>
                            Sebagai lembaga kemahasiswaan tertua dan terbesar di Fakultas Teknik, HMTS hadir sebagai wadah penalaran ilmiah keinsinyuran, pengkaderan karakter berintegritas, serta pelopor pengabdian masyarakat saat mitigasi kebencanaan pasca-gempa dan likuefaksi Palu 2018.
                        </p>
                        <p>
                            Dengan semboyan <strong class="text-white">"Kokoh, Inovatif, Membangun Peradaban"</strong>, HMTS kini menaungi lebih dari 640 mahasiswa aktif serta ribuan alumni yang berkarya di instansi pemerintah, BUMN Konstruksi, dan konsultan perencana nasional.
                        </p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between font-mono text-xs text-zinc-400">
                    <span>Didirikan Tahun 1994</span>
                    <span class="text-brand-yellow">Bumi Tadulako, Palu</span>
                </div>
            </div>

            <!-- Logo Resmi HMTS FT-UNTAD (Full Logo Saja) -->
            <div class="lg:col-span-5 clean-card p-6 sm:p-8 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-brand-yellow/10 via-transparent to-transparent opacity-70 pointer-events-none"></div>
                <div class="relative z-10 flex flex-col items-center justify-center py-4">
                    <div class="p-6 sm:p-8 rounded-3xl bg-black/40 border border-white/10 shadow-2xl backdrop-blur-sm group-hover:border-brand-yellow/30 transition-all duration-300">
                        <img src="{{ asset('images/hmts.png') }}"
                             alt="Logo Resmi HMTS FT-UNTAD"
                             class="w-48 h-48 sm:w-56 sm:h-56 object-contain drop-shadow-[0_15px_30px_rgba(255,229,0,0.18)] group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <div class="mt-6">
                        <span class="text-brand-yellow font-mono text-xs font-semibold uppercase tracking-wider block mb-1">
                            Identitas Resmi
                        </span>
                        <h3 class="font-display font-bold text-xl text-white uppercase tracking-tight">
                            HMTS FT-UNTAD
                        </h3>
                        <p class="text-zinc-400 font-mono text-xs mt-1">
                            Himpunan Mahasiswa Teknik Sipil · Universitas Tadulako
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- 2. VISI & MISI ORGANISASI -->
    <section class="py-16 px-4 sm:px-6 lg:px-8 border-t border-white/5 bg-[#0D0D12]/70 backdrop-blur-sm relative z-10">
        <div class="max-w-7xl mx-auto">
            <div class="pb-4 mb-8 border-b border-white/10 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Arah Gerak Organisasi</span>
                    <h2 class="font-display font-black text-2xl sm:text-3xl text-white uppercase">Visi & Misi HMTS FT-UNTAD</h2>
                </div>
                <div class="font-mono text-xs text-brand-orange bg-brand-orange/10 px-3 py-1.5 rounded-full border border-brand-orange/20">
                    #WeAreTheChampions
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                <!-- Visi Kami -->
                <div class="lg:col-span-5 rounded-3xl bg-brand-yellow text-black p-6 sm:p-8 flex flex-col justify-between shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="font-mono text-xs font-bold pb-3 mb-4 border-b border-black/20 flex justify-between">
                            <span>VISI KAMI</span>
                            <span>{{ $periodName }}</span>
                        </div>
                        <p class="font-display font-bold text-lg sm:text-xl lg:text-2xl leading-snug uppercase">
                            "Terciptanya HMTS FT-UNTAD yang berdaya saing dikancah nasional maupun internasional."
                        </p>
                    </div>
                    <div class="pt-6 border-t border-black/20 font-mono text-xs font-bold flex items-center justify-between relative z-10">
                        <span>HMTS FT-UNTAD</span>
                        <span>PERIODE {{ $periodName }}</span>
                    </div>
                </div>

                <!-- Misi Kami (5 Poin Sesuai Gambar) -->
                <div class="lg:col-span-7 space-y-3">
                    <div class="clean-card p-4 sm:p-5 border-l-4 border-l-brand-yellow flex items-start gap-4 transition-all duration-200 hover:border-l-brand-orange">
                        <span class="font-mono text-brand-yellow font-bold text-base shrink-0 pt-0.5">1.</span>
                        <p class="text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            Menjadikan HMTS FT-UNTAD sebagai wadah yang menampung dan menyalurkan aspirasi mahasiswa teknik sipil.
                        </p>
                    </div>
                    <div class="clean-card p-4 sm:p-5 border-l-4 border-l-brand-yellow flex items-start gap-4 transition-all duration-200 hover:border-l-brand-orange">
                        <span class="font-mono text-brand-yellow font-bold text-base shrink-0 pt-0.5">2.</span>
                        <p class="text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            Memberi kontribusi nyata pada setiap civitas melalui kegiatan-kegiatan dalam bidang ketekniksipilan.
                        </p>
                    </div>
                    <div class="clean-card p-4 sm:p-5 border-l-4 border-l-brand-yellow flex items-start gap-4 transition-all duration-200 hover:border-l-brand-orange">
                        <span class="font-mono text-brand-yellow font-bold text-base shrink-0 pt-0.5">3.</span>
                        <p class="text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            Menumbuhkan rasa kepekaan HMTS FT-UNTAD terhadap kebijakan dan isu-isu yang berkembang.
                        </p>
                    </div>
                    <div class="clean-card p-4 sm:p-5 border-l-4 border-l-brand-yellow flex items-start gap-4 transition-all duration-200 hover:border-l-brand-orange">
                        <span class="font-mono text-brand-yellow font-bold text-base shrink-0 pt-0.5">4.</span>
                        <p class="text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            Melaksanakan kegiatan sosial dan keilmuan yang berkaitan dengan ketekniksipilan.
                        </p>
                    </div>
                    <div class="clean-card p-4 sm:p-5 border-l-4 border-l-brand-yellow flex items-start gap-4 transition-all duration-200 hover:border-l-brand-orange">
                        <span class="font-mono text-brand-yellow font-bold text-base shrink-0 pt-0.5">5.</span>
                        <p class="text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            Meningkatkan wawasan kelembagaan dan penalaran keilmuan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. MARS HMTS FT-UNTAD -->
    <section class="py-16 px-4 sm:px-6 lg:px-8 border-t border-white/5">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Lagu Perjuangan Organisasi</span>
                <h2 class="font-display font-black text-2xl sm:text-3xl text-white uppercase">Mars HMTS FT-UNTAD</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Video Player YouTube Embedded -->
                <div class="lg:col-span-5 flex flex-col items-center">
                    <div class="w-full aspect-video rounded-3xl overflow-hidden border border-white/10 shadow-2xl bg-black">
                        <iframe class="w-full h-full"
                                src="https://www.youtube.com/embed/6A_Picg1NnE?rel=0"
                                title="Mars HMTS FT-UNTAD"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen>
                        </iframe>
                    </div>

                    <p class="font-serif italic text-xs sm:text-sm text-zinc-400 mt-3 text-center">
                        "Suara Panggilanmu . . . Begitu Mendayu . . ."
                    </p>

                    <div class="w-full mt-3 flex items-center justify-between px-2 font-mono text-xs text-zinc-400">
                        <span>Pencipta: KB HMTS FT-UNTAD</span>
                        <a href="https://www.youtube.com/watch?v=6A_Picg1NnE"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-brand-orange hover:text-brand-orangeHover transition-colors font-semibold">
                            <span>Buka di YouTube ↗</span>
                        </a>
                    </div>
                </div>

                <!-- Lirik Lengkap Mars HMTS (Sesuai Referensi Gambar 2) -->
                <div class="lg:col-span-7">
                    <div class="clean-card p-6 sm:p-8 sm:py-9 text-center space-y-5 rounded-3xl">
                        <!-- Bait 1 -->
                        <div class="space-y-1 text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            <p class="font-medium text-white">Mahasiswa Teknik Sipil</p>
                            <p>Tulang punggung peradaban</p>
                            <p class="font-medium text-white">Mahasiswa Teknik Sipil</p>
                            <p>Paradigma kehidupan</p>
                        </div>

                        <div class="w-10 h-px bg-white/10 mx-auto"></div>

                        <!-- Bait 2 -->
                        <div class="space-y-1 text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            <p>Merubah egois yang membatu</p>
                            <p>Menjadi insan yang membantu</p>
                        </div>

                        <div class="w-10 h-px bg-white/10 mx-auto"></div>

                        <!-- Bait 3 -->
                        <div class="space-y-1 text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            <p>Hidup mahasiswa Teknik Sipil Tadulako</p>
                            <p>Sebagai insan yang berbakti untuk masyarakat</p>
                            <p>Hidup bersama dalam satu emosional</p>
                            <p class="text-brand-yellow font-medium">Solidaritas itu prinsip hidup kami</p>
                            <p>Hidup bersama dalam payung Tadulako</p>
                            <p>Almamaterku itu identitas kami</p>
                        </div>

                        <div class="w-10 h-px bg-white/10 mx-auto"></div>

                        <!-- Bait 4 -->
                        <div class="space-y-1 text-xs sm:text-sm text-zinc-200 leading-relaxed font-sans">
                            <p>Suara panggilanmu begitu mendayu</p>
                            <p>Untuk negeri ini kami menuntut ilmu</p>
                            <p class="text-brand-yellow font-semibold">Ibu pertiwi ini baktiku untukmu (2x)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. STRUKTUR KEPENGURUSAN (BAGAN BERTINGKAT / 3-TIER TREE LAYOUT) -->
    <section class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8 border-t border-white/5 bg-[#0D0D12]/70 backdrop-blur-sm relative z-10">
        <div class="max-w-7xl mx-auto">

            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Bagan Struktur Organisasi</span>
                <h2 class="font-display font-black text-2xl sm:text-3xl text-white uppercase">Kepengurusan HMTS FT-UNTAD</h2>
                <p class="text-xs font-mono text-zinc-400 mt-2">Periode {{ $periodName }} · Dewan Pimpinan Harian & Koordinator Divisi</p>
            </div>

            <!-- TINGKAT 1: KETUA (Puncak Bagan - Ukuran Kartu Sesuai Tingkat BPH Dibawahnya) -->
            <div class="flex flex-col items-center mb-6">
                <div class="clean-card w-full max-w-xs sm:w-[calc(50%-0.625rem)] lg:w-[calc(25%-0.9375rem)] p-4 sm:p-5 relative flex flex-col justify-between text-center items-center border-brand-yellow/60 shadow-2xl shadow-brand-yellow/5 rounded-3xl">
                    <div class="w-full flex flex-col items-center">
                        <!-- Foto Portrait Ketua (Ukuran & Aspek Sama dengan Kartu BPH & Koordinator) -->
                        <div class="relative w-full aspect-[3/4] overflow-hidden rounded-2xl border-2 border-brand-yellow/60 bg-[#14141E] shadow-lg shadow-black/60">
                            <img src="{{ $ketua->photo_url ?? asset('images/members/ketua.svg') }}"
                                 alt="{{ $ketua->name }}"
                                 class="w-full h-full object-cover object-center">
                        </div>
                        <h4 class="font-display font-bold text-lg text-white text-center mt-4 mb-1">{{ $ketua->name }}</h4>
                        <span class="font-mono text-xs text-brand-yellow font-bold uppercase tracking-wider block text-center mb-1">KETUA HIMPUNAN</span>
                        <div class="font-mono text-xs text-zinc-400 text-center">NIM: {{ $ketua->nim }} · Angkatan {{ $ketua->batch }}</div>
                        <p class="text-xs text-zinc-400 mt-2.5 pt-2.5 border-t border-white/10 leading-relaxed font-sans text-center">
                            {{ $ketua->bio }}
                        </p>
                    </div>
                </div>
                <!-- Batang Vertikal Penghubung Tingkat 1 ke Tingkat 2 -->
                <div class="w-0.5 h-8 bg-brand-yellow/50"></div>
            </div>

            <!-- TINGKAT 2: 4 PIMPINAN HARIAN (Ketua 1, Ketua 2, Sekum, Bendum) -->
            <div class="relative mb-8">
                <!-- Garis Horizontal Penghubung Cabang Tingkat 2 (Desktop) -->
                <div class="hidden lg:block absolute top-0 left-[12.5%] right-[12.5%] h-0.5 bg-white/20"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 pt-4">
                    @foreach ($bphList as $bph)
                        <div class="clean-card p-4 sm:p-5 relative flex flex-col justify-between text-center items-center rounded-3xl">
                            <!-- Stem Vertikal Menghubungkan ke Garis Horizontal -->
                            <div class="hidden lg:block absolute -top-4 left-1/2 -translate-x-1/2 w-0.5 h-4 bg-white/20"></div>
                            <div class="w-full flex flex-col items-center">
                                <!-- Foto Portrait BPH -->
                                <div class="relative w-full aspect-[3/4] overflow-hidden rounded-2xl border border-white/15 bg-[#14141E] shadow-md shadow-black/50">
                                    <img src="{{ $bph->photo_url ?? asset('images/members/placeholder.svg') }}"
                                         alt="{{ $bph->name }}"
                                         class="w-full h-full object-cover object-center">
                                </div>
                                <h4 class="font-display font-bold text-lg text-white text-center mt-4 mb-1">{{ $bph->name }}</h4>
                                <span class="font-mono text-xs text-brand-yellow font-semibold uppercase tracking-wider block text-center mb-1">
                                    {{ strtoupper($bph->position) }}
                                </span>
                                <div class="font-mono text-xs text-zinc-400 text-center">NIM: {{ $bph->nim }} · Angkatan {{ $bph->batch }}</div>
                                <p class="text-xs text-zinc-400 mt-2.5 pt-2.5 border-t border-white/5 leading-relaxed font-sans text-center">{{ $bph->bio }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Batang Penghubung ke Koordinator Divisi -->
            <div class="flex flex-col items-center my-6">
                <div class="w-0.5 h-8 bg-white/20"></div>
                <div class="px-5 py-1.5 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-brand-yellow font-semibold tracking-wider uppercase">
                    KOORDINATOR DIVISI
                </div>
                <div class="w-0.5 h-8 bg-white/20"></div>
            </div>

            <!-- TINGKAT 3: KOORDINATOR DIVISI (8 Divisi) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($divisions as $division)
                    @php
                        $coord = $division->coordinator ?? (object)[
                            'name' => 'Koordinator ' . $division->name,
                            'nim' => '-',
                            'batch' => "'23",
                            'photo_url' => asset('images/members/placeholder.svg'),
                        ];
                    @endphp
                    <div class="clean-card p-4 sm:p-5 flex flex-col justify-between rounded-3xl">
                        <div class="flex flex-col items-center">
                            <!-- Foto Portrait Koordinator Divisi -->
                            <div class="relative w-full aspect-[3/4] overflow-hidden rounded-2xl border border-white/10 bg-[#14141E] shadow-md shadow-black/40">
                                <img src="{{ $coord->photo_url ?? asset('images/members/placeholder.svg') }}"
                                     alt="{{ $coord->name }}"
                                     class="w-full h-full object-cover object-center">
                            </div>
                            <h4 class="font-display font-bold text-base sm:text-lg text-white text-center mt-4 mb-1">
                                {{ $coord->name }}
                            </h4>
                            <span class="font-mono text-xs text-brand-yellow font-semibold block mb-1 text-center uppercase tracking-wider">
                                DIVISI {{ strtoupper($division->name) }}
                            </span>
                            <div class="font-mono text-[11px] text-zinc-400 text-center mb-2.5">
                                NIM: {{ $coord->nim }} · Angkatan {{ $coord->batch }}
                            </div>
                            <p class="text-xs text-zinc-400 leading-relaxed font-sans text-center mb-2">
                                {{ $division->description }}
                            </p>
                        </div>
                        <div class="mt-auto pt-3 border-t border-white/5 font-mono text-[11px] text-zinc-400 flex items-center justify-between">
                            <span>Koordinator Divisi</span>
                            <span class="text-brand-orange font-semibold">Aktif</span>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

</x-layouts.public>
