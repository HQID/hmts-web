<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TransactionReceipt extends Model { protected $fillable=['cash_transaction_id','disk','path','original_name']; public function transaction(): BelongsTo{return $this->belongsTo(CashTransaction::class,'cash_transaction_id');} }
