<?php
namespace App\Models;
use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ScopedBy([ActivePeriodScope::class])]
class CashTransaction extends Model { protected $fillable=['period_id','cash_account_id','type','amount','description','transacted_at','status','void_reason','created_by_user_id','voided_by_user_id','voided_at']; protected function casts(): array{return ['amount'=>'decimal:2','transacted_at'=>'datetime','voided_at'=>'datetime'];} public function period(): BelongsTo{return $this->belongsTo(Period::class);} public function cashAccount(): BelongsTo{return $this->belongsTo(CashAccount::class);} public function receipts(): HasMany{return $this->hasMany(TransactionReceipt::class);} }
