<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GenerateDynamicQrTokenAction
{
    public function execute(AttendanceSession $session): string
    {
        if ($session->status !== 'aktif') {
            throw ValidationException::withMessages(['session' => 'Sesi presensi belum aktif.']);
        }

        $token = (string) random_int(100000, 999999);
        $session->forceFill([
            'token_hash' => Hash::make($token),
            'token_expires_at' => now()->addSeconds(30),
        ])->save();

        return $token;
    }
}
