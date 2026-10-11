<?php
namespace App\Models;
use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ScopedBy([ActivePeriodScope::class])]
class Letter extends Model { protected $fillable=['period_id','agenda_number','letter_number','type','subject','sender_recipient','received_or_sent_on','disposition_note','status','file_path','registered_by_user_id']; protected function casts(): array{return ['received_or_sent_on'=>'date'];} public function period(): BelongsTo{return $this->belongsTo(Period::class);} }
