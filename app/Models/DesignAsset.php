<?php
namespace App\Models;
use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ScopedBy([ActivePeriodScope::class])]
class DesignAsset extends Model { protected $fillable=['period_id','title','category','description','disk','path','is_public','uploaded_by_user_id']; protected function casts(): array{return ['is_public'=>'boolean'];} public function period(): BelongsTo{return $this->belongsTo(Period::class);} }
