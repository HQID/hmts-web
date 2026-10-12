<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ManagementController;
use App\Http\Controllers\App\CashController;
use App\Livewire\Public\ProgramIndex;
use App\Livewire\Public\ServiceHub;
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

/*
|--------------------------------------------------------------------------
| Otentikasi Pengurus (Dual-Role: Superadmin & Admin)
|--------------------------------------------------------------------------
*/
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

Route::middleware('auth')->prefix('app')->as('app.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/anggota', [ManagementController::class, 'index'])->defaults('module', 'members')->name('members.index');
    Route::post('/anggota', [ManagementController::class, 'store'])->defaults('module', 'members')->name('members.store');
    Route::get('/program-kerja', [ManagementController::class, 'index'])->defaults('module', 'programs')->name('programs.index');
    Route::post('/program-kerja', [ManagementController::class, 'store'])->defaults('module', 'programs')->name('programs.store');
    Route::view('/peminjaman', 'app.module', ['module' => 'Inventaris & Peminjaman'])->name('borrowings.index');
    Route::view('/aspirasi', 'app.module', ['module' => 'Aspirasi Mahasiswa'])->name('aspirations.index');
    Route::get('/presensi', [ManagementController::class, 'index'])->defaults('module', 'attendance')->name('attendance.index');
    Route::post('/presensi', [ManagementController::class, 'store'])->defaults('module', 'attendance')->name('attendance.store');
    Route::get('/kas', [CashController::class, 'index'])->name('cash.index');
    Route::post('/kas', [CashController::class, 'store'])->name('cash.store');
    Route::post('/kas/{transaction}/void', [CashController::class, 'void'])->name('cash.void');
    Route::get('/inventaris', [ManagementController::class, 'index'])->defaults('module', 'inventory')->name('inventory.index');
    Route::post('/inventaris', [ManagementController::class, 'store'])->defaults('module', 'inventory')->name('inventory.store');
    Route::get('/surat', [ManagementController::class, 'index'])->defaults('module', 'letters')->name('letters.index');
    Route::post('/surat', [ManagementController::class, 'store'])->defaults('module', 'letters')->name('letters.store');
    Route::get('/notulensi', [ManagementController::class, 'index'])->defaults('module', 'meeting-notes')->name('meeting-notes.index');
    Route::post('/notulensi', [ManagementController::class, 'store'])->defaults('module', 'meeting-notes')->name('meeting-notes.store');
    Route::get('/aset', [ManagementController::class, 'index'])->defaults('module', 'assets')->name('assets.index');
    Route::post('/aset', [ManagementController::class, 'store'])->defaults('module', 'assets')->name('assets.store');
});
