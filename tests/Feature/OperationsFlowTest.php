<?php

namespace Tests\Feature;

use App\Actions\Attendance\GenerateDynamicQrTokenAction;
use App\Actions\Attendance\RecordAttendanceAction;
use App\Actions\Inventory\UpdateBorrowingStatusAction;
use App\Livewire\Public\ServiceHub;
use App\Models\Aspiration;
use App\Models\AttendanceSession;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Inventory;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class OperationsFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): array
    {
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

        return [$period, User::factory()->create(['role' => 'admin'])];
    }

    public function test_admin_can_approve_borrowing_and_stock_is_reserved(): void
    {
        [$period, $admin] = $this->admin();
        $inventory = Inventory::create(['period_id' => $period->id, 'name' => 'Theodolite', 'code' => 'INV-001', 'total_qty' => 2, 'available_qty' => 2, 'condition' => 'baik']);
        $borrowing = Borrowing::create(['period_id' => $period->id, 'requester_name' => 'Sinta', 'requester_nim' => 'F123', 'purpose' => 'Praktikum', 'start_time' => now(), 'expected_return_time' => now()->addDay(), 'status' => 'menunggu']);
        BorrowingItem::create(['borrowing_id' => $borrowing->id, 'inventory_id' => $inventory->id, 'qty' => 1]);

        $this->actingAs($admin)->post(route('app.operations.operate', ['borrowings', $borrowing->id]), ['status' => 'disetujui'])
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'disetujui', 'verified_by_user_id' => $admin->id]);
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'available_qty' => 2]);
    }

    public function test_admin_can_moderate_an_anonymous_aspiration(): void
    {
        [$period, $admin] = $this->admin();
        $aspiration = Aspiration::create(['period_id' => $period->id, 'tracking_code' => 'ASP-2026-ABC123', 'category' => 'Fasilitas', 'message' => 'Perbaiki lampu.']);

        $this->actingAs($admin)->post(route('app.operations.operate', ['aspirations', $aspiration->id]), ['status' => 'ditanggapi', 'response' => 'Sudah diteruskan.'])
            ->assertRedirect();

        $this->assertDatabaseHas('aspirations', ['id' => $aspiration->id, 'status' => 'ditanggapi', 'response' => 'Sudah diteruskan.', 'responded_by_user_id' => $admin->id]);
    }

    public function test_regular_admin_cannot_manage_periods(): void
    {
        [, $admin] = $this->admin();

        $this->actingAs($admin)->post(route('app.operations.store', 'periods'), ['name' => '2027/2028', 'start_year' => 2027, 'end_year' => 2028])
            ->assertForbidden();
    }

    public function test_admin_can_access_qr_session_display(): void
    {
        [$period, $admin] = $this->admin();
        $session = AttendanceSession::create([
            'period_id' => $period->id,
            'title' => 'Rapat Pleno HMTS',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'token_hash' => Hash::make('123456'),
            'token_expires_at' => now()->addSeconds(30),
            'status' => 'aktif',
            'created_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('app.attendance.qr', ['sessionId' => $session->id]))
            ->assertOk()
            ->assertSee('Rapat Pleno HMTS');
    }

    public function test_dynamic_qr_token_generation_and_attendance_recording(): void
    {
        [$period, $admin] = $this->admin();
        $session = AttendanceSession::create([
            'period_id' => $period->id,
            'title' => 'Sosialisasi Lab',
            'starts_at' => now()->subMinutes(10),
            'ends_at' => now()->addHour(),
            'token_hash' => Hash::make('000000'),
            'token_expires_at' => now()->subMinute(),
            'status' => 'aktif',
            'created_by_user_id' => $admin->id,
        ]);

        $tokenAction = app(GenerateDynamicQrTokenAction::class);
        $token = $tokenAction->execute($session);

        $this->assertIsString($token);
        $this->assertSame(6, strlen($token));
        $this->assertTrue(Hash::check($token, $session->fresh()->token_hash));
        $this->assertTrue($session->fresh()->token_expires_at->isFuture());

        $recordAction = app(RecordAttendanceAction::class);
        $attendance = $recordAction->execute($session->fresh(), 'F11122001', $token);

        $this->assertSame('F11122001', $attendance->nim);
        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $session->id,
            'nim' => 'F11122001',
        ]);
    }

    public function test_borrowing_status_transition_lifecycle(): void
    {
        [$period, $admin] = $this->admin();
        $inventory = Inventory::create([
            'period_id' => $period->id,
            'name' => 'Total Station',
            'code' => 'INV-TS-01',
            'total_qty' => 3,
            'available_qty' => 3,
            'condition' => 'baik',
        ]);

        $borrowing = Borrowing::create([
            'period_id' => $period->id,
            'requester_name' => 'Budi',
            'requester_nim' => 'F11120001',
            'purpose' => 'Ukur Lahan',
            'start_time' => now(),
            'expected_return_time' => now()->addDays(2),
            'status' => 'menunggu',
        ]);
        BorrowingItem::create(['borrowing_id' => $borrowing->id, 'inventory_id' => $inventory->id, 'qty' => 1]);

        $updateAction = app(UpdateBorrowingStatusAction::class);

        // Approval reserves the schedule; handover changes physical stock.
        $updateAction->execute($borrowing->id, 'disetujui', 'Disetujui', $admin);
        $this->assertSame('disetujui', $borrowing->fresh()->status);
        $this->assertSame(3, $inventory->fresh()->available_qty);

        // 2. disetujui -> dipinjam
        $updateAction->execute($borrowing->id, 'dipinjam', 'Diambil peminjam', $admin);
        $this->assertSame('dipinjam', $borrowing->fresh()->status);
        $this->assertSame(2, $inventory->fresh()->available_qty);

        // 3. dipinjam -> dikembalikan (stock restored)
        $updateAction->execute($borrowing->id, 'dikembalikan', 'Selesai peminjaman', $admin);
        $this->assertSame('dikembalikan', $borrowing->fresh()->status);
        $this->assertSame(3, $inventory->fresh()->available_qty);
    }

    public function test_period_cache_is_invalidated_when_switching_active_period(): void
    {
        [$period1] = $this->admin();
        Cache::put('active_period_id', $period1->id, 3600);

        $period2 = Period::create([
            'name' => '2027/2028',
            'start_year' => 2027,
            'end_year' => 2028,
            'is_active' => true,
        ]);

        $this->assertFalse(Cache::has('active_period_id'));
    }

    public function test_aspiration_honeypot_prevents_spam_storage(): void
    {
        [$period] = $this->admin();

        Livewire::test(ServiceHub::class)
            ->set('aspirationCategory', 'Fasilitas & Lab')
            ->set('aspirationMessage', 'Pesan spam otomatis dari robot internet.')
            ->set('aspirationHoneypot', 'i-am-a-bot')
            ->call('submitAspiration')
            ->assertSet('aspirationSubmitted', true);

        $this->assertDatabaseMissing('aspirations', [
            'message' => 'Pesan spam otomatis dari robot internet.',
        ]);
    }
}
