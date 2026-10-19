<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([ActivePeriodScope::class])]
class Inventory extends Model
{
    protected $attributes = ['stock_version' => 2];

    protected $fillable = ['period_id', 'name', 'code', 'category', 'specification', 'total_qty', 'available_qty', 'condition', 'photo_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'stock_version' => 'integer'];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function borrowingItems(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function checkedOutQuantity(): int
    {
        return (int) $this->borrowingItems()->whereHas('borrowing', fn ($q) => $q->whereIn('status', ['dipinjam', 'sedang_dipinjam']))->sum('qty');
    }
}
