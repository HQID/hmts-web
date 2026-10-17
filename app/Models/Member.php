<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([ActivePeriodScope::class])]
class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'division_id',
        'name',
        'nim',
        'position',
        'batch',
        'bio',
        'photo_path',
        'sort_order',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function isBph(): bool
    {
        return $this->division?->name === 'BPH' || in_array($this->position, ['Ketua', 'Ketua Himpunan', 'Ketua 1', 'Ketua 2', 'Sekretaris Umum', 'Bendahara Umum'], true);
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path) {
            if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
                return $this->photo_path;
            }
            if (file_exists(public_path($this->photo_path))) {
                return asset($this->photo_path);
            }
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($this->photo_path);
            }
            return asset($this->photo_path);
        }

        return asset('images/members/placeholder.svg');
    }
}
