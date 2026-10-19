<?php

namespace App\Actions\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Member;
use App\Models\Period;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RecordAttendanceAction
{
    public function execute(AttendanceSession $session, string $nim, string $token): Attendance
    {
        Validator::make(compact('nim', 'token'), [
            'nim' => 'required|string|min:5|max:30',
            'token' => 'required|string|digits:6',
        ])->validate();

        if (! $session->isOpen() || ! $session->token_expires_at || ! $session->token_expires_at->isFuture() || ! Hash::check($token, $session->token_hash)) {
            throw ValidationException::withMessages(['token' => 'Token presensi tidak valid atau sudah kedaluwarsa.']);
        }

        if ($session->attendances()->where('nim', $nim)->exists()) {
            throw ValidationException::withMessages(['nim' => 'NIM ini sudah tercatat hadir pada sesi tersebut.']);
        }

        $memberId = Member::withoutGlobalScopes()->where('period_id', $session->period_id)->where('nim', $nim)->value('id');

        try {
            return $session->attendances()->create([
                'period_id' => $session->period_id,
                'member_id' => $memberId,
                'nim' => $nim,
                'attended_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            if (! $session->attendances()->where('nim', $nim)->exists()) {
                throw $exception;
            }
            throw ValidationException::withMessages(['nim' => 'NIM ini sudah tercatat hadir pada sesi tersebut.']);
        }
    }
}
