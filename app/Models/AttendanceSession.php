<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([ActivePeriodScope::class])]
class AttendanceSession extends Model
{
    protected $fillable = ['period_id', 'title', 'location', 'starts_at', 'ends_at', 'token_hash', 'token_expires_at', 'status', 'created_by_user_id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'token_expires_at' => 'datetime'];
    }

    public function period(): BelongsTo { return $this->belongsTo(Period::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
}
