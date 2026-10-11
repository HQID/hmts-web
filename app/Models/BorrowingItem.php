<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowingItem extends Model
{
    protected $fillable = ['borrowing_id', 'inventory_id', 'qty'];

    public function borrowing(): BelongsTo { return $this->belongsTo(Borrowing::class); }
    public function inventory(): BelongsTo { return $this->belongsTo(Inventory::class); }
}
