<?php

namespace Tests\Feature;

use App\Actions\Attendance\GenerateDynamicQrTokenAction;
use App\Actions\Inventory\CheckBorrowingConflictAction;
use App\Actions\Inventory\UpdateBorrowingStatusAction;
use App\Livewire\Attendance\QrSessionDisplay;
use App\Livewire\Public\ServiceHub;
use App\Models\AttendanceSession;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\DesignAsset;
use App\Models\Inventory;
use App\Models\MeetingNote;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class RepairStagesTest extends TestCase
{
    use RefreshDatabase;

    private Period $period;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfSecond());
        $this->period = Period::create(['name' => 'Test repairs', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin', 'password' => Hash::make('Valid-password-2026')]);
        Cache::flush();
        Storage::fake('public');
        Storage::fake('private');
    }

    private function attendanceSession(array $overrides = []): AttendanceSession
    {
        return AttendanceSession::create(array_replace(['period_id' => $this->period->id, 'title' => 'Rapat', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour(), 'status' => 'aktif', 'token_hash' => Hash::make('123456'), 'token_expires_at' => now()->addSeconds(30), 'created_by_user_id' => $this->admin->id], $overrides));
    }

    private function tool(int $qty = 1): Inventory
    {
        return Inventory::create(['period_id' => $this->period->id, 'name' => 'Total Station', 'code' => 'TEST-'.uniqid(), 'total_qty' => $qty, 'available_qty' => $qty, 'condition' => 'baik', 'is_active' => true]);
    }

    private function loan(Inventory $tool, $start, $end): Borrowing
    {
        $loan = Borrowing::create(['period_id' => $this->period->id, 'requester_name' => 'Mahasiswa', 'requester_nim' => 'F11129999', 'purpose' => 'Praktikum', 'start_time' => $start, 'expected_return_time' => $end, 'status' => 'menunggu']);
        BorrowingItem::create(['borrowing_id' => $loan->id, 'inventory_id' => $tool->id, 'qty' => 1]);

        return $loan;
    }

    public function test_admin_cannot_edit_period_by_alternate_endpoint_or_ui(): void
    {
        $this->actingAs($this->admin)->get('/app/anggota')->assertDontSee('Edit Periode (');
        $this->put('/app/periode/'.$this->period->id, ['name' => 'Forged', 'start_year' => 2027, 'end_year' => 2028])->assertForbidden();
        $this->assertSame('Test repairs', $this->period->fresh()->name);
    }

    public function test_login_has_named_visible_errors_and_limits_attempts_then_recovers(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->from('/')->post('/login', ['email' => $this->admin->email, 'password' => 'wrong'])->assertSessionHasErrorsIn('login', 'email');
        }
        $this->from('/')->post('/login', ['email' => $this->admin->email, 'password' => 'Valid-password-2026'])->assertHeader('Retry-After', '60')->assertSessionHasErrorsIn('login', ['email' => 'Terlalu banyak percobaan masuk. Silakan tunggu satu menit.']);
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'Valid-password-2026'])->assertRedirect('/app');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_notes_require_private_disk_auth_signature_and_matching_period(): void
    {
        $this->actingAs($this->admin)->post('/app/notulensi', ['title' => 'Internal', 'held_at' => now()->toDateTimeString(), 'file' => UploadedFile::fake()->create('rapat.pdf', 1, 'application/pdf')])->assertSessionHasNoErrors();
        $note = MeetingNote::sole();
        $this->assertSame('private', $note->file_disk);
        Storage::disk('private')->assertExists($note->file_path);
        Storage::disk('public')->assertMissing($note->file_path);
        $this->get($note->file_url)->assertOk()->assertDownload();
        $this->get('/app/notulensi/'.$note->id.'/unduh')->assertForbidden();
        $expired = URL::temporarySignedRoute('app.meeting-notes.download', now()->subMinute(), ['note' => $note->id]);
        $this->get($expired)->assertForbidden();
        $this->withSession(['selected_period_id' => 99999])->get($note->file_url)->assertNotFound();
        auth()->logout();
        $this->get($note->file_url)->assertRedirect();
    }

    public function test_legacy_note_migration_is_dry_run_verified_and_idempotent(): void
    {
        Storage::disk('public')->put('meeting-notes/old.pdf', 'original bytes');
        $note = MeetingNote::create(['period_id' => $this->period->id, 'title' => 'Legacy', 'held_at' => now(), 'file_path' => 'meeting-notes/old.pdf', 'file_disk' => 'public', 'created_by_user_id' => $this->admin->id]);
        $this->actingAs($this->admin)->get('/app/notulensi')->assertSee('Menunggu pemindahan berkas privat')->assertDontSee('href=""', false);
        $this->artisan('hmts:secure-meeting-notes')->assertSuccessful();
        $this->assertSame('public', $note->fresh()->file_disk);
        Storage::disk('public')->assertExists($note->file_path);
        $this->artisan('hmts:secure-meeting-notes', ['--apply' => true])->assertSuccessful();
        $note->refresh();
        $this->assertSame('original bytes', Storage::disk('private')->get($note->file_path));
        Storage::disk('public')->assertMissing('meeting-notes/old.pdf');
        $this->assertNotEmpty(Storage::disk('private')->files('migration-backups/meeting-notes/'.$note->id));
        $this->artisan('hmts:secure-meeting-notes', ['--apply' => true])->assertSuccessful();
    }

    public function test_failed_note_replacement_preserves_old_file_and_cleans_new_file(): void
    {
        Storage::disk('private')->put('meeting-notes/old.pdf', 'keep me');
        $note = MeetingNote::create(['period_id' => $this->period->id, 'title' => 'Original', 'held_at' => now(), 'file_path' => 'meeting-notes/old.pdf', 'created_by_user_id' => $this->admin->id]);
        MeetingNote::updating(fn () => throw new \RuntimeException('Simulated DB failure'));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->admin)->put('/app/notulensi/'.$note->id, ['title' => 'New', 'held_at' => now()->toDateTimeString(), 'file' => UploadedFile::fake()->create('replacement.pdf', 1, 'application/pdf')]);
            $this->fail('Expected failed update.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated DB failure', $e->getMessage());
        } finally {
            Event::forget('eloquent.updating: '.MeetingNote::class);
        }
        $this->assertSame(['meeting-notes/old.pdf'], Storage::disk('private')->allFiles());
        $this->assertSame('Original', $note->fresh()->title);
    }

    public function test_public_assets_render_and_download_actual_files_but_not_private_assets(): void
    {
        $this->actingAs($this->admin)->post('/app/aset', ['title' => 'Public actual', 'category' => 'Dokumen', 'is_public' => '1', 'file' => UploadedFile::fake()->create('actual.pdf', 4, 'application/pdf')])->assertSessionHasNoErrors();
        $asset = DesignAsset::sole();
        $private = DesignAsset::create(['period_id' => $this->period->id, 'title' => 'Secret document', 'category' => 'Internal', 'disk' => 'private', 'path' => 'secret.pdf', 'is_public' => false, 'uploaded_by_user_id' => $this->admin->id]);
        auth()->logout();
        Livewire::test(ServiceHub::class)->set('activeTab', 'assets')->assertSee('Public actual')->assertSee('PDF')->assertDontSee('Secret document')->assertDontSee('Blueprint CAD Jembatan');
        $this->get(route('assets.download', $asset))->assertOk()->assertDownload();
        $this->get(route('assets.download', $private))->assertNotFound();
        Storage::disk('public')->delete($asset->path);
        Livewire::test(ServiceHub::class)->set('activeTab', 'assets')->assertSee('Berkas belum tersedia')->assertDontSee('Unduh berkas');
        $this->get(route('assets.download', $asset))->assertNotFound();
    }

    public function test_two_projectors_share_token_and_manual_rotation_invalidates_old_token(): void
    {
        $session = $this->attendanceSession();
        $action = app(GenerateDynamicQrTokenAction::class);
        $first = $action->execute($session);
        $this->assertSame($first, $action->execute($session));
        $next = $action->execute($session, true);
        $this->assertSame($next, $action->execute($session));
        $this->assertFalse(Hash::check($first, $session->fresh()->token_hash));
        $this->travel(31)->seconds();
        $action->execute($session);
        $this->assertTrue($session->fresh()->token_expires_at->isFuture());
    }

    public function test_public_attendance_uses_selected_session_and_rejects_cross_session_token(): void
    {
        $a = $this->attendanceSession(['title' => 'Session A', 'starts_at' => now()->subMinutes(20)]);
        $b = $this->attendanceSession(['title' => 'Session B', 'starts_at' => now()->subMinutes(10)]);
        $token = app(GenerateDynamicQrTokenAction::class)->execute($a);
        app(GenerateDynamicQrTokenAction::class)->execute($b);
        Livewire::test(ServiceHub::class)->set('qrNim', 'F11129999')->set('qrToken', $token)->call('submitAttendance')->assertHasErrors('qrSessionId');
        Livewire::test(ServiceHub::class)->set('qrSessionId', (string) $b->id)->set('qrNim', 'F11129999')->set('qrToken', $token)->call('submitAttendance')->assertHasErrors('qrToken');
        Livewire::test(ServiceHub::class)->set('qrSessionId', (string) $a->id)->set('qrNim', 'F11129999')->set('qrToken', $token)->call('submitAttendance')->assertSet('qrAttendanceStatus', 'success');
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['attendance_session_id' => $a->id]);
    }

    public function test_session_window_is_enforced_for_operator_and_token_generation(): void
    {
        foreach ([['starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2)], ['starts_at' => now()->subHours(2), 'ends_at' => now()], ['status' => 'draft'], ['status' => 'selesai']] as $overrides) {
            $session = $this->attendanceSession($overrides);
            $this->actingAs($this->admin);
            Livewire::test(QrSessionDisplay::class, ['sessionId' => $session->id])->assertSet('qrToken', null)->call('submitAttendance', '123456', 'F11129999')->assertSet('scanResult.status', 'error');
            $this->assertSame($overrides['status'] ?? 'aktif', $session->fresh()->status);
        }
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_non_overlapping_approvals_preserve_stock_until_handover_and_reject_double_return(): void
    {
        $tool = $this->tool();
        $a = $this->loan($tool, now()->subMinute(), now()->addHour());
        $b = $this->loan($tool, now()->addDays(2), now()->addDays(3));
        $action = app(UpdateBorrowingStatusAction::class);
        foreach ([$a, $b] as $loan) {
            $action->execute($loan->id, 'disetujui', null, $this->admin);
        }
        $this->assertSame(1, $tool->fresh()->available_qty);
        $action->execute($a->id, 'dipinjam', null, $this->admin);
        $this->assertSame(0, $tool->fresh()->available_qty);
        $action->execute($a->id, 'dikembalikan', null, $this->admin);
        $this->assertSame(1, $tool->fresh()->available_qty);
        try {
            $action->execute($a->id, 'dikembalikan', null, $this->admin);
            $this->fail('Double return accepted.');
        } catch (ValidationException) {
            $this->assertSame(1, $tool->fresh()->available_qty);
        }
        $action->execute($b->id, 'ditolak', null, $this->admin);
        $this->assertSame(1, $tool->fresh()->available_qty);
    }

    public function test_capacity_uses_peak_overlap_instead_of_sum_and_respects_boundaries(): void
    {
        $tool = $this->tool(2);
        $action = app(UpdateBorrowingStatusAction::class);
        foreach ([[1, 2], [2, 3]] as [$from, $until]) {
            $loan = $this->loan($tool, now()->addDays($from), now()->addDays($until));
            $action->execute($loan->id, 'disetujui', null, $this->admin);
        }
        $span = $this->loan($tool, now()->addDay(), now()->addDays(3));
        $action->execute($span->id, 'disetujui', null, $this->admin);
        $extra = $this->loan($tool, now()->addDay(), now()->addDays(3));
        try {
            $action->execute($extra->id, 'disetujui', null, $this->admin);
            $this->fail('Overbooking accepted.');
        } catch (ValidationException) {
            $this->assertSame('menunggu', $extra->fresh()->status);
        }
    }

    public function test_overdue_units_remain_unavailable_and_inventory_edit_cannot_inflate_stock(): void
    {
        $tool = $this->tool();
        $loan = $this->loan($tool, now()->subMinute(), now()->addMinute());
        $action = app(UpdateBorrowingStatusAction::class);
        $action->execute($loan->id, 'disetujui', null, $this->admin);
        $action->execute($loan->id, 'dipinjam', null, $this->admin);
        $this->travel(2)->minutes();
        $this->assertFalse(app(CheckBorrowingConflictAction::class)->isAvailable($tool->fresh(), 1, now()->addDay(), now()->addDays(2)));
        $this->actingAs($this->admin)->put('/app/inventaris/'.$tool->id, ['name' => $tool->name, 'code' => $tool->code, 'total_qty' => 1, 'available_qty' => 1, 'condition' => 'baik', 'is_active' => '1'])->assertSessionHasErrors('available_qty');
        $this->assertSame(0, $tool->fresh()->available_qty);
    }

    public function test_legacy_inventory_reconciliation_is_explicit_and_idempotent(): void
    {
        $tool = $this->tool();
        $tool->forceFill(['available_qty' => 0, 'stock_version' => 1])->save();
        $loan = $this->loan($tool, now()->addDay(), now()->addDays(2));
        $loan->update(['status' => 'disetujui']);
        $this->artisan('hmts:reconcile-inventory')->assertSuccessful();
        $this->assertSame(0, $tool->fresh()->available_qty);
        $this->artisan('hmts:reconcile-inventory', ['--apply' => true])->assertSuccessful();
        $this->assertSame(1, $tool->fresh()->available_qty);
        $this->assertSame(2, $tool->fresh()->stock_version);
        $this->artisan('hmts:reconcile-inventory', ['--apply' => true])->assertSuccessful();
        $this->assertSame(1, $tool->fresh()->available_qty);
    }

    public function test_missing_legacy_file_and_inconsistent_stock_are_reported_without_data_loss(): void
    {
        $note = MeetingNote::create(['period_id' => $this->period->id, 'title' => 'Missing legacy', 'held_at' => now(), 'file_path' => 'missing.pdf', 'file_disk' => 'public', 'created_by_user_id' => $this->admin->id]);
        $this->artisan('hmts:secure-meeting-notes', ['--apply' => true])->assertFailed();
        $this->assertSame('public', $note->fresh()->file_disk);
        $tool = $this->tool();
        $loan = $this->loan($tool, now(), now()->addHour());
        $loan->update(['status' => 'disetujui']);
        $tool->forceFill(['stock_version' => 1])->save();
        $this->artisan('hmts:reconcile-inventory', ['--apply' => true])->assertFailed();
        $this->assertSame(1, $tool->fresh()->stock_version);
        $this->assertSame(1, $tool->fresh()->available_qty);
    }

    public function test_future_handover_and_capacity_reduction_cannot_break_reserved_units(): void
    {
        $tool = $this->tool();
        $loan = $this->loan($tool, now()->addDay(), now()->addDays(2));
        $action = app(UpdateBorrowingStatusAction::class);
        $action->execute($loan->id, 'disetujui', null, $this->admin);
        try {
            $action->execute($loan->id, 'dipinjam', null, $this->admin);
            $this->fail('Early handover accepted.');
        } catch (ValidationException) {
            $this->assertSame(1, $tool->fresh()->available_qty);
        }
        $this->actingAs($this->admin)->put('/app/inventaris/'.$tool->id, ['name' => $tool->name, 'code' => $tool->code, 'total_qty' => 0, 'available_qty' => 0, 'condition' => 'baik', 'is_active' => '1'])->assertSessionHasErrors('available_qty');
        $this->assertSame(1, $tool->fresh()->total_qty);
    }
}
