<?php

namespace App\Http\Controllers\Public;

use App\Actions\Attendance\GenerateDynamicQrTokenAction;
use App\Http\Controllers\Controller;
use App\Models\Aspiration;
use App\Models\AttendanceSession;
use App\Models\Borrowing;
use App\Models\CashTransaction;
use App\Models\Committee;
use App\Models\DesignAsset;
use App\Models\Letter;
use App\Models\Member;
use App\Models\Period;
use App\Models\Program;
use App\Models\TransactionReceipt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Internal operational UI. Kept separate from public landing controllers. */
class OperationsController extends Controller
{
    public function index(string $module): View
    {
        $definition = $this->definition($module);
        $records = match ($module) {
            'borrowings' => Borrowing::with('items.inventory')->latest()->paginate(15),
            'attendance' => AttendanceSession::withCount('attendances')->latest()->paginate(15),
            'committees' => Committee::with('program')->latest()->paginate(15),
            'receipts' => CashTransaction::with(['cashAccount', 'receipts'])->latest('transacted_at')->paginate(15),
            default => $definition['model']::query()->latest()->paginate(15),
        };

        return view('livewire.public.operations', [
            'module' => $module,
            'definition' => $definition,
            'records' => $records,
            'programs' => Program::orderBy('title')->get(['id', 'title']),
            'members' => Member::orderBy('name')->get(['id', 'name', 'nim']),
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $definition = $this->definition($module);
        if (in_array($module, ['periods', 'admins'], true)) {
            Gate::authorize('manage-periods-and-admins');
        }
        $data = $request->validate($definition['rules']);

        if ($module === 'periods') {
            if ($request->boolean('is_active')) Period::query()->update(['is_active' => false]);
            Period::create($data);
        } elseif ($module === 'admins') {
            User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => $data['role']]);
        } elseif ($module === 'letters') {
            Letter::create($data + ['period_id' => $this->periodId(), 'registered_by_user_id' => $request->user()->id, 'file_path' => $request->file('file')?->store('letters', 'private')]);
        } elseif ($module === 'assets') {
            $disk = $request->boolean('is_public') ? 'public' : 'private';
            DesignAsset::create($data + ['period_id' => $this->periodId(), 'disk' => $disk, 'path' => $request->file('file')->store('assets', $disk), 'uploaded_by_user_id' => $request->user()->id]);
        } elseif ($module === 'attendance') {
            AttendanceSession::create($data + ['period_id' => $this->periodId(), 'token_hash' => Hash::make((string) random_int(100000, 999999)), 'token_expires_at' => now(), 'created_by_user_id' => $request->user()->id]);
        } elseif ($module === 'committees') {
            Committee::create($data + ['period_id' => $this->periodId()]);
        } else {
            abort(404);
        }
        return back()->with('status', 'Data operasional berhasil disimpan.');
    }

    public function operate(Request $request, string $module, int $id, GenerateDynamicQrTokenAction $qr): RedirectResponse
    {
        if ($module === 'borrowings') {
            $data = $request->validate(['status' => 'required|in:disetujui,ditolak,dipinjam,dikembalikan', 'admin_note' => 'nullable|string|max:2000']);
            $borrowing = Borrowing::with('items.inventory')->findOrFail($id);
            if ($data['status'] === 'disetujui' && $borrowing->status === 'menunggu') {
                foreach ($borrowing->items as $item) {
                    if ($item->inventory->available_qty < $item->qty) return back()->withErrors(['status' => 'Stok '.$item->inventory->name.' tidak mencukupi.']);
                    $item->inventory->decrement('available_qty', $item->qty);
                }
            }
            if ($data['status'] === 'dikembalikan' && $borrowing->status !== 'dikembalikan') {
                foreach ($borrowing->items as $item) $item->inventory->increment('available_qty', $item->qty);
                $data['returned_at'] = now();
            }
            $borrowing->update($data + ['verified_by_user_id' => $request->user()->id]);
        } elseif ($module === 'aspirations') {
            $data = $request->validate(['status' => 'required|in:ditinjau,ditanggapi,ditutup', 'response' => 'nullable|string|max:5000|required_if:status,ditanggapi']);
            Aspiration::findOrFail($id)->update($data + ['responded_by_user_id' => $request->user()->id, 'responded_at' => now()]);
        } elseif ($module === 'attendance') {
            $token = $qr->execute(AttendanceSession::findOrFail($id));
            return back()->with('status', 'Token QR aktif selama 30 detik: '.$token);
        } elseif ($module === 'receipts') {
            $data = $request->validate(['file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp']);
            $file = $data['file'];
            TransactionReceipt::create(['cash_transaction_id' => $id, 'disk' => 'private', 'path' => $file->store('receipts', 'private'), 'original_name' => $file->getClientOriginalName()]);
        } else abort(404);

        return back()->with('status', 'Status operasional berhasil diperbarui.');
    }

    public function download(string $module, int $id)
    {
        [$disk, $path, $name] = match ($module) {
            'letters' => (function () use ($id) {
                $letter = Letter::findOrFail($id);
                abort_unless($letter->file_path, 404);
                return ['private', $letter->file_path, 'surat-'.$letter->agenda_number];
            })(),
            'assets' => (function () use ($id) {
                $asset = DesignAsset::findOrFail($id);
                return [$asset->disk, $asset->path, $asset->title];
            })(),
            'receipts' => (function () use ($id) {
                $receipt = TransactionReceipt::findOrFail($id);
                return [$receipt->disk, $receipt->path, $receipt->original_name];
            })(),
            default => abort(404),
        };
        abort_unless(Storage::disk($disk)->exists($path), 404);
        return Storage::disk($disk)->download($path, $name);
    }

    private function periodId(): int
    {
        return Period::current()?->id ?? abort(422, 'Tidak ada periode aktif.');
    }

    private function definition(string $module): array
    {
        return match ($module) {
            'borrowings' => ['title' => 'Verifikasi Peminjaman', 'model' => Borrowing::class, 'rules' => [], 'columns' => ['requester_name' => 'Peminjam', 'purpose' => 'Keperluan', 'status' => 'Status']],
            'aspirations' => ['title' => 'Moderasi Aspirasi Anonim', 'model' => Aspiration::class, 'rules' => [], 'columns' => ['tracking_code' => 'Kode', 'category' => 'Kategori', 'status' => 'Status']],
            'attendance' => ['title' => 'Sesi Presensi QR', 'model' => AttendanceSession::class, 'rules' => ['title'=>'required|string|max:255','location'=>'nullable|string|max:255','starts_at'=>'required|date','ends_at'=>'required|date|after:starts_at','status'=>'required|in:draft,aktif,selesai'], 'columns' => ['title' => 'Sesi', 'starts_at' => 'Mulai', 'status' => 'Status']],
            'letters' => ['title' => 'Surat & Disposisi', 'model' => Letter::class, 'rules' => ['agenda_number'=>'required|string|max:255','letter_number'=>'nullable|string|max:255','type'=>'required|in:masuk,keluar','subject'=>'required|string|max:255','sender_recipient'=>'nullable|string|max:255','received_or_sent_on'=>'required|date','disposition_note'=>'nullable|string','status'=>'required|in:terdaftar,didisposisikan,selesai','file'=>'nullable|file|max:10240|mimes:pdf,doc,docx'], 'columns' => ['agenda_number' => 'Agenda', 'subject' => 'Perihal', 'status' => 'Status']],
            'assets' => ['title' => 'Bank Aset', 'model' => DesignAsset::class, 'rules' => ['title'=>'required|string|max:255','category'=>'required|string|max:80','description'=>'nullable|string','is_public'=>'nullable|boolean','file'=>'required|file|max:20480|mimes:pdf,png,jpg,jpeg,webp,zip'], 'columns' => ['title' => 'Aset', 'category' => 'Kategori', 'disk' => 'Akses']],
            'committees' => ['title' => 'Panitia Program Kerja', 'model' => Committee::class, 'rules' => ['program_id'=>'required|exists:programs,id','member_id'=>'nullable|exists:members,id','name'=>'required|string|max:255','nim'=>'nullable|string|max:30','position'=>'required|string|max:255','task_status'=>'required|in:belum_mulai,berjalan,selesai'], 'columns' => ['name' => 'Nama', 'position' => 'Jabatan', 'task_status' => 'Status']],
            'periods' => ['title' => 'Manajemen Periode', 'model' => Period::class, 'rules' => ['name'=>'required|string|max:255','start_year'=>'required|integer|min:2020','end_year'=>'required|integer|gte:start_year','is_active'=>'nullable|boolean'], 'columns' => ['name' => 'Periode', 'start_year' => 'Mulai', 'is_active' => 'Aktif']],
            'admins' => ['title' => 'Akun Admin', 'model' => User::class, 'rules' => ['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:12','role'=>'required|in:admin,superadmin'], 'columns' => ['name' => 'Nama', 'email' => 'Email', 'role' => 'Peran']],
            'receipts' => ['title' => 'Kuitansi Kas Privat', 'model' => CashTransaction::class, 'rules' => [], 'columns' => ['description' => 'Keterangan', 'amount' => 'Nominal', 'status' => 'Status']],
            default => abort(404),
        };
    }
}
