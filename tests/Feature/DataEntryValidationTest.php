<?php

namespace Tests\Feature;

use App\Actions\Attendance\RecordAttendanceAction;
use App\Actions\Inventory\CreateBorrowingRequestAction;
use App\Livewire\Attendance\QrSessionDisplay;
use App\Livewire\Public\ServiceHub;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\DesignAsset;
use App\Models\Division;
use App\Models\Inventory;
use App\Models\Letter;
use App\Models\Period;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class DataEntryValidationTest extends TestCase
{
    use RefreshDatabase;

    private Period $period;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($this->admin);
    }

    public function test_member_blank_sort_order_uses_zero_and_preserves_nullable_fields(): void
    {
        $division = Division::create(['period_id' => $this->period->id, 'name' => 'Infokom']);
        foreach ([[], ['sort_order' => ''], ['sort_order' => '   '], ['sort_order' => null], ['sort_order' => '0'], ['sort_order' => '12']] as $index => $overrides) {
            $data = array_replace($this->memberData(), ['nim' => 'F5512301'.$index, 'division_id' => $division->id], $overrides);
            $this->post('/app/anggota', $data)->assertSessionHasNoErrors()->assertRedirect();
            $this->assertDatabaseHas('members', ['nim' => $data['nim'], 'division_id' => $division->id, 'sort_order' => $index === 5 ? 12 : 0, 'bio' => null]);
        }
        $this->post('/app/anggota', $this->memberData())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('members', ['name' => 'Hasby Ashidiq', 'division_id' => null]);
        $this->post('/app/anggota', array_replace($this->memberData(), [
            'division_id' => $division->id, 'bio' => 'Saya adalah orang ganteng', 'sort_order' => '',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('members', ['nim' => 'F55123010', 'division_id' => $division->id, 'bio' => 'Saya adalah orang ganteng', 'sort_order' => 0]);
    }

    public function test_member_invalid_sort_order_is_a_field_error_not_an_insert(): void
    {
        foreach (['-1', '65536', '1.5', 'abc', ['1']] as $value) {
            $this->post('/app/anggota', array_replace($this->memberData(), ['sort_order' => $value]))->assertSessionHasErrors('sort_order');
        }
        $this->assertDatabaseCount('members', 0);
    }

    public function test_management_relations_must_belong_to_the_target_period(): void
    {
        $old = Period::create(['name' => 'Arsip', 'start_year' => 2025, 'end_year' => 2026, 'is_active' => false]);
        $division = Division::create(['period_id' => $old->id, 'name' => 'Divisi Arsip']);
        $program = Program::create(['period_id' => $old->id, 'title' => 'Proker Arsip', 'category' => 'seminar']);
        $this->post('/app/anggota', array_replace($this->memberData(), ['division_id' => $division->id]))->assertSessionHasErrors('division_id');
        $this->post('/app/notulensi', ['title' => 'Rapat', 'held_at' => now()->toDateTimeString(), 'content' => 'Pembahasan rapat', 'program_id' => $program->id])->assertSessionHasErrors('program_id');
        $this->assertDatabaseCount('members', 0);
        $this->assertDatabaseCount('meeting_notes', 0);
    }

    public function test_inventory_duplicate_bounds_and_blank_boolean_are_handled_before_insert(): void
    {
        $data = ['name' => 'Total Station', 'code' => 'UAT-01', 'category' => '', 'specification' => '', 'total_qty' => 2, 'available_qty' => 2, 'condition' => 'baik', 'is_active' => ''];
        $this->post('/app/inventaris', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventories', ['code' => 'UAT-01', 'is_active' => false, 'category' => null]);
        $this->post('/app/inventaris', $data)->assertSessionHasErrors('code');
        foreach (['-1', '4294967296', '1.5'] as $qty) {
            $this->post('/app/inventaris', array_replace($data, ['code' => 'OTHER', 'total_qty' => $qty, 'available_qty' => 0]))->assertSessionHasErrors('total_qty');
        }
        $this->post('/app/inventaris', array_replace($data, ['code' => 'OTHER', 'available_qty' => 3]))->assertSessionHasErrors('available_qty');
        $this->assertDatabaseCount('inventories', 1);
    }

    public function test_letter_optional_fields_and_duplicate_agenda(): void
    {
        $data = ['agenda_number' => '001/UAT', 'letter_number' => '', 'type' => 'masuk', 'subject' => 'Undangan Rapat', 'sender_recipient' => '', 'received_or_sent_on' => '2026-10-17', 'disposition_note' => '', 'status' => 'terdaftar'];
        $this->post('/app/surat', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('letters', ['agenda_number' => '001/UAT', 'letter_number' => null, 'sender_recipient' => null, 'file_path' => null]);
        $this->post('/app/surat', $data)->assertSessionHasErrors('agenda_number');
        $this->assertDatabaseCount('letters', 1);
        $this->get('/app/surat')->assertOk()->assertDontSee('value="draft"', false);
    }

    public function test_period_unchecked_or_null_does_not_activate_it_and_years_are_bounded(): void
    {
        foreach ([[], ['is_active' => ''], ['is_active' => null], ['is_active' => '0']] as $index => $overrides) {
            $this->post('/app/operasional/periods', array_replace(['name' => 'Inactive '.$index, 'start_year' => 2027, 'end_year' => 2028], $overrides))->assertSessionHasNoErrors();
            $this->assertDatabaseHas('periods', ['name' => 'Inactive '.$index, 'is_active' => false]);
            $this->assertTrue($this->period->fresh()->is_active);
        }
        $this->post('/app/operasional/periods', ['name' => 'Overflow', 'start_year' => 65536, 'end_year' => 65537])->assertSessionHasErrors(['start_year', 'end_year']);
        $this->assertDatabaseCount('periods', 5);
    }

    public function test_private_assets_never_inherit_public_database_default(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        foreach ([[], ['is_public' => ''], ['is_public' => null], ['is_public' => '0'], ['is_public' => '1']] as $index => $overrides) {
            $this->post('/app/aset', array_replace(['title' => 'Aset '.$index, 'category' => 'Template', 'description' => '', 'file' => UploadedFile::fake()->create('template.pdf', 1, 'application/pdf')], $overrides))->assertSessionHasNoErrors();
            $asset = DesignAsset::where('title', 'Aset '.$index)->firstOrFail();
            $this->assertSame($index === 4, $asset->is_public);
            $this->assertSame($index === 4 ? 'public' : 'private', $asset->disk);
            Storage::disk($asset->disk)->assertExists($asset->path);
            if ($index !== 4) {
                $this->get(route('assets.download', $asset))->assertNotFound();
                Livewire::test(ServiceHub::class)->assertDontSee('Aset '.$index);
            }
        }
    }

    public function test_minimal_program_note_session_and_external_committee_can_be_saved(): void
    {
        $this->post('/app/program-kerja', ['title' => 'Proker Minimal', 'category' => 'seminar', 'description' => '', 'starts_at' => '', 'ends_at' => '', 'location' => '', 'status' => 'rencana'])->assertSessionHasNoErrors();
        $program = Program::sole();
        $this->post('/app/notulensi', ['title' => 'Rapat', 'program_id' => '', 'held_at' => '2026-10-17T10:00', 'location' => '', 'participants' => '', 'content' => 'Pembahasan rapat', 'follow_up' => ''])->assertSessionHasNoErrors();
        $this->post('/app/presensi', ['title' => 'Presensi Minimal', 'location' => '', 'starts_at' => '2026-10-17T10:00', 'ends_at' => '2026-10-17T11:00', 'status' => 'draft'])->assertSessionHasNoErrors();
        $this->post('/app/operasional/committees', ['program_id' => $program->id, 'member_id' => '', 'name' => 'Panitia Eksternal', 'nim' => '', 'position' => 'Acara', 'task_status' => 'belum_mulai'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('meeting_notes', ['program_id' => null]);
        $this->assertDatabaseHas('attendance_sessions', ['location' => null]);
        $this->assertDatabaseHas('committees', ['member_id' => null, 'nim' => null]);
    }

    public function test_text_columns_reject_oversized_values(): void
    {
        $this->post('/app/program-kerja', ['title' => 'Terlalu Panjang', 'category' => 'seminar', 'status' => 'rencana', 'description' => str_repeat('a', 5001)])->assertSessionHasErrors('description');
        $this->assertDatabaseCount('programs', 0);
    }

    public function test_public_borrowing_rejects_lengths_and_shows_action_errors(): void
    {
        $inventory = Inventory::create(['period_id' => $this->period->id, 'name' => 'Alat', 'code' => 'PUBLIC-01', 'total_qty' => 1, 'available_qty' => 1, 'is_active' => true]);
        Livewire::test(ServiceHub::class)->set('borrowName', str_repeat('a', 256))->set('borrowNim', str_repeat('1', 31))->set('borrowPurpose', str_repeat('a', 5001))
            ->set('borrowStartDate', '2026-10-18')->set('borrowEndDate', '2026-10-19')->call('submitBorrowForm')->assertHasErrors(['borrowName', 'borrowNim', 'borrowPurpose']);
        $inventory->update(['is_active' => false]);
        Livewire::test(ServiceHub::class)->set('borrowInventoryId', $inventory->id)->set('borrowName', 'Peminjam UAT')->set('borrowNim', 'F55123010')->set('borrowPurpose', 'Praktikum lapangan')
            ->set('borrowStartDate', '2026-10-18')->set('borrowEndDate', '2026-10-19')->call('submitBorrowForm')->assertHasErrors('borrowInventoryId')->assertSet('borrowSuccess', false);
        $this->assertDatabaseCount('borrowings', 0);
    }

    public function test_shared_attendance_action_rejects_oversized_nim_before_sql(): void
    {
        $session = AttendanceSession::create(['period_id' => $this->period->id, 'title' => 'Sesi UAT', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour(), 'status' => 'aktif', 'token_hash' => Hash::make('123456'), 'token_expires_at' => now()->addMinute(), 'created_by_user_id' => $this->admin->id]);
        try {
            app(RecordAttendanceAction::class)->execute($session, str_repeat('1', 31), '123456');
            $this->fail('NIM lebih dari 30 karakter harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('nim', $exception->errors());
        }
        Livewire::test(ServiceHub::class)->set('qrNim', str_repeat('1', 31))->set('qrToken', '123456')->call('submitAttendance')->assertHasErrors('qrNim');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_unique_insert_races_return_field_errors(): void
    {
        Storage::fake('private');
        $cases = [
            [Inventory::class, '/app/inventaris', ['name' => 'Alat', 'code' => 'RACE-01', 'total_qty' => 1, 'available_qty' => 1, 'condition' => 'baik'], 'code'],
            [Letter::class, '/app/surat', ['agenda_number' => 'RACE-01', 'type' => 'masuk', 'subject' => 'Surat', 'received_or_sent_on' => '2026-10-17', 'status' => 'terdaftar', 'file' => UploadedFile::fake()->create('surat.pdf', 1, 'application/pdf')], 'agenda_number'],
            [CashAccount::class, '/app/kas/akun', ['name' => 'Kas', 'code' => 'RACE-01'], 'code'],
        ];
        foreach ($cases as [$model, $url, $data, $field]) {
            $model::creating(function ($record) {
                DB::table($record->getTable())->insert($record->getAttributes());
            });
            try {
                $this->post($url, $data)->assertSessionHasErrors($field)->assertRedirect();
                $this->assertDatabaseCount((new $model)->getTable(), 1);
            } finally {
                Event::forget('eloquent.creating: '.$model);
            }
        }
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_attendance_insert_race_is_reported_as_duplicate_nim(): void
    {
        $session = $this->attendanceSession();
        Attendance::creating(function ($record) {
            DB::table($record->getTable())->insert($record->getAttributes());
        });
        try {
            app(RecordAttendanceAction::class)->execute($session, 'F55123010', '123456');
            $this->fail('Duplikat harus menjadi error NIM.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('nim', $exception->errors());
        } finally {
            Event::forget('eloquent.creating: '.Attendance::class);
        }
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_unrelated_unique_failure_is_not_hidden(): void
    {
        $exception = new UniqueConstraintViolationException('sqlite', 'insert into inventories', [], new \RuntimeException('Unrelated constraint'));
        Inventory::creating(function () use ($exception) {
            throw $exception;
        });
        $this->withoutExceptionHandling();
        try {
            $this->post('/app/inventaris', ['name' => 'Alat', 'code' => 'UNRELATED', 'total_qty' => 1, 'available_qty' => 1, 'condition' => 'baik']);
            $this->fail('Kegagalan database yang tidak dikenali harus diteruskan.');
        } catch (UniqueConstraintViolationException $caught) {
            $this->assertSame($exception, $caught);
        } finally {
            Event::forget('eloquent.creating: '.Inventory::class);
        }
        $this->assertDatabaseCount('inventories', 0);
    }

    public function test_operator_scanner_uses_shared_nim_validation(): void
    {
        $session = $this->attendanceSession();
        Livewire::test(QrSessionDisplay::class, ['sessionId' => $session->id])
            ->call('submitAttendance', '123456', str_repeat('1', 31))
            ->assertSet('scanResult.status', 'error');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_direct_borrowing_action_rejects_oversized_identity(): void
    {
        $inventory = Inventory::create(['period_id' => $this->period->id, 'name' => 'Alat', 'code' => 'DIRECT', 'total_qty' => 1, 'available_qty' => 1, 'is_active' => true]);
        try {
            app(CreateBorrowingRequestAction::class)->execute($this->period, $inventory, ['requester_name' => str_repeat('a', 256), 'requester_nim' => str_repeat('1', 31), 'purpose' => str_repeat('a', 5001)], Carbon::tomorrow(), Carbon::tomorrow()->addDay());
            $this->fail('Payload invalid harus ditolak sebelum insert.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('requester_name', $exception->errors());
            $this->assertArrayHasKey('requester_nim', $exception->errors());
            $this->assertArrayHasKey('purpose', $exception->errors());
        }
        $this->assertDatabaseCount('borrowings', 0);
    }

    public function test_receipt_filename_is_checked_before_storing_file(): void
    {
        Storage::fake('private');
        $account = CashAccount::create(['period_id' => $this->period->id, 'name' => 'Kas', 'code' => 'RECEIPT']);
        $transaction = CashTransaction::create(['period_id' => $this->period->id, 'cash_account_id' => $account->id, 'type' => 'masuk', 'amount' => '1.00', 'description' => 'UAT', 'transacted_at' => now(), 'created_by_user_id' => $this->admin->id]);
        $this->post('/app/operasional/receipts/'.$transaction->id, ['file' => UploadedFile::fake()->create(str_repeat('a', 256).'.pdf', 1, 'application/pdf')])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('transaction_receipts', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_unsigned_boundaries_and_global_duplicates_across_periods(): void
    {
        $old = Period::create(['name' => 'Arsip', 'start_year' => 2025, 'end_year' => 2026, 'is_active' => false]);
        Inventory::create(['period_id' => $old->id, 'name' => 'Arsip', 'code' => 'GLOBAL', 'total_qty' => 1, 'available_qty' => 1]);
        Letter::create(['period_id' => $old->id, 'agenda_number' => 'GLOBAL', 'type' => 'masuk', 'subject' => 'Arsip', 'received_or_sent_on' => '2025-10-17', 'registered_by_user_id' => $this->admin->id]);
        $this->post('/app/inventaris', ['name' => 'Alat', 'code' => 'GLOBAL', 'total_qty' => 1, 'available_qty' => 1, 'condition' => 'baik'])->assertSessionHasErrors('code');
        $this->post('/app/surat', ['agenda_number' => 'GLOBAL', 'type' => 'masuk', 'subject' => 'Surat', 'received_or_sent_on' => '2026-10-17', 'status' => 'terdaftar'])->assertSessionHasErrors('agenda_number');
        $this->post('/app/anggota', $this->memberData() + ['sort_order' => 65535])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('members', ['sort_order' => 65535]);
        $this->post('/app/inventaris', ['name' => 'Batas', 'code' => 'MAX', 'total_qty' => 4294967295, 'available_qty' => 4294967295, 'condition' => 'baik'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventories', ['code' => 'MAX', 'total_qty' => 4294967295]);
        $this->post('/app/operasional/periods', ['name' => 'Batas Tahun', 'start_year' => 65535, 'end_year' => 65535, 'is_active' => '0'])->assertSessionHasNoErrors();
        $this->assertTrue($this->period->fresh()->is_active);
    }

    public function test_invalid_active_period_does_not_deactivate_existing_period(): void
    {
        $this->post('/app/operasional/periods', ['name' => 'Invalid', 'start_year' => 65536, 'end_year' => 65537, 'is_active' => '1'])->assertSessionHasErrors('start_year');
        $this->assertTrue($this->period->fresh()->is_active);
        $this->assertDatabaseCount('periods', 1);
    }

    public function test_optional_controls_match_backend_and_keep_old_input(): void
    {
        foreach (['/app/surat' => ['file', 'letter_number', 'sender_recipient'], '/app/presensi' => ['location'], '/app/operasional/committees' => ['nim', 'member_id']] as $url => $fields) {
            $html = $this->get($url)->assertOk()->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            foreach ($fields as $field) {
                $node = $xpath->query('//*[@name="'.$field.'"]')->item(0);
                $this->assertNotNull($node);
                $this->assertFalse($node->hasAttribute('required'), $url.' '.$field.' must be optional');
            }
        }
        $this->from('/app/surat')->post('/app/surat', ['agenda_number' => 'KEEP-001', 'subject' => 'Tetap tersimpan di form'])->assertSessionHasErrors('type');
        $this->get('/app/surat')->assertSee('value="KEEP-001"', false)->assertSee('value="Tetap tersimpan di form"', false);
        $this->get('/app/aset')->assertSee('type="hidden" name="is_public" value="0"', false);
    }

    private function attendanceSession(): AttendanceSession
    {
        return AttendanceSession::create(['period_id' => $this->period->id, 'title' => 'Sesi UAT', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour(), 'status' => 'aktif', 'token_hash' => Hash::make('123456'), 'token_expires_at' => now()->addMinute(), 'created_by_user_id' => $this->admin->id]);
    }

    private function memberData(): array
    {
        return ['name' => 'Hasby Ashidiq', 'nim' => 'F55123010', 'division_id' => '', 'role_type' => 'staff', 'position' => 'Anggota', 'batch' => '24', 'bio' => ''];
    }
}
