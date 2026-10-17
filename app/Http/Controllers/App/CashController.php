<?php

namespace App\Http\Controllers\App;

use App\Actions\Cash\CreateCashTransactionAction;
use App\Actions\Cash\VoidCashTransactionAction;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Period;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashController extends Controller
{
    public function index(): View
    {
        return view('app.cash.index', [
            'accounts' => CashAccount::where('is_active', true)->orderBy('name')->get(),
            'transactions' => CashTransaction::with(['cashAccount', 'receipts', 'voidedBy'])->latest('transacted_at')->paginate(20),
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $period = Period::current() ?? abort(422, 'Tidak ada periode aktif.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', Rule::unique('cash_accounts')->where('period_id', $period->id)],
        ]);
        try {
            CashAccount::create($data + ['period_id' => $period->id, 'current_balance' => '0.00', 'is_active' => true]);
        } catch (UniqueConstraintViolationException $exception) {
            if (! CashAccount::withoutGlobalScopes()->where('period_id', $period->id)->where('code', $data['code'])->exists()) {
                throw $exception;
            }
            throw ValidationException::withMessages(['code' => 'Kode akun kas sudah digunakan pada periode ini.']);
        }

        return back()->with('status', 'Akun kas berhasil dibuat. Catat saldo awal sebagai transaksi masuk.');
    }

    public function store(Request $request, CreateCashTransactionAction $action): RedirectResponse
    {
        $data = $request->validate([
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'type' => ['required', 'in:masuk,keluar'],
            'amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d{1,13}(\.\d{1,2})?$/'],
            'description' => ['required', 'string', 'max:2000'],
            'transacted_at' => ['required', 'date'],
        ]);

        $action->execute(CashAccount::findOrFail($data['cash_account_id']), $data['type'], (string) $data['amount'], $data['description'], $request->user(), new \DateTimeImmutable($data['transacted_at']));

        return back()->with('status', 'Transaksi kas berhasil dicatat.');
    }

    public function void(Request $request, CashTransaction $transaction, VoidCashTransactionAction $action): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        $action->execute($transaction, $data['reason'], $request->user());

        return back()->with('status', 'Transaksi dibatalkan melalui mekanisme void.');
    }
}
