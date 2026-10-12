<?php

namespace App\Http\Controllers\App;

use App\Actions\Cash\CreateCashTransactionAction;
use App\Actions\Cash\VoidCashTransactionAction;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashController extends Controller
{
    public function index(): View
    {
        return view('app.cash.index', [
            'accounts' => CashAccount::where('is_active', true)->orderBy('name')->get(),
            'transactions' => CashTransaction::with('cashAccount')->latest('transacted_at')->paginate(20),
        ]);
    }

    public function store(Request $request, CreateCashTransactionAction $action): RedirectResponse
    {
        $data = $request->validate([
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'type' => ['required', 'in:masuk,keluar'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:2000'],
            'transacted_at' => ['required', 'date'],
        ]);

        $action->execute(CashAccount::findOrFail($data['cash_account_id']), $data['type'], (float) $data['amount'], $data['description'], $request->user(), new \DateTimeImmutable($data['transacted_at']));

        return back()->with('status', 'Transaksi kas berhasil dicatat.');
    }

    public function void(Request $request, CashTransaction $transaction, VoidCashTransactionAction $action): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        $action->execute($transaction, $data['reason'], $request->user());

        return back()->with('status', 'Transaksi dibatalkan melalui mekanisme void.');
    }
}
