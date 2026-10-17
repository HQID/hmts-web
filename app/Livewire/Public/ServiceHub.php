<?php

namespace App\Livewire\Public;

use App\Actions\Aspiration\SubmitAspirationAction;
use App\Actions\Attendance\RecordAttendanceAction;
use App\Actions\Inventory\CreateBorrowingRequestAction;
use App\Models\Aspiration;
use App\Models\AttendanceSession;
use App\Models\DesignAsset;
use App\Models\Inventory;
use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.public')]
class ServiceHub extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'borrow'; // 'borrow', 'qr', 'aspiration', 'assets'

    // Form Peminjaman Alat
    public ?int $borrowInventoryId = null;

    public string $borrowTool = '';

    public string $borrowName = '';

    public string $borrowNim = '';

    public string $borrowStartDate = '';

    public string $borrowEndDate = '';

    public string $borrowPurpose = '';

    public bool $borrowSuccess = false;

    // Presensi QR
    public string $qrToken = '';

    public string $qrNim = '';

    public string $qrAttendanceStatus = '';

    // Aspirasi
    public string $aspirationCategory = 'Fasilitas & Lab';

    public string $aspirationMessage = '';

    public string $aspirationHoneypot = '';

    public string $aspirationTrackingCode = '';

    public bool $aspirationSubmitted = false;

    public string $trackTicketInput = '';

    public ?array $trackTicketResult = null;

    public function mount(): void
    {
        $inventory = Inventory::query()->where('is_active', true)->first();
        $this->borrowInventoryId = $inventory?->id;
        $this->borrowTool = $inventory?->name ?? 'Belum ada alat tersedia';

        if (request()->filled('token')) {
            $this->activeTab = 'qr';
            $this->qrToken = (string) request()->query('token');
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function selectInventory(int $inventoryId): void
    {
        $inventory = Inventory::findOrFail($inventoryId);
        $this->borrowInventoryId = $inventory->id;
        $this->borrowTool = $inventory->name;
    }

    public function submitBorrowForm(CreateBorrowingRequestAction $action): void
    {
        $this->validate([
            'borrowName' => 'required|string|min:3|max:255',
            'borrowNim' => 'required|string|min:5|max:30',
            'borrowInventoryId' => 'required|exists:inventories,id',
            'borrowStartDate' => 'required|date',
            'borrowEndDate' => 'required|date|after:borrowStartDate',
            'borrowPurpose' => 'required|string|min:5|max:5000',
        ]);

        $period = Period::current();
        if (! $period) {
            $this->addError('borrowInventoryId', 'Periode aktif belum tersedia.');

            return;
        }
        $inventory = Inventory::find($this->borrowInventoryId);
        if (! $inventory) {
            $this->addError('borrowInventoryId', 'Alat tidak tersedia pada periode aktif.');

            return;
        }
        try {
            $action->execute($period, $inventory, [
                'requester_name' => $this->borrowName,
                'requester_nim' => $this->borrowNim,
                'purpose' => $this->borrowPurpose,
            ], Carbon::parse($this->borrowStartDate)->startOfDay(), Carbon::parse($this->borrowEndDate)->endOfDay());
            $this->borrowSuccess = true;
        } catch (ValidationException $exception) {
            $fields = ['inventory' => 'borrowInventoryId', 'expected_return_time' => 'borrowEndDate', 'requester_name' => 'borrowName', 'requester_nim' => 'borrowNim', 'purpose' => 'borrowPurpose'];
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($fields[$field] ?? 'borrowInventoryId', $messages[0]);
            }
        }
    }

    public function resetBorrowForm(): void
    {
        $this->borrowSuccess = false;
        $this->borrowName = '';
        $this->borrowNim = '';
        $this->borrowStartDate = '';
        $this->borrowEndDate = '';
        $this->borrowPurpose = '';
    }

    public function submitAttendance(RecordAttendanceAction $action): void
    {
        $this->validate([
            'qrToken' => 'required|digits:6',
            'qrNim' => 'required|string|min:5|max:30',
        ]);

        $session = AttendanceSession::query()->where('status', 'aktif')->where('starts_at', '<=', now())->where('ends_at', '>=', now())->latest('starts_at')->first();
        if (! $session) {
            $this->addError('qrToken', 'Tidak ada sesi presensi aktif saat ini.');

            return;
        }

        try {
            $action->execute($session, $this->qrNim, $this->qrToken);
            $this->qrAttendanceStatus = 'success';
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['token'])) {
                $this->addError('qrToken', $errors['token'][0]);
            }
            if (isset($errors['nim'])) {
                $this->addError('qrNim', $errors['nim'][0]);
            }
        }
    }

    public function submitAspiration(SubmitAspirationAction $action): void
    {
        // 1. Honeypot check (anti-spam)
        if (! empty($this->aspirationHoneypot)) {
            $this->aspirationSubmitted = true;
            $this->aspirationTrackingCode = 'ASP-'.now()->format('Y').'-SPAMBLOCK';

            return;
        }

        // 2. IP Rate limiting (max 5 submissions per 10 minutes)
        $throttleKey = 'aspiration-submit:'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('aspirationMessage', "Terlalu banyak pengiriman aspirasi. Silakan tunggu {$seconds} detik sebelum mengirim lagi.");

            return;
        }

        $this->validate([
            'aspirationCategory' => 'required|string|max:80',
            'aspirationMessage' => 'required|string|min:10|max:5000',
        ]);

        $period = Period::current();
        if (! $period) {
            $this->addError('aspirationMessage', 'Periode aktif belum tersedia.');

            return;
        }

        RateLimiter::hit($throttleKey, 600);

        $aspiration = $action->execute($period, $this->aspirationCategory, $this->aspirationMessage);
        $this->aspirationTrackingCode = $aspiration->tracking_code;
        $this->aspirationSubmitted = true;
        $this->aspirationMessage = '';
    }

    public function searchTicket(): void
    {
        $code = trim(strtoupper($this->trackTicketInput));

        if (empty($code)) {
            $this->trackTicketResult = null;

            return;
        }

        $aspiration = Aspiration::withoutGlobalScopes()->where('tracking_code', $code)->first();
        if ($aspiration) {
            $this->trackTicketResult = [
                'code' => $aspiration->tracking_code,
                'status' => $aspiration->status,
                'category' => $aspiration->category,
                'date' => $aspiration->created_at->format('d M Y'),
                'response' => $aspiration->response ?: 'Aspirasi telah diterima dan menunggu tindak lanjut pengurus.',
            ];
        } else {
            $this->trackTicketResult = [
                'code' => $code,
                'status' => 'Tidak Ditemukan',
                'response' => 'Format kode tidak valid atau belum terdaftar. Pastikan diawali dengan ASP-2026-XXXX.',
            ];
        }
    }

    public function render()
    {
        return view('livewire.public.service-hub', [
            'tools' => Inventory::query()->where('is_active', true)->orderBy('name')->get(),
            'assets' => DesignAsset::query()->where('is_public', true)->orderBy('title')->get(),
        ]);
    }
}
