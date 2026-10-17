<x-layouts.app>
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-mono text-xs text-brand-yellow">LEDGER KEUANGAN ORGANISASI</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">Kas & Kuitansi</h1>
        </div>
        <a href="{{ route('app.exports.cash') }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-sm text-zinc-200 hover:border-brand-yellow hover:text-brand-yellow transition-colors">
            <span>Unduh CSV</span>
            <span>↓</span>
        </a>
    </div>

    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-brand-yellow/30 bg-brand-yellow/10 p-4 text-sm text-brand-yellow flex items-center justify-between">
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-6 rounded-xl border border-red-400/30 bg-red-500/10 p-4 text-sm text-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- BAGIAN 1: KELOLA AKUN KAS (BEDA SENDIRI / TERPISAH) -->
    <section class="mb-8 clean-card p-6 rounded-3xl">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-white/10">
            <div>
                <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Struktur Akun Kas</span>
                <h2 class="font-display font-bold text-xl text-white">Daftar Akun Kas Aktif</h2>
            </div>
            <details class="group">
                <summary class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-orange text-white hover:bg-brand-orangeHover text-xs font-semibold uppercase transition-colors list-none">
                    <span>+ Buat Akun Kas Baru</span>
                </summary>
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                    <div class="clean-card p-6 sm:p-8 rounded-3xl max-w-md w-full relative border-brand-yellow/30 shadow-2xl">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                            <h3 class="font-display font-bold text-lg text-white">Buat Akun Kas Baru</h3>
                            <button type="button" onclick="this.closest('details').removeAttribute('open')" class="text-zinc-400 hover:text-white text-lg">✕</button>
                        </div>
                        <p class="text-xs text-zinc-400 mb-4">Saldo awal kas adalah nol. Catat modal/pemasukan awal melalui ledger transaksi.</p>
                        <form method="POST" action="{{ route('app.cash.accounts.store') }}" class="grid gap-4">
                            @csrf
                            <label class="grid gap-2 text-sm text-zinc-300">
                                <span>Nama Akun Kas</span>
                                <input required name="name" maxlength="255" placeholder="Contoh: Kas Operasional BPH" value="{{ old('name') }}" class="rounded-xl border border-white/10 bg-[#181822] p-3 text-white">
                            </label>
                            <label class="grid gap-2 text-sm text-zinc-300">
                                <span>Kode Akun</span>
                                <input required name="code" maxlength="30" placeholder="Contoh: KAS-BPH-01" value="{{ old('code') }}" class="rounded-xl border border-white/10 bg-[#181822] p-3 text-white uppercase font-mono">
                            </label>
                            <div class="flex items-center justify-end gap-3 mt-2">
                                <button type="button" onclick="this.closest('details').removeAttribute('open')" class="px-4 py-2 text-xs text-zinc-400 hover:text-white">Batal</button>
                                <button type="submit" class="rounded-full bg-brand-yellow px-5 py-2.5 font-bold text-black text-xs uppercase hover:bg-brand-yellowHover shadow-md">Simpan Akun</button>
                            </div>
                        </form>
                    </div>
                </div>
            </details>
        </div>

        <!-- Akun Kas Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            @forelse($accounts as $account)
                <div class="p-4 rounded-2xl bg-[#14141E] border border-white/5 relative flex flex-col justify-between hover:border-brand-yellow/30 transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-xs px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-brand-yellow font-semibold">{{ $account->code }}</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <h4 class="font-display font-bold text-sm text-white mb-1">{{ $account->name }}</h4>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5">
                        <span class="text-[11px] text-zinc-400 block font-mono">Saldo Saat Ini:</span>
                        <p class="font-mono text-lg font-bold text-brand-yellow">
                            Rp {{ number_format($account->current_balance, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-6 text-zinc-400 text-sm">
                    Belum ada akun kas pada periode ini. Silakan buat akun kas baru di atas.
                </div>
            @endforelse
        </div>
    </section>

    <!-- BAGIAN 2: TRANSAKSI (CATAT DI ATAS, RIWAYAT DI BAWAH) -->
    <div class="space-y-8 min-w-0">

        <!-- Form Catat Transaksi Kas (Di Atas) -->
        <section class="clean-card p-6 sm:p-7 rounded-3xl min-w-0 overflow-hidden">
            <div class="mb-5 pb-3 border-b border-white/10 flex items-center justify-between">
                <div>
                    <span class="text-brand-yellow font-mono text-xs font-semibold uppercase block mb-1">Entri Ledger</span>
                    <h2 class="font-display font-bold text-xl text-white">Catat Transaksi Kas</h2>
                </div>
                <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-zinc-300 hidden sm:inline-block">
                    Pencatatan Transaksi Masuk & Keluar
                </span>
            </div>

            <form method="POST" action="{{ route('app.cash.store') }}" class="mt-5 grid gap-5 min-w-0 w-full">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 min-w-0 w-full">
                    <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                        <span>Pilih Akun Kas</span>
                        <select required name="cash_account_id" class="w-full min-w-0 rounded-xl border border-white/10 bg-[#181822] p-3 text-white focus:border-brand-yellow focus:outline-none transition-colors">
                            <option value="">Pilih akun kas sumber / tujuan</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('cash_account_id', $selectedAccountId) == $account->id)>
                                    {{ $account->name }} ({{ $account->code }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                        <span>Jenis Transaksi</span>
                        <select name="type" class="w-full min-w-0 rounded-xl border border-white/10 bg-[#181822] p-3 text-white focus:border-brand-yellow focus:outline-none transition-colors">
                            <option value="masuk" @selected(old('type') === 'masuk')>Kas Masuk (+)</option>
                            <option value="keluar" @selected(old('type') === 'keluar')>Kas Keluar (−)</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                        <span>Nominal (Rp)</span>
                        <input required type="number" min="0.01" step="0.01" name="amount" placeholder="0.00" value="{{ old('amount') }}" class="w-full min-w-0 rounded-xl border border-white/10 bg-[#181822] p-3 text-white font-mono focus:border-brand-yellow focus:outline-none transition-colors">
                    </label>
                </div>

                <!-- PILIHAN TANGGAL & WAKTU (PICKER INTERAKTIF) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 min-w-0 w-full">
                    <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                        <span>Pilih Tanggal</span>
                        <input required type="date" name="transacted_date" value="{{ old('transacted_date', now()->format('Y-m-d')) }}" onclick="this.showPicker()" class="w-full min-w-0 rounded-xl border border-white/10 bg-[#181822] p-3 text-white [color-scheme:dark] cursor-pointer focus:border-brand-yellow focus:outline-none transition-colors">
                    </label>
                    <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                        <span>Pilih Waktu (WITA)</span>
                        <input required type="time" name="transacted_time" value="{{ old('transacted_time', now()->format('H:i')) }}" onclick="this.showPicker()" class="w-full min-w-0 rounded-xl border border-white/10 bg-[#181822] p-3 text-white [color-scheme:dark] cursor-pointer focus:border-brand-yellow focus:outline-none transition-colors">
                    </label>
                </div>

                <label class="flex flex-col gap-2 text-sm text-zinc-300 min-w-0 w-full">
                    <span>Keterangan Transaksi</span>
                    <textarea required name="description" maxlength="2000" rows="2" placeholder="Uraian peruntukan transaksi..." class="w-full min-w-0 rounded-xl border border-white/10 bg-[#181822] p-3 text-white focus:border-brand-yellow focus:outline-none transition-colors">{{ old('description') }}</textarea>
                </label>

                <div class="flex items-center justify-start pt-1">
                    <button class="rounded-full bg-brand-orange px-6 py-3 font-semibold text-white hover:bg-brand-orangeHover shadow-lg transition-colors cursor-pointer">
                        Simpan Transaksi Kas
                    </button>
                </div>
            </form>
        </section>

        <!-- Tabel Informasi Kas & Kuitansi (Di Bawah) -->
        <section class="clean-card overflow-hidden rounded-3xl min-w-0">
            <div class="border-b border-white/10 p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4 bg-white/[0.02]">
                <div>
                    <h2 class="font-display font-bold text-xl text-white">Riwayat Transaksi Kas</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">Ledger permanen transaksi dan arsip kuitansi</p>
                </div>

                <!-- FILTER PILIH AKUN KAS & JENIS -->
                <form method="GET" action="{{ route('app.cash.index') }}" class="flex flex-wrap items-center gap-2.5">
                    <select name="cash_account_id" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-[#181822] px-3 py-2 text-xs text-white [color-scheme:dark] cursor-pointer">
                        <option value="">Semua Akun Kas ({{ $accounts->count() }})</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected($selectedAccountId == $acc->id)>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="type" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-[#181822] px-3 py-2 text-xs text-white [color-scheme:dark] cursor-pointer">
                        <option value="">Semua Jenis</option>
                        <option value="masuk" @selected($selectedType === 'masuk')>Kas Masuk (+)</option>
                        <option value="keluar" @selected($selectedType === 'keluar')>Kas Keluar (−)</option>
                    </select>

                    @if($selectedAccountId || $selectedType)
                        <a href="{{ route('app.cash.index') }}" class="px-2.5 py-2 text-xs text-brand-yellow hover:underline font-mono">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/[.03] text-zinc-400 font-mono text-xs">
                        <tr>
                            <th class="p-4">Waktu</th>
                            <th class="p-4">Akun & Keterangan</th>
                            <th class="p-4">Nominal</th>
                            <th class="p-4">Kuitansi</th>
                            <th class="p-4 text-right">Status / Void</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-white/[0.02] transition-colors {{ $transaction->status === 'voided' ? 'opacity-60 bg-red-950/10' : '' }}">
                                <td class="p-4 font-mono text-xs text-zinc-400 whitespace-nowrap align-top">
                                    {{ $transaction->transacted_at->format('d M Y') }}<br>
                                    <span class="text-zinc-500">{{ $transaction->transacted_at->format('H:i') }} WITA</span>
                                </td>
                                <td class="p-4 align-top">
                                    <span class="inline-block px-2 py-0.5 rounded bg-white/5 border border-white/10 font-mono text-[11px] text-brand-yellow font-semibold mb-1">
                                        {{ $transaction->cashAccount->name }}
                                    </span>
                                    <p class="text-white text-xs sm:text-sm font-sans leading-snug">{{ $transaction->description }}</p>
                                </td>
                                <td class="p-4 font-mono whitespace-nowrap align-top">
                                    @if($transaction->type === 'masuk')
                                        <span class="text-emerald-400 font-bold">+ Rp {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-rose-400 font-bold">− Rp {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="p-4 align-top min-w-44">
                                    <div class="space-y-1.5">
                                        @forelse($transaction->receipts as $receipt)
                                            <a class="inline-flex items-center gap-1.5 text-xs text-brand-yellow hover:underline truncate max-w-[180px]" 
                                               href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'receipts', 'id' => $receipt->id]) }}">
                                                <span>📎</span>
                                                <span class="truncate">{{ $receipt->original_name }}</span>
                                            </a>
                                        @empty
                                            <span class="text-[11px] text-zinc-500 italic block">Belum ada file</span>
                                        @endforelse

                                        <!-- Form Upload Kuitansi Bersih -->
                                        <details class="pt-1">
                                            <summary class="cursor-pointer text-[11px] font-semibold text-brand-yellow hover:underline list-none">
                                                + Unggah Kuitansi
                                            </summary>
                                            <form method="POST" enctype="multipart/form-data" action="{{ route('app.operations.operate', ['receipts', $transaction->id]) }}" class="mt-2 p-2.5 rounded-xl bg-[#14141E] border border-white/10 space-y-2">
                                                @csrf
                                                <input required type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-[11px] text-zinc-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-white/10 file:text-white cursor-pointer">
                                                <button class="w-full py-1 bg-brand-yellow hover:bg-brand-yellowHover text-black text-[11px] font-bold rounded uppercase">
                                                    Unggah
                                                </button>
                                            </form>
                                        </details>
                                    </div>
                                </td>
                                <td class="p-4 text-right align-top whitespace-nowrap">
                                    @if($transaction->status === 'voided')
                                        <span class="inline-block px-2.5 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 font-mono text-[11px] font-semibold">
                                            Dibatalkan (Void)
                                        </span>
                                        <p class="text-[10px] text-zinc-500 mt-1 max-w-[180px] ml-auto text-right">
                                            {{ $transaction->void_reason }} — {{ $transaction->voidedBy?->name }}
                                        </p>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono text-[11px] font-semibold">
                                            Tercatat
                                        </span>
                                        @can('void-cash-transaction')
                                            <details class="mt-2 text-right">
                                                <summary class="cursor-pointer text-[11px] font-semibold text-rose-400 hover:underline list-none">
                                                    Void transaksi
                                                </summary>
                                                <form method="POST" action="{{ route('app.cash.void', $transaction) }}" class="mt-2 p-2.5 rounded-xl bg-[#14141E] border border-red-500/20 text-left space-y-2 max-w-[220px] ml-auto">
                                                    @csrf
                                                    <label class="block text-[11px] text-zinc-300">Alasan pembatalan:</label>
                                                    <textarea required name="reason" minlength="10" maxlength="1000" rows="2" placeholder="Minimal 10 karakter..." class="w-full rounded bg-[#181822] p-2 text-xs text-white border border-white/10"></textarea>
                                                    <button class="w-full rounded-full border border-red-400/50 hover:bg-red-500/20 py-1 text-xs text-red-200 font-semibold">
                                                        Void
                                                    </button>
                                                </form>
                                            </details>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-zinc-400">
                                    Belum ada transaksi kas yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-white/10 bg-white/[0.01]">
                {{ $transactions->links() }}
            </div>
        </section>
    </div>
</x-layouts.app>
