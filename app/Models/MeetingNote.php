<?php
namespace App\Models;
use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ScopedBy([ActivePeriodScope::class])]
class MeetingNote extends Model { protected $fillable=['period_id','program_id','title','held_at','location','participants','content','follow_up','created_by_user_id']; protected function casts(): array{return ['held_at'=>'datetime'];} public function period(): BelongsTo{return $this->belongsTo(Period::class);} public function program(): BelongsTo{return $this->belongsTo(Program::class);} }
