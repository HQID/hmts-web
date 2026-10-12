<?php

namespace App\Actions\Aspiration;

use App\Models\Aspiration;
use App\Models\Period;
use Illuminate\Support\Str;

class SubmitAspirationAction
{
    public function execute(Period $period, string $category, string $message): Aspiration
    {
        do {
            $trackingCode = 'ASP-'.now()->format('Y').'-'.Str::upper(Str::random(6));
        } while (Aspiration::withoutGlobalScopes()->where('tracking_code', $trackingCode)->exists());

        return Aspiration::query()->create([
            'period_id' => $period->id,
            'tracking_code' => $trackingCode,
            'category' => $category,
            'message' => $message,
            'status' => 'baru',
        ]);
    }
}
