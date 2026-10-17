<?php

use App\Http\Controllers\App\CashController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ManagementController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\AssetDownloadController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OperationsController;
use App\Livewire\Attendance\QrSessionDisplay;
use App\Livewire\Public\ProgramIndex;
use App\Livewire\Public\ServiceHub;
use App\Models\CashTransaction;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Publik (4 Halaman Utama Terpisah)
|--------------------------------------------------------------------------
| Berdasarkan arsitektur anti-bising pada preview.html & docs/prd.md:
| 1. GET /          → HomeController (Beranda)
| 2. GET /tentang   → AboutController (Profil, Sejarah, Mars, Bagan Bertingkat)
| 3. GET /kegiatan  → ProgramIndex (Kalender Proker Livewire)
| 4. GET /layanan   → ServiceHub (Student Hub Livewire: Pinjam, QR, Aspirasi, Aset)
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [AboutController::class, 'index'])->name('about');
Route::get('/kegiatan', ProgramIndex::class)->name('programs.index');
Route::get('/layanan', ServiceHub::class)->name('services.hub');
Route::get('/aset/{asset}/unduh', AssetDownloadController::class)->name('assets.download');

/*
|--------------------------------------------------------------------------
| Otentikasi Pengurus (Dual-Role: Superadmin & Admin)
|--------------------------------------------------------------------------
*/
Route::get('/login', fn () => redirect()->route('home'))->name('login.form');
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        return redirect()->intended(route('app.dashboard'))->with('status', 'Berhasil masuk ke sistem.');
    }

    return back()->withErrors([
        'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
    ])->onlyInput('email');
})->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');

Route::middleware(['auth', 'can:manage-command-center'])->prefix('app')->as('app.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/anggota', [ManagementController::class, 'index'])->defaults('module', 'members')->name('members.index');
    Route::post('/anggota', [ManagementController::class, 'store'])->defaults('module', 'members')->name('members.store');
    Route::get('/anggota/{member}/edit', [ManagementController::class, 'editMember'])->name('members.edit');
    Route::put('/anggota/{member}', [ManagementController::class, 'updateMember'])->name('members.update');
    Route::put('/periode/{period}', [ManagementController::class, 'updatePeriod'])->name('periods.update');
    Route::get('/program-kerja', [ManagementController::class, 'index'])->defaults('module', 'programs')->name('programs.index');
    Route::post('/program-kerja', [ManagementController::class, 'store'])->defaults('module', 'programs')->name('programs.store');
    Route::get('/program-kerja/{program}/edit', [ManagementController::class, 'editProgram'])->name('programs.edit');
    Route::put('/program-kerja/{program}', [ManagementController::class, 'updateProgram'])->name('programs.update');
    Route::get('/peminjaman', [OperationsController::class, 'index'])->defaults('module', 'borrowings')->name('borrowings.index');
    Route::get('/aspirasi', [OperationsController::class, 'index'])->defaults('module', 'aspirations')->name('aspirations.index');
    Route::get('/presensi', [OperationsController::class, 'index'])->defaults('module', 'attendance')->name('attendance.index');
    Route::post('/presensi', [OperationsController::class, 'store'])->defaults('module', 'attendance')->name('attendance.store');
    Route::get('/presensi/qr', QrSessionDisplay::class)->name('attendance.qr');
    Route::get('/kas', [CashController::class, 'index'])->name('cash.index');
    Route::post('/kas', [CashController::class, 'store'])->name('cash.store');
    Route::post('/kas/akun', [CashController::class, 'storeAccount'])->name('cash.accounts.store');
    Route::post('/kas/{transaction}/void', [CashController::class, 'void'])->name('cash.void');
    Route::get('/inventaris', [ManagementController::class, 'index'])->defaults('module', 'inventory')->name('inventory.index');
    Route::post('/inventaris', [ManagementController::class, 'store'])->defaults('module', 'inventory')->name('inventory.store');
    Route::get('/inventaris/{inventory}/edit', [ManagementController::class, 'editInventory'])->name('inventory.edit');
    Route::put('/inventaris/{inventory}', [ManagementController::class, 'updateInventory'])->name('inventory.update');
    Route::get('/surat', [OperationsController::class, 'index'])->defaults('module', 'letters')->name('letters.index');
    Route::post('/surat', [OperationsController::class, 'store'])->defaults('module', 'letters')->name('letters.store');
    Route::get('/notulensi', [ManagementController::class, 'index'])->defaults('module', 'meeting-notes')->name('meeting-notes.index');
    Route::post('/notulensi', [ManagementController::class, 'store'])->defaults('module', 'meeting-notes')->name('meeting-notes.store');
    Route::get('/notulensi/{note}/edit', [ManagementController::class, 'editMeetingNote'])->name('meeting-notes.edit');
    Route::put('/notulensi/{note}', [ManagementController::class, 'updateMeetingNote'])->name('meeting-notes.update');
    Route::get('/notulensi/{note}/unduh', [ManagementController::class, 'downloadMeetingNote'])->name('meeting-notes.download');
    Route::get('/aset', [OperationsController::class, 'index'])->defaults('module', 'assets')->name('assets.index');
    Route::post('/aset', [OperationsController::class, 'store'])->defaults('module', 'assets')->name('assets.store');
    Route::get('/operasional/{module}', [OperationsController::class, 'index'])->whereIn('module', ['borrowings', 'aspirations', 'attendance', 'letters', 'assets', 'committees', 'periods', 'admins', 'receipts'])->name('operations.index');
    Route::post('/operasional/{module}', [OperationsController::class, 'store'])->whereIn('module', ['attendance', 'letters', 'assets', 'committees', 'periods', 'admins'])->name('operations.store');
    Route::post('/operasional/{module}/{id}', [OperationsController::class, 'operate'])->whereIn('module', ['borrowings', 'aspirations', 'attendance', 'receipts', 'committees'])->name('operations.operate');
    Route::get('/operasional/{module}/{id}/unduh', [OperationsController::class, 'download'])->whereIn('module', ['letters', 'assets', 'receipts'])->middleware('signed')->name('operations.download');
    Route::get('/ekspor/anggota.csv', function () {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'NIM', 'Jabatan', 'Angkatan']);
            Member::query()->orderBy('name')->each(fn (Member $member) => fputcsv($out, array_map(fn ($value) => preg_match('/^[\s]*[=+@\-]/u', (string) $value) ? "'".$value : $value, [$member->name, $member->nim, $member->position, $member->batch]), ',', '"', ''));
            fclose($out);
        }, 'anggota-hmts.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    })->name('exports.members');
    Route::get('/ekspor/kas.csv', function () {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Waktu', 'Akun', 'Jenis', 'Nominal', 'Status', 'Keterangan']);
            CashTransaction::query()->with('cashAccount')->orderBy('transacted_at')->each(fn (CashTransaction $transaction) => fputcsv($out, array_map(fn ($value) => preg_match('/^[\s]*[=+@\-]/u', (string) $value) ? "'".$value : $value, [$transaction->transacted_at->format('Y-m-d H:i:s'), $transaction->cashAccount->name, $transaction->type, $transaction->amount, $transaction->status, $transaction->description]), ',', '"', ''));
            fclose($out);
        }, 'kas-hmts.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    })->name('exports.cash');
});
