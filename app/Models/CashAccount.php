<?php
namespace App\Models;
use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ScopedBy([ActivePeriodScope::class])]
class CashAccount extends Model { protected $fillable=['period_id','name','code','current_balance','is_active']; protected function casts(): array{return ['current_balance'=>'decimal:2','is_active'=>'boolean'];} public function period(): BelongsTo{return $this->belongsTo(Period::class);} public function transactions(): HasMany{return $this->hasMany(CashTransaction::class);} }
