<?php

namespace Tests\Feature;

use App\Actions\Cash\CreateCashTransactionAction;
use App\Actions\Cash\VoidCashTransactionAction;
use App\Models\CashAccount;
use App\Models\Member;
use App\Models\Period;
use App\Models\User;
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
        } catch (\Illuminate\Auth\Access\AuthorizationException) {
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
        $response->assertSee('M. Rayhan Pratama');
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

    public function test_admin_can_update_active_period_from_kepengurusan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
}
