<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceSession;
use App\Models\Period;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GenerateDynamicQrTokenAction
{
    public function execute(AttendanceSession $session, bool $force = false): string
    {
        $periodId = Period::current()?->id;

        return DB::transaction(function () use ($session, $force, $periodId) {
            $fresh = AttendanceSession::withoutGlobalScopes()->where('period_id', $periodId)->lockForUpdate()->findOrFail($session->id);
            if (! $fresh->isOpen()) {
                throw ValidationException::withMessages(['session' => 'Sesi presensi tidak aktif atau di luar jadwal.']);
            }
            $key = 'attendance-token:'.$fresh->id;
            $encrypted = Cache::get($key);
            if (! $force && $encrypted && $fresh->token_expires_at?->isFuture()) {
                try {
                    $token = Crypt::decryptString($encrypted);
                    if (Hash::check($token, $fresh->token_hash)) {
                        return $token;
                    }
                } catch (DecryptException) {
                    Cache::forget($key);
                }
            }
            do {
                $token = (string) random_int(100000, 999999);
            } while ($force && Hash::check($token, $fresh->token_hash));
            $expires = now()->addSeconds(30)->min($fresh->ends_at);
            $fresh->update(['token_hash' => Hash::make($token), 'token_expires_at' => $expires]);
            Cache::put($key, Crypt::encryptString($token), $expires);

            return $token;
        }, 3);
    }
}
