<x-layouts.app>
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div><p class="font-mono text-xs text-brand-yellow">LEDGER PERMANEN</p><h1 class="mt-2 font-display text-3xl font-bold text-white">Kas & kuitansi</h1></div>
        <a href="{{ route('app.exports.cash') }}" class="rounded-full border border-white/20 px-4 py-2 text-sm">Unduh CSV</a>
    </div>
    @if(session('status'))<p role="status" class="mb-5 rounded-xl border border-brand-yellow/30 bg-brand-yellow/10 p-3 text-sm text-brand-yellow">{{ session('status') }}</p>@endif
    @if($errors->any())<p role="alert" class="mb-5 rounded-xl border border-red-400/30 p-3 text-red-200">{{ $errors->first() }}</p>@endif
    <div class="grid gap-6 xl:grid-cols-[.8fr_1.2fr]">
        <section class="clean-card p-6">
            <details class="mb-6 border-b border-white/10 pb-5" @if($accounts->isEmpty()) open @endif>
                <summary class="cursor-pointer font-semibold text-brand-yellow">Buat akun kas</summary>
                <p class="mt-3 text-xs text-zinc-400">Saldo awal nol. Catat pemasukan awal melalui ledger, bukan mengubah saldo akun.</p>
                <form method="POST" action="{{ route('app.cash.accounts.store') }}" class="mt-4 grid gap-3">
                    @csrf
                    <label class="grid gap-2">Nama akun<input required name="name" maxlength="255" value="{{ old('name') }}" class="rounded-xl bg-[#181822] p-3 text-white"></label>
                    <label class="grid gap-2">Kode akun<input required name="code" maxlength="30" value="{{ old('code') }}" class="rounded-xl bg-[#181822] p-3 text-white"></label>
                    <button class="rounded-full border border-brand-yellow/50 px-4 py-2 text-brand-yellow">Simpan akun</button>
                </form>
            </details>
            <h2 class="font-display text-lg font-bold text-white">Catat transaksi</h2>
            <form method="POST" action="{{ route('app.cash.store') }}" class="mt-5 grid gap-4">
                @csrf
                <label class="grid gap-2">Akun kas<select required name="cash_account_id" class="rounded-xl bg-[#181822] p-3 text-white"><option value="">Pilih akun kas</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old('cash_account_id') == $account->id)>{{ $account->name }} — Rp {{ number_format($account->current_balance, 2, ',', '.') }}</option>@endforeach</select></label>
                <label class="grid gap-2">Jenis<select name="type" class="rounded-xl bg-[#181822] p-3 text-white"><option value="masuk" @selected(old('type') === 'masuk')>Kas masuk</option><option value="keluar" @selected(old('type') === 'keluar')>Kas keluar</option></select></label>
                <label class="grid gap-2">Nominal<input required type="number" min="0.01" step="0.01" name="amount" value="{{ old('amount') }}" class="rounded-xl bg-[#181822] p-3 text-white"></label>
                <label class="grid gap-2">Waktu (WITA)<input required type="datetime-local" name="transacted_at" value="{{ old('transacted_at', now()->format('Y-m-d\TH:i')) }}" class="rounded-xl bg-[#181822] p-3 text-white"></label>
                <label class="grid gap-2">Keterangan<textarea required name="description" maxlength="2000" rows="3" class="rounded-xl bg-[#181822] p-3 text-white">{{ old('description') }}</textarea></label>
                <button class="rounded-full bg-brand-orange px-5 py-3 font-semibold text-white hover:bg-brand-orangeHover">Simpan transaksi</button>
            </form>
        </section>
        <section class="clean-card overflow-hidden">
            <div class="overflow-x-auto"><table class="w-full text-left text-sm">
                <thead class="bg-white/[.03] text-zinc-400"><tr><th class="p-4">Waktu</th><th class="p-4">Keterangan</th><th class="p-4">Nominal</th><th class="p-4">Status & kuitansi</th></tr></thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr class="border-t border-white/10">
                        <td class="p-4 font-mono text-xs">{{ $transaction->transacted_at->format('d M Y H:i') }}</td>
                        <td class="p-4"><p class="text-white">{{ $transaction->description }}</p><p class="text-xs text-zinc-400">{{ $transaction->cashAccount->name }}</p></td>
                        <td class="p-4 font-mono">{{ $transaction->type === 'masuk' ? '+' : '−' }} Rp {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                        <td class="min-w-64 space-y-3 p-4">
                            <p>{{ $transaction->status }}</p>
                            @if($transaction->status === 'voided')
                                <p class="text-xs text-zinc-400">{{ $transaction->void_reason }} — {{ $transaction->voidedBy?->name }} · {{ $transaction->voided_at?->format('d M Y H:i') }}</p>
                            @else
                                @can('void-cash-transaction')
                                    <details><summary class="cursor-pointer text-brand-yellow">Void transaksi</summary><form method="POST" action="{{ route('app.cash.void', $transaction) }}" class="mt-2 grid gap-2">@csrf<label>Alasan pembatalan<textarea required name="reason" minlength="10" maxlength="1000" class="w-full rounded-lg bg-[#181822] p-2"></textarea></label><button class="rounded-full border border-red-400/40 p-2 text-red-200">Batalkan permanen</button></form></details>
                                @endcan
                            @endif
                            @foreach($transaction->receipts as $receipt)
                                <a class="block text-xs text-brand-yellow underline" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'receipts', 'id' => $receipt->id]) }}">{{ $receipt->original_name }}</a>
                            @endforeach
                            <form method="POST" enctype="multipart/form-data" action="{{ route('app.operations.operate', ['receipts', $transaction->id]) }}" class="grid gap-2">@csrf<label class="text-xs">Kuitansi privat<input required type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-1 w-full text-xs"></label><button class="rounded-full border border-white/20 p-2 text-xs">Unggah kuitansi</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-10 text-center text-zinc-400">Belum ada transaksi.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            <div class="p-4">{{ $transactions->links() }}</div>
        </section>
    </div>
</x-layouts.app>
