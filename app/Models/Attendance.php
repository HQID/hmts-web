<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([ActivePeriodScope::class])]
class Attendance extends Model
{
    protected $fillable = ['period_id', 'attendance_session_id', 'member_id', 'nim', 'attended_at'];

    protected function casts(): array
    {
        return ['attended_at' => 'datetime'];
    }

    public function period(): BelongsTo { return $this->belongsTo(Period::class); }
    public function session(): BelongsTo { return $this->belongsTo(AttendanceSession::class, 'attendance_session_id'); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
}
