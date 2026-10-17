<?php

namespace Tests\Feature;

use App\Actions\Cash\CreateCashTransactionAction;
use App\Actions\Cash\VoidCashTransactionAction;
use App\Livewire\Public\ProgramIndex;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Committee;
use App\Models\Member;
use App\Models\Period;
use App\Models\Program;
use App\Models\TransactionReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class SprintThreeFlowTest extends TestCase
{
    use RefreshDatabase;

    private Period $period;

    private User $admin;

    private CashAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->account = CashAccount::create(['period_id' => $this->period->id, 'name' => 'Kas Umum', 'code' => 'KAS-01', 'current_balance' => '0.00']);
    }

    public function test_admin_can_initialize_a_zero_balance_account_without_editing_the_ledger(): void
    {
        $this->actingAs($this->admin)->post('/app/kas/akun', ['name' => 'Dana Kegiatan', 'code' => 'KAS-02', 'current_balance' => '500'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('cash_accounts', ['period_id' => $this->period->id, 'code' => 'KAS-02', 'current_balance' => 0]);
        $this->post('/app/kas/akun', ['name' => 'Kode duplikat', 'code' => 'KAS-02'])->assertSessionHasErrors('code');
        $this->assertDatabaseCount('cash_transactions', 0);
        $this->assertSame('Asia/Makassar', config('app.timezone'));
        $this->assertSame('+08:00', now()->format('P'));
    }

    public function test_missing_active_period_does_not_expose_or_mutate_historical_data(): void
    {
        Storage::fake('private');
        $transaction = $this->transaction();
        Program::create(['period_id' => $this->period->id, 'title' => 'Program Historis Rahasia', 'category' => 'workshop']);
        Storage::disk('private')->put('receipts/history.pdf', 'private');
        $receipt = TransactionReceipt::create(['cash_transaction_id' => $transaction->id, 'disk' => 'private', 'path' => 'receipts/history.pdf', 'original_name' => 'history.pdf']);
        $url = URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'receipts', 'id' => $receipt->id]);
        $this->period->update(['is_active' => false]);
        $this->assertNull(Period::current());
        $this->get('/kegiatan')->assertOk()->assertDontSee('Program Historis Rahasia');
        $this->actingAs($this->admin)->get($url)->assertNotFound();
        $this->get('/app/ekspor/kas.csv')->assertStreamedContent("Waktu,Akun,Jenis,Nominal,Status,Keterangan\n");
        $this->post('/app/kas', ['cash_account_id' => $this->account->id, 'type' => 'masuk', 'amount' => '1.00', 'description' => 'Tidak boleh disimpan', 'transacted_at' => now()->toDateTimeString()])->assertNotFound();
        $this->post('/app/kas/akun', ['name' => 'Tanpa Periode', 'code' => 'NO-PERIOD'])->assertStatus(422);
        $this->assertDatabaseCount('cash_transactions', 1);
        $this->assertDatabaseCount('cash_accounts', 1);
    }

    public function test_decimal_cash_uses_fresh_balance_and_rejects_insufficient_funds(): void
    {
        $action = app(CreateCashTransactionAction::class);
        $action->execute($this->account, 'masuk', '10.10', 'Iuran pertama', $this->admin, now());
        $expense = $action->execute($this->account, 'keluar', '0.09', 'Pembelian ATK', $this->admin, now());
        $this->assertSame('10.01', $this->account->fresh()->current_balance);
        $this->assertSame('0.09', $expense->amount);

        try {
            $action->execute($this->account, 'keluar', '10.02', 'Tidak cukup saldo', $this->admin, now());
            $this->fail('Saldo tidak cukup harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }
        $this->assertSame('10.01', $this->account->fresh()->current_balance);
        $this->assertDatabaseCount('cash_transactions', 2);
    }

    public function test_cash_http_validation_rejects_invalid_amounts_and_inactive_accounts(): void
    {
        $this->actingAs($this->admin);
        foreach (['0', '-1', '0.001', '1e3', '10000000000000'] as $amount) {
            $this->post('/app/kas', [
                'cash_account_id' => $this->account->id, 'type' => 'masuk', 'amount' => $amount,
                'description' => 'Nominal tidak valid', 'transacted_at' => now()->toDateTimeString(),
            ])->assertSessionHasErrors('amount');
        }
        $this->account->update(['is_active' => false]);
        $this->post('/app/kas', [
            'cash_account_id' => $this->account->id, 'type' => 'masuk', 'amount' => '10.01',
            'description' => 'Akun tidak aktif', 'transacted_at' => now()->toDateTimeString(),
        ])->assertSessionHasErrors('cash_account_id');
        $this->assertDatabaseCount('cash_transactions', 0);
        $this->assertSame('0.00', $this->account->fresh()->current_balance);
    }

    public function test_balance_overflow_rolls_back_without_ledger_entry(): void
    {
        $this->account->update(['current_balance' => '9999999999999.00']);
        try {
            app(CreateCashTransactionAction::class)->execute($this->account, 'masuk', '1.00', 'Melampaui kapasitas', $this->admin, now());
            $this->fail('Saldo melampaui decimal(15,2) harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }
        $this->assertDatabaseCount('cash_transactions', 0);
        $this->assertSame('9999999999999.00', $this->account->fresh()->current_balance);
    }

    public function test_void_authorization_audit_and_stale_double_submission(): void
    {
        $transaction = $this->transaction();
        $stale = $transaction->fresh();
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($this->admin)->get('/app/kas')->assertOk()->assertDontSee('Void permanen');
        $this->post("/app/kas/{$transaction->id}/void", ['reason' => 'Kesalahan pencatatan kas.'])->assertForbidden();
        $this->actingAs($superadmin)->get('/app/kas')->assertOk()->assertSee('Void');
        $this->post("/app/kas/{$transaction->id}/void", ['reason' => 'pendek'])->assertSessionHasErrors('reason');
        $this->post("/app/kas/{$transaction->id}/void", ['reason' => 'Kesalahan pencatatan kas.'])->assertSessionHasNoErrors()->assertRedirect();
        $transaction->refresh();
        $this->assertSame('voided', $transaction->status);
        $this->assertSame($superadmin->id, $transaction->voided_by_user_id);
        $this->assertNotNull($transaction->voided_at);
        $this->assertSame('10.25', $transaction->amount);
        $this->assertSame('0.00', $this->account->fresh()->current_balance);
        try {
            app(VoidCashTransactionAction::class)->execute($stale, 'Kirim ulang transaksi stale.', $superadmin);
            $this->fail('Void kedua harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('transaction', $exception->errors());
        }
        $this->assertSame('0.00', $this->account->fresh()->current_balance);
    }

    public function test_void_expense_restores_balance_and_void_income_preserves_ledger_history(): void
    {
        $income = $this->transaction();
        $expense = app(CreateCashTransactionAction::class)->execute($this->account, 'keluar', '4.15', 'Pembelian ATK', $this->admin, now());
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        app(VoidCashTransactionAction::class)->execute($income, 'Koreksi pemasukan sebelumnya.', $superadmin);
        $this->assertSame('-4.15', $this->account->fresh()->current_balance);
        app(VoidCashTransactionAction::class)->execute($expense, 'Koreksi pengeluaran sebelumnya.', $superadmin);
        $this->assertSame('0.00', $this->account->fresh()->current_balance);
        $this->assertDatabaseCount('cash_transactions', 2);
    }

    public function test_eloquent_ledger_rejects_editing_and_deleting(): void
    {
        $transaction = $this->transaction();
        foreach (['update', 'delete'] as $operation) {
            try {
                $operation === 'update' ? $transaction->update(['amount' => '99.00']) : $transaction->delete();
                $this->fail('Ledger tidak boleh diedit atau dihapus.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('transaction', $exception->errors());
            }
            $transaction->refresh();
        }
        $this->assertDatabaseHas('cash_transactions', ['id' => $transaction->id, 'amount' => '10.25', 'status' => 'posted']);
    }

    public function test_private_receipt_requires_auth_signature_and_matching_period(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $transaction = $this->transaction();
        $this->actingAs($this->admin)->post("/app/operasional/receipts/{$transaction->id}", [
            'file' => UploadedFile::fake()->create('kuitansi.pdf', 10, 'application/pdf'),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $receipt = TransactionReceipt::sole();
        Storage::disk('private')->assertExists($receipt->path);
        Storage::disk('public')->assertMissing($receipt->path);
        $url = URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'receipts', 'id' => $receipt->id]);
        $this->get($url)->assertOk()->assertDownload('kuitansi.pdf');
        $this->get(route('app.operations.download', ['module' => 'receipts', 'id' => $receipt->id]))->assertForbidden();
        $expired = URL::temporarySignedRoute('app.operations.download', now()->subMinute(), ['module' => 'receipts', 'id' => $receipt->id]);
        $this->get($expired)->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->get($url)->assertRedirect('/login');
        $this->actingAs($this->admin)->withSession(['selected_period_id' => $this->otherPeriod()->id])->get($url)->assertNotFound();
        $this->post("/app/operasional/receipts/{$transaction->id}", [
            'file' => UploadedFile::fake()->create('lain.pdf', 10, 'application/pdf'),
        ])->assertNotFound();
        $this->assertDatabaseCount('transaction_receipts', 1);
    }

    public function test_receipt_rejects_disallowed_file(): void
    {
        Storage::fake('private');
        $transaction = $this->transaction();
        $this->actingAs($this->admin)->post("/app/operasional/receipts/{$transaction->id}", [
            'file' => UploadedFile::fake()->create('script.php', 1, 'text/plain'),
        ])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('transaction_receipts', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_program_management_and_public_filters_use_persisted_period_data(): void
    {
        $data = ['title' => 'Pelatihan Struktur', 'category' => 'workshop', 'description' => 'Pelatihan analisis struktur.',
            'starts_at' => '2026-10-20T09:00', 'ends_at' => '2026-10-20T12:00', 'location' => 'Laboratorium Sipil', 'status' => 'rencana'];
        $this->actingAs($this->admin)->post('/app/program-kerja', $data)->assertSessionHasNoErrors()->assertRedirect();
        $program = Program::sole();
        $this->get("/app/program-kerja/{$program->id}/edit")->assertOk()->assertSee('datetime-local')->assertSee('Laboratorium Sipil');
        $this->put("/app/program-kerja/{$program->id}", array_replace($data, ['status' => 'aktif']))->assertSessionHasNoErrors()->assertRedirect('/app/program-kerja');
        $this->put("/app/program-kerja/{$program->id}", array_replace($data, ['ends_at' => '2026-10-19T09:00']))->assertSessionHasErrors('ends_at');
        $this->put("/app/program-kerja/{$program->id}", array_replace($data, ['category' => 'invalid']))->assertSessionHasErrors('category');
        $hidden = Program::create(['period_id' => $this->otherPeriod()->id, 'title' => 'Arsip periode lama', 'category' => 'workshop']);
        $this->get('/kegiatan')->assertOk()->assertSee('Pelatihan Struktur')->assertDontSee('Arsip periode lama');
        Livewire::test(ProgramIndex::class)->assertSee('Pelatihan Struktur')
            ->call('setCategory', 'seminar')->assertDontSee('Pelatihan Struktur')
            ->call('setCategory', 'workshop')->assertSee('Pelatihan Struktur')
            ->call('openTorModal', $program->id)->assertSee('program-detail-title', false)
            ->call('closeTorModal')->assertSet('activeProgramId', null);
        $this->get("/app/program-kerja/{$hidden->id}/edit")->assertNotFound();
        $this->put("/app/program-kerja/{$hidden->id}", $data)->assertNotFound();
    }

    public function test_committee_links_and_task_status_cannot_cross_periods(): void
    {
        $program = Program::create(['period_id' => $this->period->id, 'title' => 'Seminar Teknik', 'category' => 'seminar']);
        $member = Member::create(['period_id' => $this->period->id, 'name' => 'Anggota Aktif', 'position' => 'Staf']);
        $otherMember = Member::create(['period_id' => $this->otherPeriod()->id, 'name' => 'Anggota Arsip', 'position' => 'Staf']);
        $data = ['program_id' => $program->id, 'member_id' => $member->id, 'name' => 'Panitia Seminar', 'position' => 'Acara', 'task_status' => 'belum_mulai'];
        $this->actingAs($this->admin)->post('/app/operasional/committees', array_replace($data, ['member_id' => $otherMember->id]))->assertNotFound();
        $this->post('/app/operasional/committees', $data)->assertSessionHasNoErrors()->assertRedirect();
        $committee = Committee::sole();
        $this->post("/app/operasional/committees/{$committee->id}", ['task_status' => 'selesai'])->assertSessionHasNoErrors();
        $this->assertSame('selesai', $committee->fresh()->task_status);
        $this->post("/app/operasional/committees/{$committee->id}", ['task_status' => 'invalid'])->assertSessionHasErrors('task_status');
        $this->withSession(['selected_period_id' => $otherMember->period_id])->post("/app/operasional/committees/{$committee->id}", ['task_status' => 'berjalan'])->assertNotFound();
    }

    public function test_csv_exports_are_authorized_scoped_and_escape_spreadsheet_formulas(): void
    {
        Member::create(['period_id' => $this->period->id, 'name' => '=SUM(1,2)', 'nim' => 'F11126001', 'position' => "Staf, \"Acara\"\nBaris dua", 'batch' => '26']);
        Member::create(['period_id' => $this->otherPeriod()->id, 'name' => 'Anggota Tersembunyi', 'position' => 'Arsip']);
        $this->transaction(' +SUM(1,2)');
        $this->get('/app/ekspor/anggota.csv')->assertRedirect('/login');
        $this->get('/app/ekspor/kas.csv')->assertRedirect('/login');
        $memberCsv = $this->actingAs($this->admin)->get('/app/ekspor/anggota.csv')->assertOk()->assertDownload('anggota-hmts.csv')->streamedContent();
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $memberCsv);
        rewind($stream);
        $this->assertSame(['Nama', 'NIM', 'Jabatan', 'Angkatan'], fgetcsv($stream, escape: ''));
        $this->assertSame(["'=SUM(1,2)", 'F11126001', "Staf, \"Acara\"\nBaris dua", '26'], fgetcsv($stream, escape: ''));
        $this->assertFalse(fgetcsv($stream, escape: ''));
        fclose($stream);
        $cashCsv = $this->get('/app/ekspor/kas.csv')->assertOk()->assertDownload('kas-hmts.csv')->streamedContent();
        $this->assertStringContainsString("' +SUM(1,2)", $cashCsv);
        $this->withSession(['selected_period_id' => $this->otherPeriod()->id])->get('/app/ekspor/kas.csv')->assertStreamedContent("Waktu,Akun,Jenis,Nominal,Status,Keterangan\n");
    }

    public function test_command_center_module_views_render_for_authorized_roles(): void
    {
        $this->actingAs($this->admin);
        foreach (['anggota', 'program-kerja', 'inventaris', 'notulensi', 'surat', 'aset', 'peminjaman', 'aspirasi', 'presensi', 'operasional/committees', 'operasional/receipts'] as $path) {
            $this->get('/app/'.$path)->assertOk();
        }
        $this->get('/app/operasional/periods')->assertForbidden();
        $this->get('/app/operasional/admins')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $this->get('/app/operasional/periods')->assertOk();
        $this->get('/app/operasional/admins')->assertOk();
    }

    public function test_guest_redirect_has_a_valid_destination_and_non_admin_is_forbidden(): void
    {
        $this->get('/app')->assertRedirect('/login');
        $this->get('/login')->assertRedirect('/');
        $user = User::factory()->create(['role' => 'member']);
        $this->actingAs($user)->get('/app')->assertForbidden();
        $this->get('/app/ekspor/kas.csv')->assertForbidden();
    }

    private function transaction(string $description = 'Iuran anggota'): CashTransaction
    {
        return app(CreateCashTransactionAction::class)->execute($this->account, 'masuk', '10.25', $description, $this->admin, now());
    }

    private function otherPeriod(): Period
    {
        return Period::firstOrCreate(['name' => '2025/2026'], ['start_year' => 2025, 'end_year' => 2026, 'is_active' => false]);
    }
}
