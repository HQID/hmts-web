<?php

namespace App\Actions\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RecordAttendanceAction
{
    public function execute(AttendanceSession $session, string $nim, string $token): Attendance
    {
        if ($session->status !== 'aktif' || $session->token_expires_at->isPast() || ! Hash::check($token, $session->token_hash)) {
            throw ValidationException::withMessages(['token' => 'Token presensi tidak valid atau sudah kedaluwarsa.']);
        }

        if ($session->attendances()->where('nim', $nim)->exists()) {
            throw ValidationException::withMessages(['nim' => 'NIM ini sudah tercatat hadir pada sesi tersebut.']);
        }

        $memberId = Member::query()->where('nim', $nim)->value('id');

        return $session->attendances()->create([
            'period_id' => $session->period_id,
            'member_id' => $memberId,
            'nim' => $nim,
            'attended_at' => now(),
        ]);
    }
}
