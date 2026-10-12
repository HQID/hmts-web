<div>
    <!-- HEADER LAYANAN MAHASISWA -->
    <section class="pt-10 pb-12 px-4 sm:px-6 lg:px-8 border-b border-white/10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-yellow"></span>
                    <span>One-Stop Student Hub</span>
                </div>
                <h1 class="font-display font-black text-3xl sm:text-5xl text-white uppercase tracking-tight mb-2">
                    Pusat <span class="text-brand-yellow">Layanan Mahasiswa</span>
                </h1>
                <p class="text-zinc-300 text-sm max-w-xl font-sans">
                    Fasilitas utilitas digital mandiri: peminjaman alat lab, absensi acara via QR, kanal aspirasi anonim, dan repositori arsip.
                </p>
            </div>

            <!-- Tab Pills Navigasi Cepat -->
            <div class="flex flex-wrap items-center gap-2 p-1.5 rounded-2xl bg-[#121217] border border-white/10 font-mono text-xs">
                <button type="button" 
                        wire:click="setTab('borrow')" 
                        class="px-4 py-2 rounded-xl transition-all cursor-pointer {{ $activeTab === 'borrow' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Pinjam Alat Lab
                </button>
                <button type="button" 
                        wire:click="setTab('qr')" 
                        class="px-4 py-2 rounded-xl transition-all cursor-pointer {{ $activeTab === 'qr' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Presensi QR
                </button>
                <button type="button" 
                        wire:click="setTab('aspiration')" 
                        class="px-4 py-2 rounded-xl transition-all cursor-pointer {{ $activeTab === 'aspiration' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Kotak Aspirasi
                </button>
                <button type="button" 
                        wire:click="setTab('assets')" 
                        class="px-4 py-2 rounded-xl transition-all cursor-pointer {{ $activeTab === 'assets' ? 'bg-brand-orange text-white font-bold shadow-md' : 'text-zinc-400 hover:text-white' }}">
                    Bank Aset
                </button>
            </div>
        </div>
    </section>

    <!-- CONTENT VIEW BERDASARKAN ACTIVE TAB -->
    <section class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- ============================================================ -->
            <!-- TAB 1: PEMINJAMAN ALAT LAB -->
            <!-- ============================================================ -->
            @if ($activeTab === 'borrow')
                <div class="space-y-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-white/10">
                        <div>
                            <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block">Inventaris Alat Ukur & Laboratorium</span>
                            <h3 class="font-display font-bold text-2xl text-white uppercase">Katalog Alat Tersedia</h3>
                        </div>
                        <span class="text-xs font-mono text-zinc-400">Verifikasi peminjaman maksimal 1x24 jam kerja</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($tools as $tool)
                            <div class="clean-card p-5 flex flex-col justify-between group">
                                <div>
                                    <div class="h-40 rounded-xl overflow-hidden bg-black/40 mb-4 border border-white/5 relative">
                                        <div class="flex h-full items-center justify-center font-mono text-xs text-zinc-500">{{ $tool->code }}</div>
                                        <span class="absolute top-2 right-2 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-mono">
                                            {{ $tool->available_qty }} Unit Tersedia
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-mono text-brand-yellow uppercase block">{{ $tool->category }}</span>
                                    <h4 class="font-display font-bold text-base text-white mt-1 mb-2">{{ $tool->name }}</h4>
                                    <p class="text-xs text-zinc-400 font-sans leading-relaxed">{{ $tool->specification }}</p>
                                </div>

                                <div class="mt-5 pt-3 border-t border-white/10 flex items-center justify-between">
                                    <span class="text-xs font-mono text-zinc-400">Status: <strong class="text-emerald-400">{{ $tool->condition === 'baik' ? 'Tersedia' : 'Perlu Pemeriksaan' }}</strong></span>
                                    <button type="button" 
                                            wire:click="selectInventory({{ $tool->id }})"
                                            class="text-xs font-mono text-brand-orange hover:underline font-bold cursor-pointer">
                                        Pilih Alat →
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Formulir Peminjaman Inline -->
                    <div class="clean-card p-6 sm:p-8 border border-white/15 mt-8">
                        <div class="pb-4 mb-6 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="font-mono text-xs text-brand-yellow font-semibold uppercase block mb-1">Pengajuan Langsung</span>
                                <h4 class="font-display font-bold text-xl text-white uppercase">Formulir Reservasi Alat</h4>
                            </div>
                            <span class="text-xs font-mono text-zinc-400">Instrumen terpilih: <span class="text-brand-yellow font-bold">{{ $borrowTool }}</span></span>
                        </div>

                        @if ($borrowSuccess)
                            <div class="p-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-center font-mono text-xs space-y-2">
                                <div class="text-lg font-bold">✓ Pengajuan Peminjaman Berhasil Dikirim!</div>
                                <p class="text-zinc-300 font-sans text-xs">Pengurus Divisi Ristek/Inventaris akan memverifikasi jadwal Anda. Bukti peminjaman akan dikonfirmasi melalui kontak WhatsApp yang terdaftar.</p>
                                <button type="button" wire:click="resetBorrowForm" class="mt-3 px-4 py-2 rounded-full bg-emerald-500 text-black font-bold uppercase cursor-pointer">
                                    Ajukan Alat Lain
                                </button>
                            </div>
                        @else
                            <form wire:submit.prevent="submitBorrowForm" class="space-y-4 font-mono text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-zinc-300 font-semibold mb-1 uppercase">Nama Lengkap Mahasiswa</label>
                                        <input type="text" wire:model="borrowName" required placeholder="Contoh: Muhammad Raihan" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                                        @error('borrowName') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-zinc-300 font-semibold mb-1 uppercase">NIM Mahasiswa</label>
                                        <input type="text" wire:model="borrowNim" required placeholder="F111XXXXX" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                                        @error('borrowNim') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-zinc-300 font-semibold mb-1 uppercase">Tanggal Pinjam</label>
                                        <input type="date" wire:model="borrowStartDate" required class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                                        @error('borrowStartDate') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-zinc-300 font-semibold mb-1 uppercase">Tanggal Pengembalian</label>
                                        <input type="date" wire:model="borrowEndDate" required class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                                        @error('borrowEndDate') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-zinc-300 font-semibold mb-1 uppercase">Keperluan Peminjaman</label>
                                    <textarea wire:model="borrowPurpose" rows="2" required placeholder="Praktikum Ilmu Ukur Tanah / Tugas Akhir Pemetaan Topografi..." class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2 text-white outline-none font-sans text-xs"></textarea>
                                    @error('borrowPurpose') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                </div>

                                <div class="pt-2 flex justify-end">
                                    <button type="submit" class="bg-brand-orange hover:bg-brand-orangeHover text-white px-6 py-3 rounded-full font-mono text-xs font-bold uppercase shadow-lg shadow-brand-orange/20 cursor-pointer">
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
                <div class="max-w-2xl mx-auto clean-card p-6 sm:p-10 text-center">
                    <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Presensi Mandiri QR Code</span>
                    <h3 class="font-display font-bold text-2xl text-white uppercase mb-6">Scan QR & Token Sesi Acara</h3>

                    <!-- Viewfinder Simulator -->
                    <div class="w-64 h-64 mx-auto rounded-3xl border-2 border-dashed border-brand-yellow/60 bg-black/60 relative overflow-hidden flex flex-col items-center justify-center p-4 mb-6 shadow-2xl">
                        <div class="absolute inset-x-0 h-0.5 bg-brand-yellow animate-pulse top-1/2"></div>
                        <svg class="w-16 h-16 text-zinc-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"></path></svg>
                        <span class="text-xs font-mono text-zinc-400">Arahkan kamera ke layar QR proyektor</span>
                    </div>

                    @if ($qrAttendanceStatus === 'success')
                        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono text-xs mb-6">
                            ✓ Presensi Anda Telah Tercatat Berhasil pada Sesi Aktif!
                        </div>
                    @endif

                    <!-- Form Token Input Manual -->
                    <form wire:submit.prevent="submitAttendance" class="space-y-4 max-w-sm mx-auto font-mono text-xs text-left">
                        <div>
                            <label class="block text-zinc-300 font-semibold mb-1 uppercase">NIM Mahasiswa</label>
                            <input type="text" wire:model="qrNim" required placeholder="F111XXXXX" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                            @error('qrNim') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-zinc-300 font-semibold mb-1 uppercase">Token Sesi 6-Digit</label>
                            <input type="text" wire:model="qrToken" required maxlength="6" placeholder="Contoh: 829140" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none tracking-widest text-center text-sm font-bold">
                            @error('qrToken') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full bg-brand-orange hover:bg-brand-orangeHover text-white py-3 rounded-full font-bold uppercase tracking-wider transition-all shadow-md shadow-brand-orange/20 cursor-pointer">
                            Validasi Presensi Sekarang ↗
                        </button>
                    </form>
                </div>
            @endif

            <!-- ============================================================ -->
            <!-- TAB 3: KOTAK ASPIRASI MAHASISWA -->
            <!-- ============================================================ -->
            @if ($activeTab === 'aspiration')
                <div class="max-w-3xl mx-auto space-y-8">
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
                                <div>
                                    <label class="block text-zinc-300 font-semibold mb-1 uppercase">Kategori Aspirasi</label>
                                    <select wire:model="aspirationCategory" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl text-white px-4 py-2.5 outline-none font-sans text-xs">
                                        <option value="Fasilitas & Lab">Fasilitas & Kelengkapan Lab</option>
                                        <option value="Akademik & Perkuliahan">Akademik & Perkuliahan</option>
                                        <option value="Kegiatan Kemahasiswaan">Kegiatan Kemahasiswaan & Proker</option>
                                        <option value="Lainnya">Lain-lain</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-zinc-300 font-semibold mb-1 uppercase">Isi Masukan / Aduan</label>
                                    <textarea wire:model="aspirationMessage" rows="4" required placeholder="Tuliskan aspirasi, kritik konstruktif, atau masukan perbaikan sarana secara objektif..." class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none font-sans text-xs leading-relaxed"></textarea>
                                    @error('aspirationMessage') <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                </div>

                                <button type="submit" class="bg-brand-orange hover:bg-brand-orangeHover text-white px-6 py-3 rounded-full font-bold uppercase tracking-wider transition-all shadow-md shadow-brand-orange/20 cursor-pointer">
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
                            <input type="text" wire:model="trackTicketInput" placeholder="Masukkan Kode Tiket (misal: ASP-2026-XXXXX)" class="flex-1 bg-[#181822] border border-white/10 rounded-xl px-4 py-2.5 text-white outline-none">
                            <button type="button" wire:click="searchTicket" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold cursor-pointer">
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
                <div class="space-y-6">
                    <div class="pb-4 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block">Open Repository</span>
                            <h3 class="font-display font-bold text-2xl text-white uppercase">Bank Aset & Dokumen Resmi</h3>
                        </div>
                        <span class="text-xs font-mono text-zinc-400">Arsip terverifikasi Departemen Teknik Sipil UNTAD</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div class="clean-card p-5 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-mono text-brand-yellow uppercase block mb-1">Identitas Visual</span>
                                <h4 class="font-display font-bold text-base text-white mb-2">Master Logo Kit & Kop Surat</h4>
                                <p class="text-xs text-zinc-400 font-sans leading-relaxed">Paket format PNG resolusi tinggi, SVG vektor resmi, dan template kop persuratan.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs flex items-center justify-between">
                                <span class="text-zinc-500">ZIP · 12 MB</span>
                                <a href="{{ asset('images/hmts.png') }}" download class="text-brand-yellow hover:underline font-semibold">Unduh ↗</a>
                            </div>
                        </div>

                        <div class="clean-card p-5 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-mono text-brand-yellow uppercase block mb-1">CAD Blueprint</span>
                                <h4 class="font-display font-bold text-base text-white mb-2">Blueprint CAD Jembatan Busur 60m</h4>
                                <p class="text-xs text-zinc-400 font-sans leading-relaxed">Berkas gambar kerja AutoCAD DWG jembatan rangka baja bentang 60 meter siap cetak.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs flex items-center justify-between">
                                <span class="text-zinc-500">DWG · 28 MB</span>
                                <a href="#" onclick="alert('File CAD siap diunduh.'); return false;" class="text-brand-yellow hover:underline font-semibold">Unduh ↗</a>
                            </div>
                        </div>

                        <div class="clean-card p-5 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-mono text-brand-yellow uppercase block mb-1">Publikasi Ilmiah</span>
                                <h4 class="font-display font-bold text-base text-white mb-2">Paper Jurnal Inovasi Beton Serat</h4>
                                <p class="text-xs text-zinc-400 font-sans leading-relaxed">Riset mahasiswa dan dosen terkait pemanfaatan serat bambu dalam daktilitas balok.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs flex items-center justify-between">
                                <span class="text-zinc-500">PDF · 4.2 MB</span>
                                <a href="#" onclick="alert('File Paper Jurnal siap diunduh.'); return false;" class="text-brand-yellow hover:underline font-semibold">Unduh ↗</a>
                            </div>
                        </div>

                        <div class="clean-card p-5 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-mono text-brand-yellow uppercase block mb-1">Pedoman Praktikum</span>
                                <h4 class="font-display font-bold text-base text-white mb-2">Modul Praktikum Survei & Pemetaan</h4>
                                <p class="text-xs text-zinc-400 font-sans leading-relaxed">Buku panduan lapangan praktikum Theodolite, Total Station, dan pengolahan data kontur.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/5 font-mono text-xs flex items-center justify-between">
                                <span class="text-zinc-500">PDF · 15 MB</span>
                                <a href="#" onclick="alert('File Modul Praktikum siap diunduh.'); return false;" class="text-brand-yellow hover:underline font-semibold">Unduh ↗</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </section>
</div>
