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
    protected $fillable = ['period_id', 'name', 'code', 'category', 'specification', 'total_qty', 'available_qty', 'condition', 'photo_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function period(): BelongsTo { return $this->belongsTo(Period::class); }
    public function borrowingItems(): HasMany { return $this->hasMany(BorrowingItem::class); }
}
