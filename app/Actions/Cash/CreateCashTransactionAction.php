<?php
namespace App\Actions\Cash;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class CreateCashTransactionAction { public function execute(CashAccount $account,string $type,float $amount,string $description,User $user,\DateTimeInterface $transactedAt): CashTransaction { if(!in_array($type,['masuk','keluar'],true)||$amount<=0){throw ValidationException::withMessages(['amount'=>'Nominal transaksi harus lebih dari nol.']);} if($type==='keluar' && $account->current_balance<$amount){throw ValidationException::withMessages(['amount'=>'Saldo kas tidak mencukupi.']);} return DB::transaction(function()use($account,$type,$amount,$description,$user,$transactedAt){$transaction=CashTransaction::create(['period_id'=>$account->period_id,'cash_account_id'=>$account->id,'type'=>$type,'amount'=>$amount,'description'=>$description,'transacted_at'=>$transactedAt,'status'=>'posted','created_by_user_id'=>$user->id]); $account->increment('current_balance',$type==='masuk'?$amount:-$amount); return $transaction;});} }
