<?php
namespace App\Models;
use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ScopedBy([ActivePeriodScope::class])]
class Program extends Model { protected $fillable=['period_id','title','category','description','starts_at','ends_at','location','status','tor_path']; protected function casts(): array { return ['starts_at'=>'datetime','ends_at'=>'datetime']; } public function period(): BelongsTo{return $this->belongsTo(Period::class);} public function committees(): HasMany{return $this->hasMany(Committee::class);} }
