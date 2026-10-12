<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([ActivePeriodScope::class])]
class Borrowing extends Model
{
    protected $fillable = ['period_id', 'requester_name', 'requester_nim', 'purpose', 'start_time', 'expected_return_time', 'returned_at', 'status', 'admin_note', 'verified_by_user_id'];

    protected function casts(): array
    {
        return ['start_time' => 'datetime', 'expected_return_time' => 'datetime', 'returned_at' => 'datetime'];
    }

    public function period(): BelongsTo { return $this->belongsTo(Period::class); }
    public function items(): HasMany { return $this->hasMany(BorrowingItem::class); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_user_id'); }
}
