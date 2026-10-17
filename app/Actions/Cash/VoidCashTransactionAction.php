<?php

namespace App\Actions\Cash;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VoidCashTransactionAction
{
    public function execute(CashTransaction $transaction, string $reason, User $user): CashTransaction
    {
        Gate::forUser($user)->authorize('void-cash-transaction');
        Validator::make(['reason' => trim($reason)], ['reason' => 'required|string|min:10|max:1000'])->validate();

        return DB::transaction(function () use ($transaction, $reason, $user) {
            $transaction = CashTransaction::lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->status !== 'posted') {
                throw ValidationException::withMessages(['transaction' => 'Transaksi sudah dibatalkan.']);
            }
            $account = CashAccount::lockForUpdate()->findOrFail($transaction->cash_account_id);
            [$whole, $fraction] = explode('.', $transaction->amount);
            $cents = (int) $whole * 100 + (int) $fraction;
            $account->adjustBalance($transaction->type === 'masuk' ? -$cents : $cents);
            $transaction->update([
                'status' => 'voided',
                'void_reason' => trim($reason),
                'voided_by_user_id' => $user->id,
                'voided_at' => now(),
            ]);

            return $transaction;
        }, 3);
    }
}
