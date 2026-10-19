<?php

namespace App\Models;

use App\Models\Scopes\ActivePeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

#[ScopedBy([ActivePeriodScope::class])]
class MeetingNote extends Model
{
    protected $attributes = ['file_disk' => 'private'];

    protected $fillable = [
        'period_id',
        'program_id',
        'title',
        'held_at',
        'file_path',
        'file_disk',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'held_at' => 'datetime',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path || $this->file_disk !== 'private') {
            return null;
        }

        return URL::temporarySignedRoute('app.meeting-notes.download', now()->addMinutes(15), ['note' => $this->id]);
    }
}
