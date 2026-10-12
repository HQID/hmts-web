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
    public string $aspirationTrackingCode = '';
    public bool $aspirationSubmitted = false;
    public string $trackTicketInput = '';
    public ?array $trackTicketResult = null;

    public function mount(): void
    {
        $inventory = Inventory::query()->where('is_active', true)->first();
        $this->borrowInventoryId = $inventory?->id;
        $this->borrowTool = $inventory?->name ?? 'Belum ada alat tersedia';
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
            'borrowName' => 'required|min:3',
            'borrowNim' => 'required|min:5',
            'borrowInventoryId' => 'required|exists:inventories,id',
            'borrowStartDate' => 'required|date',
            'borrowEndDate' => 'required|date|after:borrowStartDate',
            'borrowPurpose' => 'required|min:5',
        ]);

        $period = Period::current();
        if (! $period) { $this->addError('borrowInventoryId', 'Periode aktif belum tersedia.'); return; }
        $action->execute($period, Inventory::findOrFail($this->borrowInventoryId), [
            'requester_name' => $this->borrowName,
            'requester_nim' => $this->borrowNim,
            'purpose' => $this->borrowPurpose,
        ], Carbon::parse($this->borrowStartDate)->startOfDay(), Carbon::parse($this->borrowEndDate)->endOfDay());
        $this->borrowSuccess = true;
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
            'qrNim' => 'required|min:5',
        ]);

        $session = AttendanceSession::query()->where('status', 'aktif')->where('starts_at', '<=', now())->where('ends_at', '>=', now())->latest('starts_at')->first();
        if (! $session) { $this->addError('qrToken', 'Tidak ada sesi presensi aktif saat ini.'); return; }
        $action->execute($session, $this->qrNim, $this->qrToken);
        $this->qrAttendanceStatus = 'success';
    }

    public function submitAspiration(SubmitAspirationAction $action): void
    {
        $this->validate([
            'aspirationMessage' => 'required|min:10',
        ]);

        $period = Period::current();
        if (! $period) { $this->addError('aspirationMessage', 'Periode aktif belum tersedia.'); return; }
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
