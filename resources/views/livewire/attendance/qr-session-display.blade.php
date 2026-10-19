<div class="space-y-6">
    <!-- Header: Kembali & Info Sesi -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-5">
        <div class="flex items-center gap-4">
            <a href="{{ route('app.attendance.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-mono text-zinc-300 hover:bg-white/10 hover:text-white transition">
                ← Kembali ke Daftar Sesi
            </a>
            <div>
                <h1 class="font-display text-xl font-bold text-white uppercase tracking-wider">
                    {{ $session?->title ?? 'Layar Presensi QR Dinamis' }}
                </h1>
                <p class="text-xs font-mono text-zinc-400 mt-0.5">
                    @if($session)
                        Lokasi: {{ $session->location ?? 'HMTS Command Center' }} • Dimulai: {{ $session->starts_at?->format('d M Y, H:i') }} WITA
                    @else
                        Sesi tidak aktif
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 font-mono text-xs font-semibold text-emerald-400">
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                LIVE PROJECTOR
            </span>
            <button wire:click="toggleScanner" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 font-mono text-xs text-zinc-300 hover:bg-white/10 hover:text-white transition">
                {{ $showScanner ? 'Tutup Scanner Kamera' : 'Buka Scanner Kamera' }}
            </button>
        </div>
    </div>

    @if ($error)
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-xs font-mono text-red-300">
            ⚠️ {{ $error }}
        </div>
    @endif

    <!-- Scanner Kamera Operator (Optional / Toggleable) -->
    @if ($showScanner)
        <div class="clean-card p-6" x-data="qrCameraScanner()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-sm font-bold text-white uppercase tracking-wider">Scanner Kamera Panitia (html5-qrcode)</h3>
                <span class="text-xs font-mono text-zinc-400">Arahkan kamera ke QR kartu mahasiswa</span>
            </div>

            <div class="grid gap-6 md:grid-cols-2 items-center">
                <div class="relative mx-auto w-full max-w-sm overflow-hidden rounded-2xl border-2 border-dashed border-brand-orange/40 bg-black/60 p-2">
                    <div id="qr-camera-reader" class="w-full aspect-square rounded-xl overflow-hidden bg-black"></div>
                </div>

                <div class="space-y-4 font-mono text-xs">
                    <div>
                        <label class="block text-zinc-300 font-semibold mb-1 uppercase">NIM Mahasiswa</label>
                        <input type="text" wire:model="scanNim" placeholder="F111XXXXX" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                    </div>

                    <div>
                        <label class="block text-zinc-300 font-semibold mb-1 uppercase">Token Hasil Scan / Manual</label>
                        <input type="text" wire:model="scanTokenInput" placeholder="{{ $qrToken ?? '6-digit token' }}" class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                    </div>

                    <button wire:click="submitAttendance" class="w-full bg-brand-orange hover:bg-brand-orangeHover text-white py-2.5 rounded-xl font-bold uppercase tracking-wider transition">
                        Catat Hadir Sekarang ↗
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($scanResult)
        <div class="rounded-2xl border p-4 text-xs font-mono {{ $scanResult['status'] === 'success' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' : 'border-red-500/30 bg-red-500/10 text-red-300' }}">
            {{ $scanResult['status'] === 'success' ? '✓ ' : '✕ ' }}{{ $scanResult['message'] }}
        </div>
    @endif

    <!-- Layout Utama: Proyektor QR (Kiri) + Real-time Attendance List (Kanan) -->
    <div class="grid gap-6 lg:grid-cols-12">
        <!-- Kolom Kiri: Display QR Code Dinamis & Token -->
        <div class="lg:col-span-7 clean-card p-6 sm:p-8 flex flex-col items-center justify-center text-center space-y-6"
             x-data="{
                expires: $wire.entangle('qrExpiresAt'), clock: Date.now(), timer: null,
                get remaining() { return Math.max(0, Math.ceil(((this.expires || 0) * 1000 - this.clock) / 1000)); },
                get progress() { return Math.min(100, this.remaining / 30 * 100); },
                init() { this.timer = setInterval(() => this.clock = Date.now(), 500); },
                destroy() { clearInterval(this.timer); }
             }" wire:poll.5s="refreshQr">

            <div class="space-y-1">
                <span class="font-mono text-xs uppercase tracking-widest text-brand-yellow font-semibold">Dynamic Token Engine</span>
                <h2 class="font-display text-2xl font-bold text-white uppercase">Scan QR Code Presensi</h2>
                <p class="text-xs font-mono text-zinc-400">Arahkan kamera HP ke kode di bawah untuk presensi otomatis</p>
            </div>

            <!-- Kartu QR Code Putih Bersih (High Contrast untuk Proyektor) -->
            <div class="rounded-3xl bg-white p-5 shadow-2xl shadow-black/80 ring-8 ring-white/10 transition-transform duration-300 hover:scale-[1.02]">
                @if ($qrPayload || $qrToken)
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(260)->margin(1)->backgroundColor(255, 255, 255)->color(15, 15, 20)->generate($qrPayload ?? $qrToken) !!}
                @else
                    <div class="w-[260px] h-[260px] flex items-center justify-center text-zinc-400 font-mono text-xs">
                        Memuat QR...
                    </div>
                @endif
            </div>

            <!-- Kode Manual 6-Digit & Countdown Bar -->
            <div class="w-full max-w-sm space-y-3">
                <div class="rounded-2xl border border-white/10 bg-[#12121a] p-4 text-center">
                    <span class="block text-[11px] font-mono uppercase text-zinc-400 mb-1">Atau Masukkan Token 6-Digit di /layanan</span>
                    <div class="font-mono text-3xl sm:text-4xl font-extrabold tracking-widest text-brand-yellow select-all">
                        {{ $qrToken ?? '------' }}
                    </div>
                </div>

                <!-- Progress Bar TTL 30 Detik -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-[11px] font-mono text-zinc-400 px-1">
                        <span>Kadaluarsa dalam:</span>
                        <span class="font-bold text-brand-orange" x-text="remaining + ' detik'"></span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-white/10">
                        <div class="h-full bg-brand-orange transition-all duration-1000 ease-linear rounded-full"
                             :style="'width: ' + progress + '%'"></div>
                    </div>
                </div>

                <button wire:click="generateToken" class="inline-flex items-center gap-2 text-xs font-mono text-zinc-400 hover:text-white transition cursor-pointer mt-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Perbarui Token Sekarang</span>
                </button>
            </div>
        </div>

        <!-- Kolom Kanan: Real-time Live Attendance Feed -->
        <div class="lg:col-span-5 clean-card p-6 flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                    <div>
                        <span class="font-mono text-xs uppercase text-brand-yellow font-semibold block">Live Feed</span>
                        <h3 class="font-display text-lg font-bold text-white uppercase">Daftar Kehadiran</h3>
                    </div>
                    <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-center">
                        <span class="block text-[10px] font-mono text-emerald-300 uppercase">Tercatat</span>
                        <span class="font-mono text-xl font-bold text-emerald-400">{{ $session?->attendances_count ?? $recentAttendances->count() }}</span>
                    </div>
                </div>

                <div class="space-y-2 max-h-[440px] overflow-y-auto pr-1">
                    @forelse ($recentAttendances as $attendance)
                        <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/[0.02] p-3 text-xs font-mono hover:bg-white/[0.05] transition">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-orange/20 text-brand-orange font-bold text-xs">
                                    {{ substr($attendance->member?->name ?? $attendance->nim, 0, 1) }}
                                </span>
                                <div>
                                    <div class="font-bold text-white font-sans text-xs">
                                        {{ $attendance->member?->name ?? 'Mahasiswa Sipil' }}
                                    </div>
                                    <div class="text-[11px] text-zinc-400">
                                        {{ $attendance->nim }}
                                    </div>
                                </div>
                            </div>
                            <span class="text-[11px] text-emerald-400 font-mono">
                                {{ $attendance->attended_at->format('H:i:s') }}
                            </span>
                        </div>
                    @empty
                        <div class="py-12 text-center font-mono text-xs text-zinc-500">
                            Belum ada mahasiswa yang mencatat presensi pada sesi ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-white/10 pt-3 text-[11px] font-mono text-zinc-500 text-center">
                Daftar otomatis diperbarui secara live setiap ada peserta hadir.
            </div>
        </div>
    </div>
</div>

@script
<script>
Alpine.data('qrCameraScanner', () => ({
    scanner: null,
    isScanning: false,

    init() {
        this.startScanner();
    },

    async startScanner() {
        if (typeof Html5Qrcode === 'undefined') {
            return;
        }

        try {
            this.scanner = new Html5Qrcode('qr-camera-reader');
            await this.scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => {
                    this.onScanSuccess(decodedText);
                },
                (error) => {}
            );
            this.isScanning = true;
        } catch (e) {
            console.warn('Camera scanner could not start:', e);
        }
    },

    onScanSuccess(decodedText) {
        // Extract 6-digit token if full URL or standalone
        const match = decodedText.match(/(?:token=|\b)(\d{6})\b/);
        const token = match ? match[1] : decodedText;

        try {
            const url = new URL(decodedText);
            const session = url.searchParams.get('session');
            if (session && session !== String($wire.get('sessionId'))) {
                $wire.set('scanResult', { status: 'error', message: 'QR berasal dari sesi lain.' });
                return;
            }
        } catch {}
        $wire.set('scanTokenInput', token);

        // If NIM is already filled, automatically submit
        if ($wire.get('scanNim')) {
            $wire.submitAttendance(token);
        }
    },

    async destroy() {
        if (this.scanner && this.isScanning) {
            try {
                await this.scanner.stop();
            } catch (e) {}
        }
    }
}));
</script>
@endscript
