<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\DesignAsset;
use App\Models\Division;
use App\Models\Inventory;
use App\Models\Letter;
use App\Models\MeetingNote;
use App\Models\Member;
use App\Models\Period;
use App\Models\Program;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManagementController extends Controller
{
    public function index(string $module): View
    {
        $definition = $this->definition($module);
        $model = $definition['model'];

        $records = $module === 'members'
            ? Member::with('division')->orderBy('sort_order')->paginate(20)
            : $model::query()->latest()->paginate(15);

        return view('app.management.index', [
            'module' => $module,
            'definition' => $definition,
            'records' => $records,
            'divisions' => Division::orderBy('sort_order')->get(['id', 'name']),
            'programs' => Program::orderBy('starts_at')->get(['id', 'title']),
            'currentPeriod' => Period::current(),
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        if ($module === 'members') {
            return redirect()->route('app.members.index')
                ->with('status', 'Penambahan anggota baru dinonaktifkan. Pengurus dikelola melalui tombol Sunting.');
        }

        foreach (['starts_at', 'ends_at', 'held_at'] as $dtField) {
            if ($request->filled($dtField . '_date')) {
                $time = $request->input($dtField . '_time') ?: ($dtField === 'held_at' ? '09:00' : '08:00');
                $request->merge([
                    $dtField => $request->input($dtField . '_date') . ' ' . $time,
                ]);
            }
        }

        $definition = $this->definition($module);
        $periodId = Period::current()?->id ?? abort(422, 'Tidak ada periode aktif.');
        $rules = $definition['rules'];
        if ($module === 'meeting-notes') {
            $rules['program_id'] = ['nullable', Rule::exists('programs', 'id')->where('period_id', $periodId)];
        }
        $data = $request->validate($rules);
        $model = $definition['model'];
        $data['period_id'] = $periodId;

        if ($module === 'inventory') {
            $data['is_active'] = $request->boolean('is_active');
        }

        if (in_array('created_by_user_id', $definition['attributes'], true)) {
            $data['created_by_user_id'] = $request->user()->id;
        }

        if (in_array('registered_by_user_id', $definition['attributes'], true)) {
            $data['registered_by_user_id'] = $request->user()->id;
        }

        if (in_array('uploaded_by_user_id', $definition['attributes'], true)) {
            $data['uploaded_by_user_id'] = $request->user()->id;
        }

        if ($module === 'attendance') {
            $data['token_hash'] = Hash::make((string) random_int(100000, 999999));
            $data['token_expires_at'] = now();
        }

        if ($module === 'meeting-notes') {
            if ($request->hasFile('file')) {
                $data['file_path'] = $request->file('file')->store('meeting-notes', 'public');
            } else {
                $data['file_path'] = null;
            }
        }

        try {
            $model::query()->create(Arr::only($data, $definition['attributes']));
        } catch (UniqueConstraintViolationException $exception) {
            if ($module !== 'inventory' || ! Inventory::withoutGlobalScopes()->where('code', $data['code'])->exists()) {
                throw $exception;
            }
            throw ValidationException::withMessages(['code' => 'Kode inventaris sudah digunakan.']);
        }

        return back()->with('status', 'Data berhasil disimpan.');
    }

    public function editMember(Member $member): View
    {
        return view('app.management.index', [
            'module' => 'members',
            'definition' => $this->definition('members'),
            'records' => Member::with('division')->orderBy('sort_order')->paginate(20),
            'divisions' => Division::orderBy('sort_order')->get(['id', 'name']),
            'programs' => collect(),
            'editing' => $member,
            'currentPeriod' => Period::current(),
        ]);
    }

    public function updatePeriod(Request $request, Period $period): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:2000|max:65535',
            'end_year' => 'required|integer|gte:start_year|max:65535',
        ]);

        $period->update($data);
        cache()->forget('active_period_id');

        return back()->with('status', 'Periode kepengurusan berhasil diperbarui menjadi ' . $period->name . '.');
    }

    public function updateMember(Request $request, Member $member): RedirectResponse
    {
        $periodId = $member->period_id;
        $rules = [
            'name' => 'required|string|max:255',
            'nim' => 'nullable|string|max:30',
            'division_id' => ['nullable', Rule::exists('divisions', 'id')->where('period_id', $periodId)],
            'position' => 'required|string|max:255',
            'batch' => 'nullable|string|max:10',
            'bio' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:3072',
            'photo_path' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ];

        $data = $request->validate($rules);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('members', 'public');
            $data['photo_path'] = $path;
        } elseif (empty($data['photo_path'])) {
            $data['photo_path'] = $member->photo_path;
        }

        unset($data['photo']);

        $member->update($data);

        return redirect()->route('app.members.index')->with('status', 'Data pengurus ' . $member->name . ' berhasil diperbarui.');
    }

    public function editProgram(Program $program): View
    {
        return view('app.management.index', [
            'module' => 'programs',
            'definition' => $this->definition('programs'),
            'records' => Program::orderBy('starts_at')->paginate(15),
            'divisions' => collect(),
            'programs' => collect(),
            'editing' => $program,
            'currentPeriod' => Period::current(),
        ]);
    }

    public function updateProgram(Request $request, Program $program): RedirectResponse
    {
        foreach (['starts_at', 'ends_at'] as $dtField) {
            if ($request->filled($dtField . '_date')) {
                $time = $request->input($dtField . '_time') ?: '08:00';
                $request->merge([
                    $dtField => $request->input($dtField . '_date') . ' ' . $time,
                ]);
            }
        }

        $program->update($request->validate($this->definition('programs')['rules']));

        return redirect()->route('app.programs.index')->with('status', 'Program kerja berhasil diperbarui.');
    }

    public function editInventory(Inventory $inventory): View
    {
        return view('app.management.index', [
            'module' => 'inventory',
            'definition' => $this->definition('inventory'),
            'records' => Inventory::latest()->paginate(15),
            'divisions' => collect(),
            'programs' => collect(),
            'editing' => $inventory,
            'currentPeriod' => Period::current(),
        ]);
    }

    public function updateInventory(Request $request, Inventory $inventory): RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:40', Rule::unique('inventories', 'code')->ignore($inventory->id)],
            'category' => 'nullable|string|max:255',
            'specification' => 'nullable|string|max:5000',
            'total_qty' => 'required|integer|min:0|max:4294967295',
            'available_qty' => 'required|integer|min:0|max:4294967295|lte:total_qty',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat',
            'is_active' => 'nullable|boolean',
        ];

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');

        $inventory->update($data);

        return redirect()->route('app.inventory.index')->with('status', 'Data alat inventaris ' . $inventory->name . ' berhasil diperbarui.');
    }

    public function editMeetingNote(MeetingNote $note): View
    {
        return view('app.management.index', [
            'module' => 'meeting-notes',
            'definition' => $this->definition('meeting-notes'),
            'records' => MeetingNote::with('program')->latest('held_at')->paginate(15),
            'divisions' => collect(),
            'programs' => Program::orderBy('starts_at')->get(['id', 'title']),
            'editing' => $note,
            'currentPeriod' => Period::current(),
        ]);
    }

    public function updateMeetingNote(Request $request, MeetingNote $note): RedirectResponse
    {
        if ($request->filled('held_at_date')) {
            $time = $request->input('held_at_time') ?: '09:00';
            $request->merge([
                'held_at' => $request->input('held_at_date') . ' ' . $time,
            ]);
        }

        $periodId = $note->period_id;
        $rules = [
            'program_id' => ['nullable', Rule::exists('programs', 'id')->where('period_id', $periodId)],
            'title' => 'required|string|max:255',
            'held_at' => 'required|date',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt,csv,png,jpg,jpeg,webp',
        ];

        $data = $request->validate($rules);

        if ($request->hasFile('file')) {
            if ($note->file_path && Storage::disk('public')->exists($note->file_path)) {
                Storage::disk('public')->delete($note->file_path);
            }
            $data['file_path'] = $request->file('file')->store('meeting-notes', 'public');
        }

        unset($data['file']);

        $note->update($data);

        return redirect()->route('app.meeting-notes.index')->with('status', 'Notulensi rapat ' . $note->title . ' berhasil diperbarui.');
    }

    public function downloadMeetingNote(MeetingNote $note)
    {
        abort_unless($note->file_path && Storage::disk('public')->exists($note->file_path), 404);

        return Storage::disk('public')->download($note->file_path, 'notulensi-' . str($note->title)->slug() . '.' . pathinfo($note->file_path, PATHINFO_EXTENSION));
    }

    private function definition(string $module): array
    {
        return match ($module) {
            'members' => ['title' => 'Kepengurusan', 'model' => Member::class, 'attributes' => ['period_id', 'division_id', 'name', 'nim', 'position', 'batch', 'bio', 'photo_path', 'sort_order'], 'rules' => ['name' => 'required|string|max:255', 'nim' => 'nullable|string|max:30', 'division_id' => 'nullable|exists:divisions,id', 'position' => 'required|string|max:255', 'batch' => 'nullable|string|max:10', 'bio' => 'nullable|string|max:255', 'photo_path' => 'nullable|string|max:255', 'sort_order' => 'nullable|integer|min:0|max:65535'], 'columns' => ['name' => 'Nama', 'nim' => 'NIM', 'position' => 'Jabatan']],
            'programs' => ['title' => 'Program Kerja & Panitia', 'model' => Program::class, 'attributes' => ['period_id', 'title', 'category', 'description', 'starts_at', 'ends_at', 'location', 'status'], 'rules' => ['title' => 'required|string|max:255', 'category' => 'required|in:internal,competition,workshop,community,seminar', 'description' => 'nullable|string|max:5000', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after_or_equal:starts_at', 'location' => 'nullable|string|max:255', 'status' => 'required|in:rencana,aktif,selesai,dibatalkan'], 'columns' => ['title' => 'Program', 'category' => 'Kategori', 'status' => 'Status']],
            'inventory' => ['title' => 'Inventaris', 'model' => Inventory::class, 'attributes' => ['period_id', 'name', 'code', 'category', 'specification', 'total_qty', 'available_qty', 'condition', 'is_active'], 'rules' => ['name' => 'required|string|max:255', 'code' => 'required|string|max:40|unique:inventories,code', 'category' => 'nullable|string|max:255', 'specification' => 'nullable|string|max:5000', 'total_qty' => 'required|integer|min:0|max:4294967295', 'available_qty' => 'required|integer|min:0|max:4294967295|lte:total_qty', 'condition' => 'required|in:baik,rusak_ringan,rusak_berat', 'is_active' => 'nullable|boolean'], 'columns' => ['name' => 'Nama Alat', 'code' => 'Kode', 'total_qty' => 'Total Unit', 'available_qty' => 'Tersedia', 'condition' => 'Kondisi']],
            'attendance' => ['title' => 'Sesi Presensi QR', 'model' => AttendanceSession::class, 'attributes' => ['period_id', 'title', 'location', 'starts_at', 'ends_at', 'token_hash', 'token_expires_at', 'status', 'created_by_user_id'], 'rules' => ['title' => 'required|string|max:255', 'location' => 'nullable|string|max:255', 'starts_at' => 'required|date', 'ends_at' => 'required|date|after:starts_at', 'status' => 'required|in:draft,aktif,selesai'], 'columns' => ['title' => 'Sesi', 'starts_at' => 'Mulai', 'status' => 'Status']],
            'letters' => ['title' => 'Surat & Disposisi', 'model' => Letter::class, 'attributes' => ['period_id', 'agenda_number', 'letter_number', 'type', 'subject', 'sender_recipient', 'received_or_sent_on', 'disposition_note', 'status', 'registered_by_user_id'], 'rules' => ['agenda_number' => 'required|string|max:255', 'letter_number' => 'nullable|string|max:255', 'type' => 'required|in:masuk,keluar', 'subject' => 'required|string|max:255', 'sender_recipient' => 'nullable|string|max:255', 'received_or_sent_on' => 'required|date', 'disposition_note' => 'nullable|string', 'status' => 'required|in:terdaftar,didisposisikan,selesai'], 'columns' => ['agenda_number' => 'Agenda', 'subject' => 'Perihal', 'status' => 'Status']],
            'meeting-notes' => ['title' => 'Notulensi', 'model' => MeetingNote::class, 'attributes' => ['period_id', 'program_id', 'title', 'held_at', 'file_path', 'created_by_user_id'], 'rules' => ['program_id' => 'nullable|exists:programs,id', 'title' => 'required|string|max:255', 'held_at' => 'required|date', 'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt,csv,png,jpg,jpeg,webp'], 'columns' => ['title' => 'Agenda Rapat', 'held_at' => 'Waktu Pelaksanaan', 'file_path' => 'Berkas Notulensi']],
            'assets' => ['title' => 'Bank Aset', 'model' => DesignAsset::class, 'attributes' => ['period_id', 'title', 'category', 'description', 'disk', 'path', 'is_public', 'uploaded_by_user_id'], 'rules' => ['title' => 'required|string|max:255', 'category' => 'required|string|max:80', 'description' => 'nullable|string|max:5000', 'disk' => 'required|in:public,private', 'path' => 'required|string|max:255', 'is_public' => 'nullable|boolean'], 'columns' => ['title' => 'Aset', 'category' => 'Kategori', 'disk' => 'Disk']],
            default => abort(404),
        };
    }
}
