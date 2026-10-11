<?php

namespace Tests\Feature;

use App\Actions\Cash\CreateCashTransactionAction;
use App\Actions\Cash\VoidCashTransactionAction;
use App\Models\CashAccount;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_center_requires_authentication(): void
    {
        $this->get('/app')->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_open_command_center_and_store_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

        $this->actingAs($admin)->get('/app')->assertOk()->assertSee('Ringkasan pengurus');

        $this->actingAs($admin)->post('/app/anggota', [
            'name' => 'Rizky Anugrah',
            'nim' => 'F11124001',
            'role_type' => 'staff',
            'position' => 'Staf Ristek',
            'batch' => "'24",
            'sort_order' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('members', ['nim' => 'F11124001', 'position' => 'Staf Ristek']);
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
}
