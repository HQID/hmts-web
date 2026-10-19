<div>
    <!-- HEADER LAYANAN MAHASISWA (KOMPAK UNTUK VIEWPORT LAPTOP 150%) -->
    <section class="pt-4 sm:pt-6 pb-4 sm:pb-5 px-4 sm:px-6 lg:px-8 border-b border-white/10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end justify-between gap-4 animate-hero-slide animate-delay-75">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-yellow"></span>
                    <span>One-Stop Student Hub</span>
                </div>
                <h1 class="font-display font-black text-2xl sm:text-3xl lg:text-[38px] leading-tight text-white uppercase tracking-tight mb-1">
                    Pusat <span class="text-brand-yellow">Layanan Mahasiswa</span>
                </h1>
                <p class="text-zinc-300 text-xs sm:text-sm max-w-xl font-sans">
                    Fasilitas utilitas digital mandiri: peminjaman alat lab, absensi acara via QR, kanal aspirasi anonim, dan repositori arsip.
                </p>
            </div>

            <!-- Tab Pills Navigasi Cepat -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-xl bg-[#121217] border border-white/10 font-mono text-xs">
                <button type="button" 
                        wire:click="setTab('borrow')" 
                        class="px-3.5 py-1.5 rounded-lg transition-all active:scale-[0.98] cursor-pointer {{ $activeTab === 'borrow' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Pinjam Alat Lab
                </button>
                <button type="button" 
                        wire:click="setTab('qr')" 
                        class="px-3.5 py-1.5 rounded-lg transition-all active:scale-[0.98] cursor-pointer {{ $activeTab === 'qr' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Presensi QR
                </button>
                <button type="button" 
                        wire:click="setTab('aspiration')" 
                        class="px-3.5 py-1.5 rounded-lg transition-all active:scale-[0.98] cursor-pointer {{ $activeTab === 'aspiration' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Kotak Aspirasi
                </button>
                <button type="button" 
                        wire:click="setTab('assets')" 
                        class="px-3.5 py-1.5 rounded-lg transition-all active:scale-[0.98] cursor-pointer {{ $activeTab === 'assets' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Bank Aset
                </button>
            </div>
        </div>
    </section>

    <!-- CONTENT VIEW BERDASARKAN ACTIVE TAB -->
    <section class="pt-5 sm:pt-6 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- ============================================================ -->
            <!-- TAB 1: PEMINJAMAN ALAT LAB -->
            <!-- ============================================================ -->
            @if ($activeTab === 'borrow')
                <div class="space-y-6 tab-content-enter">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 pb-3 border-b border-white/10">
                        <div>
                            <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block">Inventaris Alat Ukur & Laboratorium</span>
                            <h3 class="font-display font-bold text-lg sm:text-xl text-white uppercase">Katalog Alat Tersedia</h3>
                        </div>
                        <span class="text-xs font-mono text-zinc-400">Verifikasi peminjaman maksimal 1x24 jam kerja</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @forelse ($tools as $index => $tool)
                            @php($hasPhoto = $tool->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($tool->photo_path))
                            @php($canRequest = $tool->condition === 'baik' && $tool->total_qty > 0 && $tool->stock_version === 2)
                            <div class="clean-card p-4 flex flex-col justify-between group motion-reveal delay-{{ (($index % 4) + 1) * 75 }}">
                                <div>
                                    <div class="{{ $hasPhoto ? 'h-32 sm:h-36' : 'min-h-14' }} rounded-xl overflow-hidden bg-black/40 mb-3 border border-white/5 relative">
                                        @if($hasPhoto)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($tool->photo_path) }}" alt="{{ $tool->name }}" class="h-full w-full object-cover" loading="lazy">
                                        @else<div class="flex min-h-14 items-center px-3 font-mono text-xs text-zinc-500">{{ $tool->code }}</div>@endif
                                        <span class="absolute top-2 right-2 px-2.5 py-0.5 rounded-full border text-[10px] font-mono {{ $canRequest && $tool->available_qty > 0 ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-zinc-800 text-zinc-300 border-white/10' }}">
                                            {{ $tool->available_qty }} Unit Tersedia
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-mono text-brand-yellow uppercase block">{{ $tool->category }}</span>
                                    <h4 class="font-display font-bold text-sm sm:text-base text-white mt-1 mb-1.5">{{ $tool->name }}</h4>
                                    <p class="text-xs text-zinc-400 font-sans leading-relaxed line-clamp-2">{{ $tool->specification }}</p>
                                </div>

                                <div class="mt-5 pt-3 border-t border-white/10 flex items-center justify-between">
                                    <span class="text-xs font-mono text-zinc-400">Status: <strong class="{{ $canRequest && $tool->available_qty > 0 ? 'text-emerald-400' : 'text-zinc-300' }}">{{ $tool->stock_version !== 2 ? 'Stok sedang diperiksa' : ($tool->condition !== 'baik' ? 'Perlu Pemeriksaan' : ($tool->available_qty > 0 ? 'Tersedia fisik' : 'Stok fisik habis')) }}</strong></span>
                                    <button type="button" 
                                            wire:click="selectInventory({{ $tool->id }})"
                                            @disabled(!$canRequest)
                                            class="text-xs font-mono text-brand-orange hover:underline font-bold cursor-pointer">
                                        Pilih Alat →
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-sm text-zinc-400 sm:col-span-2 lg:col-span-4">Belum ada alat yang dapat diajukan pada periode ini.</p>
                        @endforelse
                    </div>

                    <!-- Formulir Peminjaman Inline -->
                    <div id="borrow-form" tabindex="-1" class="clean-card scroll-mt-24 p-6 sm:p-8 border border-white/15 mt-8">
                        <div class="pb-4 mb-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="font-mono text-xs text-brand-yellow font-semibold uppercase block mb-1">Pengajuan Langsung</span>
                                <h4 class="font-display font-bold text-xl text-white uppercase">Formulir Reservasi Alat</h4>
                            </div>
                            <span class="text-xs font-mono text-zinc-400">Instrumen terpilih: <span class="text-brand-yellow font-bold">{{ $borrowTool }}</span></span>
                        </div>

                        @error('borrowInventoryId') <p role="alert" class="mb-4 text-xs text-red-400">{{ $message }}</p> @enderror
                        @if ($borrowSuccess)
                            <div class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-center font-mono text-xs space-y-2">
                                <div class="text-lg font-bold">✓ Pengajuan Peminjaman Berhasil Dikirim!</div>
                                <p class="text-zinc-300 font-sans text-xs">Pengajuan menunggu persetujuan pengurus. Hubungi sekretariat untuk konfirmasi. Persetujuan jadwal belum berarti alat sudah diserahkan.</p>
                                <button type="button" wire:click="resetBorrowForm" class="mt-3 px-4 py-2 rounded-full bg-emerald-500 text-black font-bold uppercase cursor-pointer">
                                    Ajukan Alat Lain
                                </button>
                            </div>
                        @else
                            <form wire:submit.prevent="submitBorrowForm" class="space-y-4 font-mono text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="borrowName" class="block text-zinc-300 font-semibold mb-1 uppercase">Nama Lengkap Mahasiswa</label>
                                        <input type="text" id="borrowName" wire:model="borrowName" required maxlength="255" placeholder="Contoh: Muhammad Raihan" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                                        @error('borrowName') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="borrowNim" class="block text-zinc-300 font-semibold mb-1 uppercase">NIM Mahasiswa</label>
                                        <input type="text" id="borrowNim" wire:model="borrowNim" required maxlength="30" placeholder="F111XXXXX" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                                        @error('borrowNim') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="borrowStartDate" class="block text-zinc-300 font-semibold mb-1 uppercase">Tanggal Pinjam</label>
                                        <input type="date" id="borrowStartDate" wire:model="borrowStartDate" required onclick="this.showPicker()" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none [color-scheme:dark] cursor-pointer">
                                        @error('borrowStartDate') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="borrowEndDate" class="block text-zinc-300 font-semibold mb-1 uppercase">Tanggal Pengembalian</label>
                                        <input type="date" id="borrowEndDate" wire:model="borrowEndDate" required onclick="this.showPicker()" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none [color-scheme:dark] cursor-pointer">
                                        @error('borrowEndDate') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="borrowPurpose" class="block text-zinc-300 font-semibold mb-1 uppercase">Keperluan Peminjaman</label>
                                    <textarea id="borrowPurpose" wire:model="borrowPurpose" rows="2" required maxlength="5000" placeholder="Praktikum Ilmu Ukur Tanah / Tugas Akhir Pemetaan Topografi..." class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2 text-white outline-none font-sans text-xs"></textarea>
                                    @error('borrowPurpose') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                </div>

                                <div class="pt-2 flex justify-end">
                                    <button wire:loading.attr="disabled" wire:target="submitBorrowForm" type="submit" class="bg-brand-orange hover:bg-brand-orangeHover text-white px-6 py-3 rounded-full font-mono text-xs font-bold uppercase shadow-lg shadow-brand-orange/20 cursor-pointer">
                                        Kirim Pengajuan Reservasi ↗
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- ============================================================ -->
            <!-- TAB 2: PRESENSI QR -->
            <!-- ============================================================ -->
            @if ($activeTab === 'qr')
                <div class="max-w-2xl mx-auto clean-card p-6 sm:p-10 text-center tab-content-enter">
                    <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Presensi Mandiri QR Code</span>
                    <h3 class="font-display font-bold text-2xl text-white uppercase mb-6">Scan QR & Token Sesi Acara</h3>

                    <div x-data="hmtsStudentScanner($wire)" class="mb-6 space-y-3">
                        <div id="student-qr-reader" x-show="scanning" class="mx-auto max-w-sm overflow-hidden rounded-2xl"></div>
                        <button type="button" @click="scanning ? stop() : start()" class="rounded-full border border-brand-yellow/50 px-5 py-3 text-sm text-brand-yellow" x-text="scanning ? 'Hentikan kamera' : 'Mulai kamera QR'"></button>
                        <p role="status" class="text-sm text-zinc-300" x-text="message">Gunakan kamera atau isi sesi dan token secara manual.</p>
                    </div>

                    @if ($qrAttendanceStatus === 'success')
                        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono text-xs mb-6">
                            ✓ Presensi Anda Telah Tercatat Berhasil pada Sesi Aktif!
                        </div>
                    @endif

                    <!-- Form Token Input Manual -->
                    <form wire:submit.prevent="submitAttendance" class="space-y-4 max-w-sm mx-auto font-mono text-xs text-left">
                        <label class="block text-zinc-300">Pilih sesi presensi
                            <select wire:model="qrSessionId" class="mt-2 w-full rounded-xl border border-white/10 bg-[#181822] p-3 text-white">
                                <option value="">Pilih sesi yang Anda ikuti</option>
                                @foreach ($attendanceSessions as $attendanceSession)
                                    <option value="{{ $attendanceSession->id }}">{{ $attendanceSession->title }}</option>
                                @endforeach
                            </select>
                        </label>
                        @error('qrSessionId') <p role="alert" class="text-red-400">Pilih sesi presensi yang benar.</p> @enderror
                        <div>
                            <label for="qrNim" class="block text-zinc-300 font-semibold mb-1 uppercase">NIM Mahasiswa</label>
                            <input type="text" id="qrNim" wire:model="qrNim" required maxlength="30" placeholder="F111XXXXX" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                            @error('qrNim') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="qrToken" class="block text-zinc-300 font-semibold mb-1 uppercase">Token Sesi 6-Digit</label>
                            <input type="text" id="qrToken" wire:model="qrToken" required maxlength="6" placeholder="Contoh: 829140" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none tracking-widest text-center text-sm font-bold">
                            @error('qrToken') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <button wire:loading.attr="disabled" wire:target="submitAttendance" type="submit" class="w-full bg-brand-orange hover:bg-brand-orangeHover text-white py-3 rounded-full font-bold uppercase tracking-wider transition-all shadow-md shadow-brand-orange/20 cursor-pointer active:scale-[0.98]">
                            Validasi Presensi Sekarang ↗
                        </button>
                    </form>
                </div>
            @endif

            <!-- ============================================================ -->
            <!-- TAB 3: KOTAK ASPIRASI MAHASISWA -->
            <!-- ============================================================ -->
            @if ($activeTab === 'aspiration')
                <div class="max-w-3xl mx-auto space-y-8 tab-content-enter">
                    <!-- Penjelasan Privasi -->
                    <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs font-mono text-amber-300 flex items-start gap-3">
                        <span class="text-lg">🛡️</span>
                        <div>
                            <strong class="block mb-0.5 text-white">Privasi Anonimitas Dijamin:</strong>
                            Sistem tidak menyimpan alamat IP, identitas akun, maupun riwayat peramban Anda. Simpan nomor tiket pelacakan Anda secara mandiri untuk melihat tanggapan pengurus.
                        </div>
                    </div>

                    <!-- Kirim Aspirasi -->
                    <div class="clean-card p-6 sm:p-8">
                        <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Saluran Suara Mahasiswa</span>
                        <h3 class="font-display font-bold text-xl text-white uppercase mb-4">Kirimkan Aspirasi & Masukan</h3>

                        @if ($aspirationSubmitted)
                            <div class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-center font-mono text-xs space-y-3">
                                <div class="text-lg font-bold">✓ Aspirasi Anda Berhasil Terkirim Secara Anonim!</div>
                                <p class="text-zinc-300 font-sans">Gunakan kode tiket pelacak acak berikut untuk memantau status tindak lanjut pengurus harian:</p>
                                <div class="inline-block px-5 py-2.5 rounded-xl bg-black/60 border border-emerald-500/50 text-brand-yellow text-sm font-bold tracking-wider">
                                    {{ $aspirationTrackingCode }}
                                </div>
                            </div>
                        @else
                            <form wire:submit.prevent="submitAspiration" class="space-y-4 font-mono text-xs">
                                <div class="hidden" aria-hidden="true" style="display: none;">
                                    <input type="text" wire:model="aspirationHoneypot" tabindex="-1" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-zinc-300 font-semibold mb-1 uppercase">Kategori Aspirasi</label>
                                    <select aria-label="Kategori aspirasi" wire:model="aspirationCategory" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl text-white px-4 py-2.5 outline-none font-sans text-xs">
                                        <option value="Fasilitas & Lab">Fasilitas & Kelengkapan Lab</option>
                                        <option value="Akademik & Perkuliahan">Akademik & Perkuliahan</option>
                                        <option value="Kegiatan Kemahasiswaan">Kegiatan Kemahasiswaan & Proker</option>
                                        <option value="Lainnya">Lain-lain</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="aspirationMessage" class="block text-zinc-300 font-semibold mb-1 uppercase">Isi Masukan / Aduan</label>
                                    <textarea id="aspirationMessage" wire:model="aspirationMessage" rows="4" required placeholder="Tuliskan aspirasi, kritik konstruktif, atau masukan perbaikan sarana secara objektif..." class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none font-sans text-xs leading-relaxed"></textarea>
                                    @error('aspirationMessage') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                </div>

                                <button wire:loading.attr="disabled" wire:target="submitAspiration" type="submit" class="bg-brand-orange hover:bg-brand-orangeHover text-white px-6 py-3 rounded-full font-bold uppercase tracking-wider transition-all shadow-md shadow-brand-orange/20 cursor-pointer active:scale-[0.98]">
                                    Kirimkan Aspirasi (Anonim) ↗
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Pelacak Tiket Aspirasi -->
                    <div class="clean-card p-6 sm:p-8">
                        <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Cek Respon Pengurus</span>
                        <h4 class="font-display font-bold text-lg text-white uppercase mb-3">Lacak Status Tiket Aspirasi</h4>

                        <div class="flex flex-col sm:flex-row gap-2 font-mono text-xs">
                            <input type="text" aria-label="Kode tiket aspirasi" wire:model="trackTicketInput" placeholder="Masukkan Kode Tiket (misal: ASP-2026-XXXXX)" class="flex-1 bg-[#181822] border border-white/10 rounded-xl px-4 py-2.5 text-white outline-none">
                            <button type="button" wire:click="searchTicket" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold cursor-pointer active:scale-[0.98]">
                                Lacak Tiket →
                            </button>
                        </div>

                        @if ($trackTicketResult)
                            <div class="mt-4 p-4 rounded-xl bg-black/40 border border-white/10 font-mono text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-brand-yellow font-bold">{{ $trackTicketResult['code'] }}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-brand-orange/20 text-brand-orange text-[10px]">{{ $trackTicketResult['status'] }}</span>
                                </div>
                                <p class="text-zinc-300 font-sans text-xs pt-1 border-t border-white/5">{{ $trackTicketResult['response'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- ============================================================ -->
            <!-- TAB 4: BANK ASET & REPOSITORI -->
            <!-- ============================================================ -->
            @if ($activeTab === 'assets')
                <div class="space-y-6 tab-content-enter">
                    <div class="pb-4 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block">Open Repository</span>
                            <h3 class="font-display font-bold text-2xl text-white uppercase">Bank Aset & Dokumen Resmi</h3>
                        </div>
                        <span class="text-xs font-mono text-zinc-400">Arsip terverifikasi Departemen Teknik Sipil UNTAD</span>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($assets as $asset)
                            <article class="clean-card flex flex-col justify-between p-6" wire:key="asset-{{ $asset->id }}">
                                <div>
                                    <p class="mb-2 text-xs font-mono text-brand-yellow">{{ $asset->category }}</p>
                                    <h4 class="text-lg font-bold text-white">{{ $asset->title }}</h4>
                                    <p class="mt-3 text-sm text-zinc-300">{{ $asset->description }}</p>
                                </div>
                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-white/10 pt-4 text-xs">
                                    @if ($asset->download_label)
                                        <span class="font-mono text-zinc-400">{{ $asset->download_label }}</span>
                                        <a href="{{ route('assets.download', $asset) }}" class="rounded-full bg-brand-orange px-4 py-2 font-bold text-white">Unduh berkas</a>
                                    @else
                                        <p class="text-zinc-400">Berkas belum tersedia. Silakan hubungi pengurus.</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="clean-card p-8 text-zinc-300 sm:col-span-2 lg:col-span-3">Belum ada aset publik pada periode ini.</p>
                        @endforelse
                    </div>
                </div>
            @endif

        </div>
    </section>
</div>
