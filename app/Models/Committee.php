<?php
namespace App\Models;
use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ScopedBy([ActivePeriodScope::class])]
class Committee extends Model { protected $fillable=['period_id','program_id','member_id','name','nim','position','task_status']; public function period(): BelongsTo{return $this->belongsTo(Period::class);} public function program(): BelongsTo{return $this->belongsTo(Program::class);} public function member(): BelongsTo{return $this->belongsTo(Member::class);} }
