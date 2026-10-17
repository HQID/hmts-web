<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([ActivePeriodScope::class])]
class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'name',
        'full_name',
        'code',
        'description',
        'sort_order',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class)->orderBy('sort_order');
    }

    public function getCoordinatorAttribute(): ?Member
    {
        return $this->members->first();
    }
}
