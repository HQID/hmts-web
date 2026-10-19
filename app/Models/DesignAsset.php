<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[ScopedBy([ActivePeriodScope::class])]
class DesignAsset extends Model
{
    protected $fillable = ['period_id', 'title', 'category', 'description', 'disk', 'path', 'is_public', 'uploaded_by_user_id'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function getDownloadLabelAttribute(): ?string
    {
        $storage = Storage::disk('public');
        if ($this->disk !== 'public' || ! $this->is_public || ! $storage->exists($this->path)) {
            return null;
        }
        $size = $storage->size($this->path);

        return strtoupper(pathinfo($this->path, PATHINFO_EXTENSION)).' · '.($size < 1048576 ? number_format($size / 1024, 1).' KB' : number_format($size / 1048576, 1).' MB');
    }
}
