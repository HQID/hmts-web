<?php

namespace App\Livewire\Attendance;

use App\Actions\Attendance\GenerateDynamicQrTokenAction;
use App\Actions\Attendance\RecordAttendanceAction;
use App\Models\AttendanceSession;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class QrSessionDisplay extends Component
{
    #[Url]
    public ?int $sessionId = null;

    public ?string $qrToken = null;

    public ?string $qrPayload = null;

    public ?int $qrExpiresAt = null;

    public ?array $scanResult = null;

    public ?string $error = null;

    public string $scanNim = '';

    public string $scanTokenInput = '';

    public bool $showScanner = false;

    public function mount(?int $sessionId = null): void
    {
        $this->sessionId = $sessionId ?: (int) request('sessionId');

        if (! $this->sessionId) {
            $this->error = 'ID sesi tidak ditemukan. Silakan buka melalui menu Presensi.';

            return;
        }

        $session = AttendanceSession::find($this->sessionId);
        if (! $session) {
            $this->error = 'Sesi presensi tidak ditemukan.';

            return;
        }

        $this->generateTokenForSession($session);
    }

    #[On('refreshQr')]
    public function refreshQr(): void
    {
        $this->generateToken(false);
    }

    public function generateToken(bool $force = true): void
    {
        if (! $this->sessionId) {
            $this->error = 'ID sesi tidak valid.';

            return;
        }

        $session = AttendanceSession::find($this->sessionId);
        if (! $session) {
            $this->error = 'Sesi presensi tidak ditemukan.';

            return;
        }

        $this->generateTokenForSession($session, $force);
    }

    public function toggleScanner(): void
    {
        $this->showScanner = ! $this->showScanner;
    }

    public function submitAttendance(?string $token = null, ?string $nim = null): void
    {
        $token = $token ?: $this->scanTokenInput ?: $this->qrToken;
        $nim = trim($nim ?: $this->scanNim);

        if (! $this->sessionId) {
            $this->scanResult = ['status' => 'error', 'message' => 'Sesi presensi belum dipilih.'];

            return;
        }

        if (empty($nim)) {
            $this->scanResult = ['status' => 'error', 'message' => 'NIM wajib diisi untuk presensi.'];

            return;
        }

        if (empty($token)) {
            $this->scanResult = ['status' => 'error', 'message' => 'Token QR wajib diisi.'];

            return;
        }

        $session = AttendanceSession::findOrFail($this->sessionId);

        try {
            $action = app(RecordAttendanceAction::class);
            $attendance = $action->execute($session, $nim, $token);

            $this->scanResult = [
                'status' => 'success',
                'message' => "Presensi NIM {$nim} tercatat pada ".$attendance->attended_at->format('H:i:s').' WITA.',
            ];

            $this->scanNim = '';
            $this->scanTokenInput = '';
            $this->dispatch('attendanceRecorded', message: $this->scanResult['message']);
        } catch (ValidationException $e) {
            $this->scanResult = [
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first() ?? 'Presensi gagal.',
            ];
        } catch (\Throwable $e) {
            $this->scanResult = [
                'status' => 'error',
                'message' => 'Gagal mencatat presensi: '.$e->getMessage(),
            ];
        }
    }

    private function generateTokenForSession(AttendanceSession $session, bool $force = false): void
    {
        try {
            $action = app(GenerateDynamicQrTokenAction::class);
            $token = $action->execute($session, $force);

            $this->qrToken = $token;
            $this->qrPayload = url('/layanan?'.http_build_query(['tab' => 'qr', 'session' => $session->id, 'token' => $token]));
            $this->qrExpiresAt = $session->fresh()->token_expires_at->timestamp;
            $this->error = null;
            $this->dispatch('qrRefreshed');
        } catch (ValidationException $e) {
            $this->qrToken = $this->qrPayload = null;
            $this->qrExpiresAt = null;
            $this->error = collect($e->errors())->flatten()->first();
        }
    }

    public function render(): View
    {
        $session = $this->sessionId ? AttendanceSession::withCount('attendances')->find($this->sessionId) : null;
        $recentAttendances = $session
            ? $session->attendances()->with('member')->latest('attended_at')->take(12)->get()
            : collect();

        return view('livewire.attendance.qr-session-display', [
            'session' => $session,
            'recentAttendances' => $recentAttendances,
        ]);
    }
}
