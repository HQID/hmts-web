<x-layouts.app>
    @php
        $input = 'w-full min-w-0 max-w-full rounded-xl border border-white/10 bg-[#181822] px-3.5 py-2.5 text-white focus:border-brand-yellow focus:outline-none transition-colors';
        $period = $currentPeriod ?? \App\Models\Period::current();
    @endphp

    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-yellow"></span>
                <p class="font-mono text-xs text-brand-yellow font-semibold tracking-wider uppercase">
                    PERIODE AKTIF {{ $period ? ': ' . $period->name : '' }}
                </p>
            </div>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">{{ $definition['title'] }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($module === 'members')
                <a href="{{ route('app.exports.members') }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-zinc-200 hover:border-brand-yellow hover:text-brand-yellow transition-colors">
                    <span>Unduh CSV Pengurus</span>
                    <span>↓</span>
                </a>
            @endif
            @if($module === 'inventory')
                <a href="{{ route('app.borrowings.index') }}" class="inline-flex items-center gap-2 rounded-full border border-brand-yellow/40 bg-brand-yellow/10 px-4 py-2 text-xs font-semibold text-brand-yellow hover:bg-brand-yellow hover:text-black transition-colors">
                    <span>Lihat Verifikasi Peminjaman</span>
                    <span>→</span>
                </a>
            @endif
            @if($module === 'programs')
                <a href="{{ route('app.operations.index', 'committees') }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-zinc-200 hover:border-brand-yellow hover:text-brand-yellow transition-colors">
                    <span>Kelola Panitia</span>
                    <span>→</span>
                </a>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div role="status" class="mb-6 rounded-2xl border border-brand-yellow/30 bg-brand-yellow/10 p-4 text-sm text-brand-yellow flex items-center justify-between">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border border-red-400/30 bg-red-500/10 p-4 text-sm text-red-200">
            <strong class="font-bold block mb-1">Gagal menyimpan data:</strong>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODUL KEPENGURUSAN (MEMBERS)                             --}}
    {{-- ======================================================== --}}
    @if($module === 'members')
        @if(isset($editing))
            {{-- MODE EDIT DATA PENGURUS (DENGAN UPLOAD FOTO & PREVIEW) --}}
            <div class="mb-6">
                <a href="{{ route('app.members.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-brand-yellow hover:underline">
                    <span>←</span>
                    <span>Kembali ke Bagan Kepengurusan</span>
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr] items-start min-w-0">
                <section class="clean-card p-6 sm:p-7 rounded-3xl min-w-0 overflow-hidden">
                    <div class="mb-5 pb-3 border-b border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Formulir Profil Pengurus</span>
                            <h2 class="font-display text-xl font-bold text-white">Sunting Data Pengurus ({{ $editing->position }})</h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300">
                            {{ $editing->division?->name ?? 'BPH' }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('app.members.update', $editing) }}" enctype="multipart/form-data" class="mt-5 grid gap-5 min-w-0 w-full">
                        @csrf
                        @method('PUT')

                        {{-- SEKSI UPLOAD FOTO PENGURUS --}}
                        <div class="rounded-2xl border border-white/10 bg-[#14141E] p-4 sm:p-5 min-w-0">
                            <span class="block font-semibold text-white text-sm mb-3">Foto Resmi Pengurus</span>
                            <div class="flex flex-col sm:flex-row items-center gap-5 min-w-0">
                                <div class="relative w-28 h-36 rounded-xl overflow-hidden border-2 border-brand-yellow/40 bg-[#0A0A0E] flex-shrink-0 shadow-xl">
                                    <img src="{{ $editing->photo_url }}" alt="{{ $editing->name }}" class="w-full h-full object-cover">
                                    <span class="absolute bottom-0 inset-x-0 bg-black/80 backdrop-blur-xs text-[10px] text-center font-mono text-zinc-300 py-1 border-t border-white/10">Foto Saat Ini</span>
                                </div>
                                <div class="flex-1 w-full space-y-2 min-w-0">
                                    <span class="text-xs text-zinc-300 font-medium block">Unggah Foto Baru (Potret)</span>
                                    <input type="file" name="photo" accept="image/*" class="block w-full min-w-0 text-xs text-zinc-300 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-yellow file:text-black hover:file:bg-brand-yellowHover cursor-pointer file:cursor-pointer bg-[#181822] border border-white/10 rounded-xl p-2.5">
                                    <p class="text-[11px] text-zinc-400">Rekomendasi rasio potret 3:4. Format: JPG, PNG, WEBP, SVG (Maksimal 3 MB). Biarkan kosong jika tetap menggunakan foto lama.</p>
                                    <input type="hidden" name="photo_path" value="{{ old('photo_path', $editing->photo_path) }}">
                                    @error('photo')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                                    @error('photo_path')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- NAMA LENGKAP --}}
                        <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                            <span>Nama Lengkap Pengurus</span>
                            <input type="text" name="name" value="{{ old('name', $editing->name) }}" required maxlength="255" class="{{ $input }}">
                            @error('name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                        </label>

                        {{-- NIM & ANGKATAN --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 min-w-0 w-full">
                            <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                                <span>Nomor Induk Mahasiswa (NIM)</span>
                                <input type="text" name="nim" value="{{ old('nim', $editing->nim) }}" maxlength="30" placeholder="Contoh: F11122045" class="{{ $input }} uppercase font-mono">
                                @error('nim')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                            </label>
                            <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                                <span>Angkatan Mahasiswa</span>
                                <input type="text" name="batch" value="{{ old('batch', $editing->batch) }}" maxlength="10" placeholder="Contoh: '22" class="{{ $input }}">
                                @error('batch')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                            </label>
                        </div>

                        {{-- DIVISI & JABATAN (FIXED: TIDAK LAGI BERTUMPUKAN / TERTUTUP) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 min-w-0 w-full">
                            <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                                <span>Divisi Organisasi</span>
                                <select name="division_id" class="{{ $input }} cursor-pointer truncate">
                                    <option value="">Badan Pengurus Harian (BPH) / Non-Divisi</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" @selected(old('division_id', $editing->division_id) == $division->id)>{{ $division->name }}</option>
                                    @endforeach
                                </select>
                                @error('division_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                            </label>
                            <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                                <span>Jabatan Resmi</span>
                                <input type="text" name="position" value="{{ old('position', $editing->position) }}" required maxlength="255" placeholder="Contoh: Koordinator Divisi Ristek" class="{{ $input }}">
                                @error('position')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                            </label>
                        </div>

                        {{-- URUTAN TAMPIL (SORT ORDER) --}}
                        <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                            <span>Urutan Tampil (Sort Order)</span>
                            <input type="number" min="0" max="65535" name="sort_order" value="{{ old('sort_order', $editing->sort_order ?? 0) }}" class="{{ $input }} font-mono">
                            <span class="text-xs text-zinc-500">Angka lebih kecil tampil lebih awal (misal: 1 untuk Ketua, 2 untuk Ketua 1, dst.)</span>
                            @error('sort_order')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                        </label>

                        {{-- BIO / PERAN --}}
                        <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                            <span>Bio Singkat / Tanggung Jawab Utama</span>
                            <textarea name="bio" rows="3" maxlength="255" placeholder="Uraian singkat peran dalam kepengurusan..." class="{{ $input }}">{{ old('bio', $editing->bio) }}</textarea>
                            @error('bio')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                        </label>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" class="rounded-full bg-brand-orange px-6 py-3 font-semibold text-white hover:bg-brand-orangeHover shadow-lg transition-colors">
                                Perbarui Data Pengurus
                            </button>
                            <a href="{{ route('app.members.index') }}" class="rounded-full border border-white/10 px-5 py-3 text-xs text-zinc-300 hover:text-white hover:border-white/30 transition-colors">
                                Batal
                            </a>
                        </div>
                    </form>
                </section>

                {{-- KARTU PREVIEW LIVE PENGURUS --}}
                <aside class="space-y-4 min-w-0">
                    <div class="clean-card p-6 rounded-3xl min-w-0">
                        <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-3">Tampilan Profil Pengurus</span>
                        <div class="p-5 rounded-2xl bg-[#14141E] border border-white/10 flex flex-col items-center text-center">
                            <div class="relative w-36 h-48 rounded-2xl overflow-hidden border-2 border-brand-yellow/30 bg-[#0A0A0E] shadow-2xl mb-4">
                                <img src="{{ $editing->photo_url }}" alt="{{ $editing->name }}" class="w-full h-full object-cover">
                            </div>
                            <span class="px-3 py-1 rounded-full bg-brand-yellow/10 border border-brand-yellow/30 text-xs font-semibold text-brand-yellow mb-2">
                                {{ $editing->position }}
                            </span>
                            <h3 class="font-display font-bold text-lg text-white">{{ $editing->name }}</h3>
                            <p class="font-mono text-xs text-zinc-400 mt-1">
                                {{ $editing->nim ?? 'NIM belum diisi' }} {{ $editing->batch ? '• Angkatan ' . $editing->batch : '' }}
                            </p>
                            <p class="text-xs text-zinc-400 mt-3 italic line-clamp-3">
                                "{{ $editing->bio ?? 'Belum ada bio singkat.' }}"
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        @else
            {{-- MODE BAGAN STRUKTUR KEPENGURUSAN (VISUAL DENGAN FOTO POTRET) --}}
            <div class="space-y-8 min-w-0">
                <!-- Header Info Bagan -->
                <div class="rounded-3xl bg-[#14141E] border border-white/10 p-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-brand-yellow"></span>
                            <span class="font-mono text-xs text-brand-yellow font-semibold uppercase tracking-wider">
                                STRUKTUR KEPENGURUSAN • PERIODE {{ $period?->name ?? 'AKTIF' }}
                            </span>
                        </div>
                        <h2 class="font-display font-bold text-2xl text-white">Struktur Pengurus HMTS FT-UNTAD</h2>
                        <p class="text-xs text-zinc-400 mt-1 max-w-2xl">
                            Struktur kepengurusan periode <strong>{{ $period?->name }}</strong> (Divisi BPH & 8 Koordinator Divisi). Untuk memperbarui profil pengurus atau mengganti foto, silakan gunakan tombol <strong>Sunting</strong> pada kartu masing-masing.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if($period)
                            <!-- Tombol Modal Edit Periode di Info Bagan -->
                            <details class="group relative">
                                <summary class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300 hover:border-brand-yellow hover:text-brand-yellow hover:bg-white/[0.04] transition-colors list-none">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-brand-yellow"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                    <span>Edit Periode ({{ $period->name }})</span>
                                </summary>
                                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                                    <div class="clean-card p-6 sm:p-8 rounded-3xl max-w-md w-full relative border-brand-yellow/40 shadow-2xl text-left min-w-0">
                                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                                            <div>
                                                <span class="text-brand-yellow font-mono text-xs uppercase font-semibold block">Pengaturan Periode</span>
                                                <h3 class="font-display font-bold text-lg text-white">Edit Periode Kepengurusan</h3>
                                            </div>
                                            <button type="button" onclick="this.closest('details').removeAttribute('open')" class="text-zinc-400 hover:text-white text-lg font-bold">✕</button>
                                        </div>
                                        <p class="text-xs text-zinc-400 mb-4">Sesuaikan nama periode aktif serta tahun masa jabatan kepengurusan.</p>
                                        <form method="POST" action="{{ route('app.periods.update', $period) }}" class="grid gap-4 min-w-0 w-full">
                                            @csrf
                                            @method('PUT')
                                            <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                                                <span>Nama Periode</span>
                                                <input required type="text" name="name" value="{{ old('name', $period->name) }}" placeholder="Contoh: 2026/2027" class="{{ $input }}">
                                            </label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 min-w-0 w-full">
                                                <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                                                    <span>Tahun Mulai</span>
                                                    <input required type="number" min="2000" max="65535" name="start_year" value="{{ old('start_year', $period->start_year) }}" class="{{ $input }} font-mono">
                                                </label>
                                                <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                                                    <span>Tahun Selesai</span>
                                                    <input required type="number" min="2000" max="65535" name="end_year" value="{{ old('end_year', $period->end_year) }}" class="{{ $input }} font-mono">
                                                </label>
                                            </div>
                                            <div class="flex items-center justify-end gap-3 mt-3 pt-3 border-t border-white/10">
                                                <button type="button" onclick="this.closest('details').removeAttribute('open')" class="px-4 py-2 text-xs text-zinc-400 hover:text-white">Batal</button>
                                                <button type="submit" class="rounded-full bg-brand-yellow px-5 py-2.5 font-bold text-black text-xs uppercase hover:bg-brand-yellowHover shadow-md">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </details>
                        @endif
                        <span class="px-4 py-2 rounded-full bg-brand-yellow/10 border border-brand-yellow/30 text-xs font-mono text-brand-yellow font-bold">
                            {{ $records->total() }} Pengurus Terdaftar
                        </span>
                    </div>
                </div>

                @php
                    $bphList = $records->filter(fn($m) => $m->isBph() || $m->division?->name === 'BPH');
                    $coordList = $records->reject(fn($m) => $m->isBph() || $m->division?->name === 'BPH');
                    $ketua = $bphList->first(fn($m) => str_contains(strtolower($m->position), 'ketua') && !str_contains(strtolower($m->position), '1') && !str_contains(strtolower($m->position), '2'));
                    $otherBph = $bphList->reject(fn($m) => $m->id === $ketua?->id);
                @endphp

                <!-- 1. BADAN PENGURUS HARIAN (BPH) -->
                @if($bphList->isNotEmpty())
                    <section class="space-y-5">
                        <div class="flex items-center gap-3">
                            <span class="h-px flex-1 bg-white/10"></span>
                            <span class="font-mono text-xs text-brand-yellow font-bold uppercase tracking-widest px-4 py-1.5 rounded-full bg-brand-yellow/10 border border-brand-yellow/30">
                                BADAN PENGURUS HARIAN (BPH)
                            </span>
                            <span class="h-px flex-1 bg-white/10"></span>
                        </div>

                        <!-- KETUA UMUM (FEATURED TOP) -->
                        @if($ketua)
                            <div class="flex justify-center">
                                <div class="w-full max-w-md p-6 rounded-3xl bg-gradient-to-b from-[#1E1E2C] to-[#121218] border-2 border-brand-yellow/40 shadow-2xl relative group hover:border-brand-yellow transition-all">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="relative w-36 h-48 sm:w-40 sm:h-52 rounded-2xl overflow-hidden border-2 border-brand-yellow/50 bg-[#0A0A0E] shadow-xl mb-4">
                                            <img src="{{ $ketua->photo_url }}" alt="{{ $ketua->name }}" class="w-full h-full object-cover">
                                            <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-brand-yellow text-black font-mono text-[10px] font-bold">
                                                BPH
                                            </span>
                                        </div>
                                        <span class="px-3 py-1 rounded-full bg-brand-yellow/20 border border-brand-yellow/40 text-xs font-bold text-brand-yellow uppercase tracking-wider mb-2">
                                            {{ $ketua->position }}
                                        </span>
                                        <h3 class="font-display font-bold text-xl text-white">{{ $ketua->name }}</h3>
                                        <p class="font-mono text-xs text-zinc-300 mt-1">
                                            {{ $ketua->nim }} • Angkatan {{ $ketua->batch }}
                                        </p>
                                        @if($ketua->bio)
                                            <p class="text-xs text-zinc-400 mt-2 italic max-w-xs">{{ $ketua->bio }}</p>
                                        @endif
                                        <a href="{{ route('app.members.edit', $ketua) }}" class="mt-4 inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-brand-yellow text-black hover:bg-brand-yellowHover text-xs font-bold uppercase tracking-wider transition-colors shadow-md">
                                            <span>Sunting Profil & Foto</span>
                                            <span>→</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- KETUA 1, KETUA 2, SEKUM, BENDUM -->
                        @if($otherBph->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                                @foreach($otherBph as $bph)
                                    <div class="p-5 rounded-3xl bg-[#14141E] border border-white/10 hover:border-brand-yellow/40 transition-all flex flex-col justify-between group shadow-lg min-w-0">
                                        <div class="flex flex-col items-center text-center min-w-0">
                                            <div class="relative w-full h-52 rounded-2xl overflow-hidden border border-white/10 bg-[#0A0A0E] mb-4">
                                                <img src="{{ $bph->photo_url }}" alt="{{ $bph->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-white/10 backdrop-blur-xs text-brand-yellow font-mono text-[10px] font-bold border border-white/10">
                                                    BPH
                                                </span>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-full bg-brand-yellow/10 border border-brand-yellow/30 text-xs font-semibold text-brand-yellow mb-2">
                                                {{ $bph->position }}
                                            </span>
                                            <h4 class="font-display font-bold text-base text-white truncate max-w-full">{{ $bph->name }}</h4>
                                            <p class="font-mono text-xs text-zinc-400 mt-0.5">
                                                {{ $bph->nim }} {{ $bph->batch ? '• ' . $bph->batch : '' }}
                                            </p>
                                            @if($bph->bio)
                                                <p class="text-xs text-zinc-400 mt-2 line-clamp-2 italic">{{ $bph->bio }}</p>
                                            @endif
                                        </div>
                                        <div class="mt-4 pt-3 border-t border-white/5">
                                            <a href="{{ route('app.members.edit', $bph) }}" class="inline-flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-xl bg-white/5 hover:bg-brand-yellow hover:text-black border border-white/10 text-xs font-semibold text-zinc-200 transition-colors">
                                                <span>Sunting Profil & Foto</span>
                                                <span>→</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif

                <!-- 2. KOORDINATOR DIVISI PELAKSANA (8 DIVISI) -->
                @if($coordList->isNotEmpty())
                    <section class="space-y-5 pt-4">
                        <div class="flex items-center gap-3">
                            <span class="h-px flex-1 bg-white/10"></span>
                            <span class="font-mono text-xs text-brand-orange font-bold uppercase tracking-widest px-4 py-1.5 rounded-full bg-brand-orange/10 border border-brand-orange/30">
                                KOORDINATOR DIVISI PELAKSANA (8 DIVISI)
                            </span>
                            <span class="h-px flex-1 bg-white/10"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            @foreach($coordList as $coord)
                                <div class="p-5 rounded-3xl bg-[#14141E] border border-white/10 hover:border-brand-orange/50 transition-all flex flex-col justify-between group shadow-lg min-w-0">
                                    <div class="flex flex-col items-center text-center min-w-0">
                                        <div class="relative w-full h-56 rounded-2xl overflow-hidden border border-white/10 bg-[#0A0A0E] mb-4">
                                            <img src="{{ $coord->photo_url }}" alt="{{ $coord->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <span class="absolute top-2 left-2 px-2.5 py-0.5 rounded-full bg-brand-orange/90 text-white font-mono text-[10px] font-bold shadow-md">
                                                {{ $coord->division?->code ?? 'DIVISI' }}
                                            </span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-semibold text-zinc-300 mb-2 truncate max-w-full">
                                            {{ $coord->position }}
                                        </span>
                                        <h4 class="font-display font-bold text-base text-white truncate max-w-full">{{ $coord->name }}</h4>
                                        <p class="font-mono text-xs text-zinc-400 mt-0.5">
                                            {{ $coord->nim }} {{ $coord->batch ? '• ' . $coord->batch : '' }}
                                        </p>
                                        @if($coord->bio)
                                            <p class="text-xs text-zinc-400 mt-2 line-clamp-2 italic">{{ $coord->bio }}</p>
                                        @endif
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-white/5">
                                        <a href="{{ route('app.members.edit', $coord) }}" class="inline-flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-xl bg-white/5 hover:bg-brand-orange hover:text-white border border-white/10 text-xs font-semibold text-zinc-200 transition-colors">
                                            <span>Sunting Profil & Foto</span>
                                            <span>→</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- REKAPITULASI TABEL RINGKAS -->
                <details class="group rounded-3xl border border-white/10 bg-[#14141E] overflow-hidden">
                    <summary class="cursor-pointer p-5 flex items-center justify-between text-xs font-mono text-zinc-400 hover:text-white select-none">
                        <span>[+] Rekapitulasi Data Tabel Pengurus</span>
                        <span class="text-brand-yellow font-sans">Buka Tabel ↓</span>
                    </summary>
                    <div class="border-t border-white/10 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white/[.03] text-zinc-400 font-mono text-xs">
                                <tr>
                                    <th class="px-5 py-3">Foto</th>
                                    <th class="px-5 py-3">Nama</th>
                                    <th class="px-5 py-3">NIM</th>
                                    <th class="px-5 py-3">Jabatan</th>
                                    <th class="px-5 py-3">Divisi</th>
                                    <th class="px-5 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr class="border-t border-white/5 hover:bg-white/[.02]">
                                        <td class="px-5 py-3">
                                            <img src="{{ $record->photo_url }}" alt="{{ $record->name }}" class="w-9 h-11 object-cover rounded-lg border border-white/10">
                                        </td>
                                        <td class="px-5 py-3 font-semibold text-white">{{ $record->name }}</td>
                                        <td class="px-5 py-3 font-mono text-xs text-zinc-300">{{ $record->nim }}</td>
                                        <td class="px-5 py-3 text-zinc-300">{{ $record->position }}</td>
                                        <td class="px-5 py-3">
                                            <span class="px-2 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300">
                                                {{ $record->division?->name ?? 'BPH' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <a href="{{ route('app.members.edit', $record) }}" class="text-xs font-semibold text-brand-yellow hover:underline">
                                                Sunting →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>

                <div class="pt-2">{{ $records->links() }}</div>
            </div>
        @endif

    {{-- ======================================================== --}}
    {{-- MODUL UMUM LAINNYA (PROGRAMS, INVENTORY, DLL)            --}}
    {{-- ======================================================== --}}
    @else
        @php
            $updateRoute = match($module) {
                'programs' => isset($editing) ? route('app.programs.update', $editing) : '',
                'inventory' => isset($editing) ? route('app.inventory.update', $editing) : '',
                'meeting-notes' => isset($editing) ? route('app.meeting-notes.update', $editing) : '',
                default => isset($editing) ? route('app.'.$module.'.update', $editing) : '',
            };
            $formAction = isset($editing) ? $updateRoute : route('app.'.$module.'.store');

            $fieldLabels = [
                'title' => match($module) {
                    'programs' => 'Nama / Judul Program Kerja',
                    'meeting-notes' => 'Agenda / Judul Rapat',
                    'attendance' => 'Nama Sesi Presensi',
                    'assets' => 'Nama Aset Desain',
                    default => 'Judul Kegiatan',
                },
                'name' => match($module) {
                    'inventory' => 'Nama Alat / Aset Keteknikan',
                    default => 'Nama Lengkap',
                },
                'code' => 'Kode Inventaris Alat',
                'category' => match($module) {
                    'programs' => 'Kategori Program Kerja',
                    'inventory' => 'Kategori Alat / Laboratorium',
                    default => 'Kategori',
                },
                'specification' => 'Spesifikasi & Kelengkapan Alat',
                'total_qty' => 'Jumlah Total Unit Alat',
                'available_qty' => 'Jumlah Alat Tersedia',
                'condition' => 'Kondisi Fisik Alat',
                'is_active' => 'Status Ketersediaan / Siap Dipinjam',
                'starts_at' => match($module) {
                    'programs' => 'Tanggal & Waktu Mulai',
                    'attendance' => 'Waktu Mulai Presensi',
                    default => 'Waktu Mulai',
                },
                'ends_at' => match($module) {
                    'programs' => 'Tanggal & Waktu Selesai',
                    'attendance' => 'Waktu Selesai Presensi',
                    default => 'Waktu Selesai',
                },
                'location' => 'Lokasi / Tempat Pelaksanaan',
                'status' => 'Status Pelaksanaan',
                'program_id' => 'Program Kerja Terkait (Opsional)',
                'held_at' => 'Tanggal & Waktu Pelaksanaan Rapat',
                'file' => match($module) {
                    'meeting-notes' => 'Unggah Berkas Notulensi',
                    'letters' => 'Unggah Berkas Surat',
                    'assets' => 'Unggah Berkas Aset',
                    default => 'Unggah Berkas Dokumen',
                },
                'agenda_number' => 'Nomor Agenda Surat',
                'letter_number' => 'Nomor Surat Resmi',
                'type' => 'Jenis Surat',
                'subject' => 'Perihal / Pokok Surat',
                'sender_recipient' => 'Asal Pengirim / Tujuan Penerima',
                'received_or_sent_on' => 'Tanggal Surat Masuk / Keluar',
                'disposition_note' => 'Catatan / Instruksi Disposisi',
                'description' => 'Deskripsi / Uraian Rencana Kegiatan',
                'disk' => 'Lokasi Penyimpanan Berkas',
                'is_public' => 'Dapat Diakses & Diunduh Publik',
            ];

            $optionLabels = [
                'internal' => 'Internal Organisasi',
                'competition' => 'Kompetisi & Lomba',
                'workshop' => 'Workshop & Pelatihan',
                'community' => 'Pengabdian Masyarakat',
                'seminar' => 'Seminar Akademik',
                'baik' => 'Baik / Siap Pakai',
                'rusak_ringan' => 'Rusak Ringan (Perlu Perawatan)',
                'rusak_berat' => 'Rusak Berat (Tidak Dapat Dipinjam)',
                'rencana' => 'Rencana Kegiatan',
                'aktif' => 'Sedang Berlangsung / Aktif',
                'selesai' => 'Selesai Dilaksanakan',
                'dibatalkan' => 'Dibatalkan',
                'draft' => 'Draf / Belum Dimulai',
                'terdaftar' => 'Terdaftar di Agenda',
                'didisposisikan' => 'Telah Didisposisikan',
                'masuk' => 'Surat Masuk',
                'keluar' => 'Surat Keluar',
            ];
        @endphp

        <div class="space-y-6 min-w-0">
            <section class="clean-card p-6 min-w-0 overflow-hidden">
                <div class="mb-4 pb-3 border-b border-white/10 flex items-center justify-between">
                    <div>
                        <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">
                            {{ isset($editing) ? 'Perbarui Data' : 'Entri Data Baru' }}
                        </span>
                        <h2 class="font-display text-lg font-bold text-white">
                            {{ isset($editing) ? 'Sunting Data ' . $definition['title'] : 'Tambah ' . $definition['title'] . ' Baru' }}
                        </h2>
                    </div>
                    @isset($editing)
                        <span class="px-3 py-1 rounded-full bg-brand-yellow/10 border border-brand-yellow/30 text-xs font-mono text-brand-yellow">Mode Edit</span>
                    @endisset
                </div>

                <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="mt-5 grid gap-4 min-w-0 w-full">
                    @csrf
                    @isset($editing) @method('PUT') @endisset
                    @foreach($definition['rules'] as $field => $rules)
                        @php
                            $label = $fieldLabels[$field] ?? str($field)->replace('_', ' ')->headline();
                            $value = old($field, isset($editing) ? data_get($editing, $field) : ($field === 'sort_order' ? 0 : ''));
                            $required = in_array('required', explode('|', $rules), true);
                            preg_match('/(?:^|\|)max:(\d+)/', $rules, $maxMatch);
                            $max = $maxMatch[1] ?? null;
                            $options = match($field) {
                                'status' => ['rencana', 'aktif', 'selesai', 'dibatalkan'],
                                'condition' => ['baik', 'rusak_ringan', 'rusak_berat'],
                                'category' => $module === 'programs' ? ['internal', 'competition', 'workshop', 'community', 'seminar'] : [],
                                default => [],
                            };
                        @endphp
                        <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                            <span>{{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif</span>
                        @if(in_array($field, ['division_id', 'program_id']))
                            <select name="{{ $field }}" class="{{ $input }}">
                                <option value="">Pilih bila diperlukan</option>
                                @foreach($field === 'division_id' ? $divisions : $programs as $option)
                                    <option value="{{ $option->id }}" @selected($value == $option->id)>{{ $option->name ?? $option->title }}</option>
                                @endforeach
                            </select>
                        @elseif($options)
                            <select name="{{ $field }}" class="{{ $input }}">
                                @foreach($options as $option)
                                    <option value="{{ $option }}" @selected($value === $option)>{{ $optionLabels[$option] ?? str($option)->headline() }}</option>
                                @endforeach
                            </select>
                        @elseif($field === 'file')
                            <input type="file" name="file" @required($required && !isset($editing)) class="{{ $input }} cursor-pointer file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-brand-yellow file:text-black file:font-semibold">
                            <span class="text-xs text-zinc-400">
                                @if($module === 'meeting-notes') Format: PDF, Word, Excel, PPT, ZIP, Gambar. Maksimal ukuran 10 MB.
                                @elseif($module === 'assets') Format: Bebas (PDF, PNG, JPG, WEBP, ZIP). Maksimal ukuran 20 MB.
                                @elseif($module === 'letters') Format: PDF, DOC, DOCX, Gambar. Maksimal ukuran 10 MB.
                                @else Maksimal ukuran berkas 10 MB.
                                @endif
                            </span>
                            @if(isset($editing) && !empty($editing->file_path))
                                <div class="mt-1 flex items-center gap-2 text-xs font-mono text-brand-yellow">
                                    <span class="px-2 py-0.5 rounded bg-brand-yellow/10 border border-brand-yellow/30">Berkas tersimpan: {{ basename($editing->file_path) }}</span>
                                    @if($module === 'meeting-notes')
                                        <a href="{{ route('app.meeting-notes.download', $editing) }}" class="underline font-bold text-white hover:text-brand-yellow">Unduh 📥</a>
                                    @endif
                                </div>
                            @endif
                        @elseif(in_array($field, ['description', 'content', 'participants', 'follow_up', 'disposition_note', 'specification', 'bio']))
                            <textarea name="{{ $field }}" rows="3" @required($required) @if($max) maxlength="{{ $max }}" @endif placeholder="Tuliskan keterangan lengkap..." class="{{ $input }}">{{ $value }}</textarea>
                        @elseif($field === 'is_active')
                            <div class="flex items-center gap-3 pt-1">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="is_active_input" name="is_active" value="1" @checked(old('is_active', isset($editing) ? $editing->is_active : true)) class="w-4 h-4 rounded border-white/20 bg-[#181822] text-brand-yellow focus:ring-brand-yellow cursor-pointer">
                                <label for="is_active_input" class="text-xs text-zinc-300 cursor-pointer">Centang jika alat aktif dan tersedia untuk dipinjam mahasiswa</label>
                            </div>
                        @elseif(in_array($field, ['starts_at', 'ends_at', 'held_at']))
                            @php
                                $dateVal = '';
                                $timeVal = '';
                                if ($value instanceof \DateTimeInterface) {
                                    $dateVal = $value->format('Y-m-d');
                                    $timeVal = $value->format('H:i');
                                } elseif (is_string($value) && !empty($value)) {
                                    $raw = str_replace('T', ' ', $value);
                                    $parts = explode(' ', $raw);
                                    $dateVal = $parts[0] ?? '';
                                    $timeVal = isset($parts[1]) ? substr($parts[1], 0, 5) : '';
                                }
                                if (empty($dateVal) && $required) {
                                    $dateVal = now()->format('Y-m-d');
                                }
                                if (empty($timeVal) && $required) {
                                    $timeVal = $field === 'held_at' ? '09:00' : '08:00';
                                }
                            @endphp
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0 w-full" data-datetime-picker-group data-type="datetime-local">
                                <div class="flex flex-col gap-1.5 min-w-0 w-full">
                                    <span class="text-xs font-mono text-zinc-400 flex items-center gap-1.5">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-brand-yellow"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        Pilih Tanggal
                                    </span>
                                    <input type="date" 
                                           name="{{ $field }}_date" 
                                           value="{{ old($field . '_date', $dateVal) }}" 
                                           @required($required)
                                           onclick="this.showPicker()"
                                           class="{{ $input }} [color-scheme:dark] cursor-pointer focus:border-brand-yellow transition-colors dt-date-input">
                                </div>
                                <div class="flex flex-col gap-1.5 min-w-0 w-full">
                                    <span class="text-xs font-mono text-zinc-400 flex items-center gap-1.5">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-brand-yellow"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        Pilih Waktu (WITA)
                                    </span>
                                    <input type="time" 
                                           name="{{ $field }}_time" 
                                           value="{{ old($field . '_time', $timeVal) }}" 
                                           @required($required)
                                           onclick="this.showPicker()"
                                           class="{{ $input }} [color-scheme:dark] cursor-pointer focus:border-brand-yellow transition-colors dt-time-input">
                                </div>
                            </div>
                            <input type="hidden" name="{{ $field }}" value="{{ old($field, ($dateVal && $timeVal) ? $dateVal . ' ' . $timeVal : '') }}" class="dt-hidden-input">
                        @else
                            @php
                                $type = in_array($field, ['total_qty', 'available_qty', 'sort_order']) ? 'number' : 'text';
                            @endphp
                            <input type="{{ $type }}" name="{{ $field }}" value="{{ $value }}" @required($required) @if($type === 'number') min="0" max="{{ $max }}" step="1" @elseif($type === 'text' && $max) maxlength="{{ $max }}" @endif class="{{ $input }}">
                            @if($field === 'sort_order')<span class="text-xs text-zinc-500">Opsional; kosong otomatis urutan 0.</span>@endif
                            @if($field === 'available_qty')<span class="text-xs text-zinc-400">Jumlah unit yang saat ini siap digunakan/dipinjam.</span>@endif
                        @endif
                        </label>
                        @error($field)<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                    @endforeach
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="rounded-full bg-brand-orange px-6 py-3 font-semibold text-white hover:bg-brand-orangeHover shadow-lg transition-colors cursor-pointer">
                            {{ isset($editing) ? 'Perbarui Data ' . $definition['title'] : 'Simpan Data ' . $definition['title'] }}
                        </button>
                        @isset($editing)
                            <a href="{{ route('app.'.$module.'.index') }}" class="rounded-full border border-white/10 px-5 py-3 text-xs text-zinc-300 hover:text-white hover:border-white/30 transition-colors">Batal</a>
                        @endisset
                    </div>
                </form>

                <script>
                    document.querySelectorAll('[data-datetime-picker-group]').forEach(group => {
                        const dateInput = group.querySelector('.dt-date-input');
                        const timeInput = group.querySelector('.dt-time-input');
                        const hiddenInput = group.nextElementSibling;
                        const update = () => {
                            if (dateInput.value) {
                                hiddenInput.value = dateInput.value + ' ' + (timeInput.value || '00:00');
                            } else {
                                hiddenInput.value = '';
                            }
                        };
                        dateInput.addEventListener('change', update);
                        timeInput.addEventListener('change', update);
                    });
                </script>
            </section>

            <section class="clean-card overflow-hidden min-w-0">
                <div class="border-b border-white/10 p-6 flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-lg font-bold text-white">Daftar {{ $definition['title'] }}</h2>
                        <p class="text-xs text-zinc-400 mt-0.5">Catatan data pada periode aktif</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300">
                        {{ $records->total() }} Data
                    </span>
                </div>
                <div class="overflow-x-auto"><table class="w-full text-left text-sm">
                    <thead class="bg-white/[.03] text-zinc-400 font-mono text-xs">
                        <tr>
                            @foreach($definition['columns'] as $colKey => $label)
                                <th class="px-5 py-3">{{ $label }}</th>
                            @endforeach
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr class="border-t border-white/10 {{ (isset($editing) && $editing->id === $record->id) ? 'bg-brand-yellow/10' : 'hover:bg-white/[.02]' }}">
                                @foreach(array_keys($definition['columns']) as $column)
                                    <td class="px-5 py-4">
                                        @if($column === 'file_path')
                                            @if($record->file_path)
                                                <div class="flex items-center gap-2.5">
                                                    @if($record->file_url)
                                                        <a href="{{ $record->file_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs text-white hover:text-brand-yellow font-mono underline">
                                                            <span>Lihat Berkas</span>
                                                            <span>↗</span>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('app.meeting-notes.download', $record) }}" class="inline-flex items-center gap-1 text-xs text-brand-yellow hover:underline font-mono">
                                                        <span>Unduh</span>
                                                        <span>📥</span>
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-xs text-zinc-500 font-mono">Tanpa Berkas</span>
                                            @endif
                                        @elseif($column === 'category' && $module === 'programs')
                                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300">
                                                {{ $optionLabels[$record->category] ?? str($record->category)->headline() }}
                                            </span>
                                        @elseif($column === 'condition' && $module === 'inventory')
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-mono {{ $record->condition === 'baik' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                                                {{ $optionLabels[$record->condition] ?? str($record->condition)->headline() }}
                                            </span>
                                        @elseif(data_get($record, $column) instanceof \DateTimeInterface)
                                            <span class="font-mono text-xs text-zinc-300">{{ data_get($record, $column)->format('d M Y H:i') }}</span>
                                        @else
                                            {{ data_get($record, $column) }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-5 py-4 text-right">
                                    @if($module === 'programs')
                                        <a href="{{ route('app.programs.edit', $record) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-yellow hover:underline">
                                            <span>Sunting</span>
                                            <span>→</span>
                                        </a>
                                    @elseif($module === 'inventory')
                                        <a href="{{ route('app.inventory.edit', $record) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-yellow hover:underline">
                                            <span>Sunting</span>
                                            <span>→</span>
                                        </a>
                                    @elseif($module === 'meeting-notes')
                                        <div class="flex items-center justify-end gap-3">
                                            @if($record->file_path)
                                                <a href="{{ route('app.meeting-notes.download', $record) }}" class="text-xs text-emerald-400 hover:underline">Unduh 📥</a>
                                            @endif
                                            <a href="{{ route('app.meeting-notes.edit', $record) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-yellow hover:underline">
                                                <span>Sunting</span>
                                                <span>→</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($definition['columns']) + 1 }}" class="px-5 py-10 text-center text-zinc-400">
                                    Belum ada data pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
                <div class="p-5">{{ $records->links() }}</div>
            </section>
        </div>
    @endif
</x-layouts.app>
