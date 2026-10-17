<?php

namespace App\Actions\Cash;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateCashTransactionAction
{
    public function execute(CashAccount $account, string $type, string|int|float $amount, string $description, User $user, \DateTimeInterface $transactedAt): CashTransaction
    {
        Gate::forUser($user)->authorize('manage-command-center');
        Validator::make(compact('type', 'amount', 'description'), [
            'type' => 'required|in:masuk,keluar',
            'amount' => ['required', 'regex:/^\d{1,13}(\.\d{1,2})?$/', 'numeric', 'min:0.01'],
            'description' => 'required|string|max:2000',
        ])->validate();
        [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '');
        $cents = (int) $whole * 100 + (int) str_pad($fraction, 2, '0');

        return DB::transaction(function () use ($account, $type, $cents, $description, $user, $transactedAt) {
            $account = CashAccount::lockForUpdate()->findOrFail($account->id);
            if (! $account->is_active) {
                throw ValidationException::withMessages(['cash_account_id' => 'Akun kas tidak aktif.']);
            }
            if ($type === 'keluar' && $account->balanceInCents() < $cents) {
                throw ValidationException::withMessages(['amount' => 'Saldo kas tidak mencukupi.']);
            }

            $account->adjustBalance($type === 'masuk' ? $cents : -$cents);

            return CashTransaction::create([
                'period_id' => $account->period_id,
                'cash_account_id' => $account->id,
                'type' => $type,
                'amount' => intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT),
                'description' => $description,
                'transacted_at' => $transactedAt,
                'status' => 'posted',
                'created_by_user_id' => $user->id,
            ]);
        }, 3);
    }
}
