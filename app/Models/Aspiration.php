<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([ActivePeriodScope::class])]
class Aspiration extends Model
{
    protected $fillable = ['period_id', 'tracking_code', 'category', 'message', 'status', 'response', 'responded_by_user_id', 'responded_at'];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function period(): BelongsTo { return $this->belongsTo(Period::class); }
    public function respondedBy(): BelongsTo { return $this->belongsTo(User::class, 'responded_by_user_id'); }
}
