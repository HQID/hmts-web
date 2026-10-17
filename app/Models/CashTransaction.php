<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[ScopedBy([ActivePeriodScope::class])]
class CashTransaction extends Model
{
    protected $fillable = ['period_id', 'cash_account_id', 'type', 'amount', 'description', 'transacted_at', 'status', 'void_reason', 'created_by_user_id', 'voided_by_user_id', 'voided_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'transacted_at' => 'datetime', 'voided_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (self $transaction) {
            if ($transaction->isDirty(['period_id', 'cash_account_id', 'type', 'amount', 'description', 'transacted_at', 'created_by_user_id'])
                || $transaction->getOriginal('status') !== 'posted'
                || $transaction->status !== 'voided'
                || ! $transaction->void_reason || ! $transaction->voided_by_user_id || ! $transaction->voided_at) {
                throw ValidationException::withMessages(['transaction' => 'Ledger permanen hanya dapat dikoreksi melalui void.']);
            }
        });
        static::deleting(function () {
            throw ValidationException::withMessages(['transaction' => 'Transaksi kas tidak dapat dihapus. Gunakan void.']);
        });
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(TransactionReceipt::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by_user_id');
    }
}
