<?php
namespace App\Actions\Cash;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class VoidCashTransactionAction { public function execute(CashTransaction $transaction,string $reason,User $user): CashTransaction { if(!$user->isSuperAdmin()){throw new AuthorizationException('Hanya superadmin dapat membatalkan transaksi.');} if($transaction->status==='voided'){throw ValidationException::withMessages(['transaction'=>'Transaksi sudah dibatalkan.']);} return DB::transaction(function()use($transaction,$reason,$user){$transaction->update(['status'=>'voided','void_reason'=>$reason,'voided_by_user_id'=>$user->id,'voided_at'=>now()]);$transaction->cashAccount->increment('current_balance',$transaction->type==='masuk'?-1*$transaction->amount:$transaction->amount);return $transaction;});} }
