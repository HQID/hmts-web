<?php

namespace Tests\Feature;

use App\Actions\Cash\CreateCashTransactionAction;
use App\Actions\Cash\VoidCashTransactionAction;
use App\Models\CashAccount;
use App\Models\Inventory;
use App\Models\MeetingNote;
use App\Models\Member;
use App\Models\Period;
use App\Models\Program;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommandCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_center_requires_authentication(): void
    {
        $this->get('/app')->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_open_command_center_and_update_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        $member = Member::create([
            'period_id' => $period->id,
            'name' => 'Rizky Anugrah',
            'nim' => 'F11124001',
            'position' => 'Koordinator Ristek',
            'batch' => "'24",
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)->get('/app')->assertOk()->assertSee('Ringkasan pengurus');

        $this->actingAs($admin)->put('/app/anggota/'.$member->id, [
            'name' => 'Rizky Anugrah S.T.',
            'nim' => 'F11124001',
            'position' => 'Koordinator Divisi Ristek',
            'batch' => "'24",
            'bio' => 'Penanggung jawab riset',
            'sort_order' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'Rizky Anugrah S.T.', 'position' => 'Koordinator Divisi Ristek']);
    }

    public function test_cash_transactions_are_recorded_and_only_superadmin_can_void_them(): void
    {
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $account = CashAccount::create(['period_id' => $period->id, 'name' => 'Kas Umum', 'code' => 'KAS-01', 'current_balance' => 0]);

        $transaction = app(CreateCashTransactionAction::class)->execute($account, 'masuk', 250000, 'Iuran anggota', $admin, now());

        $this->assertDatabaseHas('cash_transactions', ['id' => $transaction->id, 'status' => 'posted']);
        $this->assertSame('250000.00', $account->fresh()->current_balance);

        try {
            app(VoidCashTransactionAction::class)->execute($transaction, 'Kesalahan input nominal transaksi.', $admin);
            $this->fail('Admin tidak boleh membatalkan transaksi kas.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('cash_transactions', ['id' => $transaction->id, 'status' => 'posted']);
        }

        app(VoidCashTransactionAction::class)->execute($transaction->fresh(), 'Kesalahan input nominal transaksi.', $superadmin);
        $this->assertDatabaseHas('cash_transactions', ['id' => $transaction->id, 'status' => 'voided']);
        $this->assertSame('0.00', $account->fresh()->current_balance);
    }

    public function test_admin_can_view_edit_member_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        $member = Member::create([
            'period_id' => $period->id,
            'name' => 'M. Rayhan Pratama',
            'position' => 'Ketua',
        ]);

        $response = $this->actingAs($admin)->get('/app/anggota/'.$member->id.'/edit');

        $response->assertOk();
        $response->assertSee('Sunting Data Pengurus (Ketua)');
        $response->assertSee('M. Rayhan Pratama');
        $response->assertSee('Foto Resmi Pengurus');
        $response->assertSee('type="file"', false);
        $response->assertDontSee('role_type');
    }

    public function test_admin_sees_seeded_members_without_add_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->seed();

        $response = $this->actingAs($admin)->get('/app/anggota');

        $response->assertOk();
        $response->assertSee('Muhammad Farhan Mubarak');
        $response->assertSee('13 Pengurus Terdaftar');
        $response->assertSee('BADAN PENGURUS HARIAN (BPH)');
        $response->assertSee('KOORDINATOR DIVISI PELAKSANA (8 DIVISI)');
        $response->assertDontSee('Tambah data');
    }

    public function test_admin_can_upload_photo_for_member(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        $member = Member::create([
            'period_id' => $period->id,
            'name' => 'M. Rayhan Pratama',
            'position' => 'Ketua',
        ]);

        $file = UploadedFile::fake()->image('rayhan.jpg', 600, 800);

        $response = $this->actingAs($admin)->put('/app/anggota/'.$member->id, [
            'name' => 'M. Rayhan Pratama',
            'position' => 'Ketua',
            'photo' => $file,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/app/anggota');

        $member->refresh();
        $this->assertNotNull($member->photo_path);
        Storage::disk('public')->assertExists($member->photo_path);
    }

    public function test_superadmin_can_update_active_period_from_kepengurusan(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/app/anggota');
        $response->assertOk();
        $response->assertSee('Edit Periode (2026/2027)');

        $updateResponse = $this->actingAs($admin)->put('/app/periode/'.$period->id, [
            'name' => '2026/2028',
            'start_year' => 2026,
            'end_year' => 2028,
        ]);

        $updateResponse->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('periods', [
            'id' => $period->id,
            'name' => '2026/2028',
            'start_year' => 2026,
            'end_year' => 2028,
        ]);
    }

    public function test_admin_can_create_and_edit_internal_program(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/app/program-kerja', [
            'title' => 'Rapat Kerja Pengurus',
            'category' => 'internal',
            'description' => 'Upgrading internal',
            'status' => 'rencana',
        ]);
        $response->assertSessionHasNoErrors()->assertRedirect();

        $program = Program::firstWhere('title', 'Rapat Kerja Pengurus');
        $this->assertNotNull($program);
        $this->assertSame('internal', $program->category);

        $editResponse = $this->actingAs($admin)->put('/app/program-kerja/'.$program->id, [
            'title' => 'Rapat Kerja Pengurus BPH',
            'category' => 'internal',
            'description' => 'Upgrading internal dan BPH',
            'status' => 'aktif',
        ]);
        $editResponse->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('aktif', $program->fresh()->status);
    }

    public function test_admin_can_upload_file_for_meeting_note_and_download_it(): void
    {
        Storage::fake('private');
        $admin = User::factory()->create(['role' => 'admin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

        $file = UploadedFile::fake()->create('notulensi-rapat.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->post('/app/notulensi', [
            'title' => 'Notulensi Rapat Pleno',
            'held_at' => '2026-10-18T09:00',
            'file' => $file,
        ]);
        $response->assertSessionHasNoErrors()->assertRedirect();

        $note = MeetingNote::firstWhere('title', 'Notulensi Rapat Pleno');
        $this->assertNotNull($note);
        $this->assertNotNull($note->file_path);
        Storage::disk('private')->assertExists($note->file_path);

        $downloadResponse = $this->actingAs($admin)->get($note->file_url);
        $downloadResponse->assertOk();
    }

    public function test_admin_can_edit_inventory_and_add_available_qty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

        $inventory = Inventory::create([
            'period_id' => $period->id,
            'name' => 'Total Station Topcon',
            'code' => 'INV-TS-099',
            'category' => 'Ukur Tanah',
            'total_qty' => 3,
            'available_qty' => 1,
            'condition' => 'baik',
            'is_active' => true,
        ]);

        $viewResponse = $this->actingAs($admin)->get('/app/inventaris/'.$inventory->id.'/edit');
        $viewResponse->assertOk();
        $viewResponse->assertSee('Total Station Topcon');
        $viewResponse->assertSee('Mode Edit');

        $updateResponse = $this->actingAs($admin)->put('/app/inventaris/'.$inventory->id, [
            'name' => 'Total Station Topcon Seri GTS',
            'code' => 'INV-TS-099',
            'category' => 'Ukur Tanah',
            'total_qty' => 5,
            'available_qty' => 4,
            'condition' => 'baik',
            'is_active' => 1,
        ]);
        $updateResponse->assertSessionHasNoErrors()->assertRedirect('/app/inventaris');

        $inventory->refresh();
        $this->assertSame(5, $inventory->total_qty);
        $this->assertSame(4, $inventory->available_qty);
        $this->assertSame('Total Station Topcon Seri GTS', $inventory->name);
    }

    public function test_sidebar_contains_inventory_and_borrowings_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/app');

        $response->assertOk();
        $response->assertSee('Inventaris &amp; Stok Alat', false);
        $response->assertSee('Peminjaman Alat');
    }

    public function test_cash_page_renders_stacked_form_and_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/app/kas');

        $response->assertOk();
        $response->assertSee('Catat Transaksi Kas');
        $response->assertSee('Riwayat Transaksi Kas');
        $response->assertSee('space-y-8', false);
    }
}
