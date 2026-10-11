<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Aspiration;
use App\Models\AttendanceSession;
use App\Models\Borrowing;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Inventory;
use App\Models\Letter;
use App\Models\Member;
use App\Models\Program;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('app.dashboard', [
            'metrics' => [
                ['label' => 'Anggota aktif', 'value' => Member::count(), 'href' => route('app.members.index')],
                ['label' => 'Agenda kerja', 'value' => Program::count(), 'href' => route('app.programs.index')],
                ['label' => 'Peminjaman menunggu', 'value' => Borrowing::where('status', 'menunggu')->count(), 'href' => route('app.borrowings.index')],
                ['label' => 'Aspirasi baru', 'value' => Aspiration::where('status', 'baru')->count(), 'href' => route('app.aspirations.index')],
            ],
            'cashBalance' => CashAccount::sum('current_balance'),
            'recentTransactions' => CashTransaction::with('cashAccount')->latest('transacted_at')->limit(5)->get(),
            'recentBorrowings' => Borrowing::with('items.inventory')->latest()->limit(5)->get(),
            'activeSessions' => AttendanceSession::where('status', 'aktif')->orderBy('starts_at')->get(),
            'openLetters' => Letter::whereIn('status', ['terdaftar', 'didisposisikan'])->latest()->limit(5)->get(),
            'inventoryCount' => Inventory::where('is_active', true)->count(),
        ]);
    }
}
