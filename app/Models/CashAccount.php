<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[ScopedBy([ActivePeriodScope::class])]
class CashAccount extends Model
{
    protected $fillable = ['period_id', 'name', 'code', 'current_balance', 'is_active'];

    protected function casts(): array
    {
        return ['current_balance' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function balanceInCents(): int
    {
        $balance = $this->current_balance;
        [$whole, $fraction] = explode('.', ltrim($balance, '-'));

        return ((int) $whole * 100 + (int) $fraction) * (str_starts_with($balance, '-') ? -1 : 1);
    }

    // Caller must lock this account inside a database transaction.
    public function adjustBalance(int $cents): void
    {
        $balance = $this->balanceInCents() + $cents;
        if (abs($balance) > 999999999999999) {
            throw ValidationException::withMessages(['amount' => 'Saldo melampaui kapasitas akun kas.']);
        }
        $this->update(['current_balance' => ($balance < 0 ? '-' : '').intdiv(abs($balance), 100).'.'.str_pad((string) (abs($balance) % 100), 2, '0', STR_PAD_LEFT)]);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }
}
