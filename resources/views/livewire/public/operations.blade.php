<x-layouts.app>
    @php
        $input = 'rounded-xl border border-white/10 bg-[#181822] px-3 py-2.5 text-sm text-white';
    @endphp
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="font-mono text-xs text-brand-yellow">LAYANAN OPERASIONAL</p><h1 class="mt-2 font-display text-3xl font-bold text-white">{{ $definition['title'] }}</h1></div>
        <div class="flex flex-wrap gap-2 text-xs font-mono">
            @foreach(['borrowings'=>'Peminjaman','aspirations'=>'Aspirasi','attendance'=>'Presensi QR','letters'=>'Surat','assets'=>'Aset','committees'=>'Panitia','receipts'=>'Kuitansi'] as $key=>$label)
                <a href="{{ route('app.operations.index', $key) }}" class="rounded-full border px-3 py-2 {{ $module === $key ? 'border-brand-orange bg-brand-orange text-white' : 'border-white/10 text-zinc-400 hover:text-white' }}">{{ $label }}</a>
            @endforeach
            @if(auth()->user()->isSuperAdmin())<a href="{{ route('app.operations.index', 'periods') }}" class="rounded-full border border-brand-yellow/40 px-3 py-2 text-brand-yellow">Periode</a><a href="{{ route('app.operations.index', 'admins') }}" class="rounded-full border border-brand-yellow/40 px-3 py-2 text-brand-yellow">Admin</a>@endif
        </div>
    </div>
    @if(session('status'))<div class="mb-5 rounded-xl border border-brand-yellow/30 bg-brand-yellow/10 p-3 text-sm text-brand-yellow">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="mb-5 rounded-xl border border-red-400/30 bg-red-400/10 p-3 text-sm text-red-200">{{ $errors->first() }}</div>@endif

    <div class="grid gap-6 {{ empty($definition['rules']) ? '' : 'xl:grid-cols-[.8fr_1.2fr]' }}">
        @if(!empty($definition['rules']))
        <section class="clean-card p-6"><h2 class="font-display text-lg font-bold text-white">Tambah data</h2>
            <form method="POST" enctype="multipart/form-data" action="{{ route('app.operations.store', $module) }}" class="mt-5 grid gap-4">@csrf
                @foreach($definition['rules'] as $field => $rules)
                    @php
                        $label = str($field)->replace('_',' ')->headline();
                        $required = in_array('required', explode('|', $rules), true);
                        $value = $field === 'password' ? '' : old($field, '');
                        preg_match('/(?:^|\|)max:(\d+)/', $rules, $maxMatch);
                        $max = $maxMatch[1] ?? null;
                    @endphp
                    @if($field === 'program_id')<label class="grid gap-2 text-sm text-zinc-300">Program<select @required($required) name="program_id" class="{{ $input }}"><option value="">Pilih program</option>@foreach($programs as $program)<option value="{{ $program->id }}" @selected($value == $program->id)>{{ $program->title }}</option>@endforeach</select></label>
                    @elseif($field === 'member_id')<label class="grid gap-2 text-sm text-zinc-300">Anggota<select name="member_id" class="{{ $input }}"><option value="">Panitia eksternal</option>@foreach($members as $member)<option value="{{ $member->id }}" @selected($value == $member->id)>{{ $member->name }}{{ $member->nim ? ' — '.$member->nim : '' }}</option>@endforeach</select></label>
                    @elseif($field === 'file')<label class="grid gap-2 text-sm text-zinc-300">Berkas<input @required($required) type="file" name="file" class="{{ $input }}"></label>
                    @elseif(in_array($field, ['description','disposition_note']))<label class="grid gap-2 text-sm text-zinc-300">{{ $label }}<textarea name="{{ $field }}" rows="3" maxlength="{{ $max }}" class="{{ $input }}">{{ $value }}</textarea></label>
                    @elseif(in_array($field, ['status','type','task_status','role']))
                        @php
                            $options = ['status' => $module === 'letters' ? ['terdaftar','didisposisikan','selesai'] : ['draft','aktif','selesai'], 'type' => ['masuk','keluar'], 'task_status' => ['belum_mulai','berjalan','selesai'], 'role' => ['admin','superadmin']][$field];
                        @endphp
                        <label class="grid gap-2 text-sm text-zinc-300">{{ $label }}<select @required($required) name="{{ $field }}" class="{{ $input }}">@foreach($options as $option)<option value="{{ $option }}" @selected($value === $option)>{{ str($option)->headline() }}</option>@endforeach</select></label>
                    @elseif($field === 'is_active' || $field === 'is_public')<label class="flex items-center gap-2 text-sm text-zinc-300"><input type="hidden" name="{{ $field }}" value="0"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, false))> {{ $label }}</label>
                    @else
                        @php
                            $type = in_array($field,['starts_at','ends_at']) ? 'datetime-local' : (str_contains($field,'date') || str_contains($field,'_on') ? 'date' : (in_array($field,['start_year','end_year']) ? 'number' : ($field === 'password' ? 'password' : ($field === 'email' ? 'email' : 'text'))));
                        @endphp
                        <label class="grid gap-2 text-sm text-zinc-300">{{ $label }}<input @required($required) type="{{ $type }}" name="{{ $field }}" value="{{ $value }}" @if($type === 'number') min="2020" max="65535" step="1" @elseif($max) maxlength="{{ $max }}" @endif class="{{ $input }}"></label>
                    @endif
                    @error($field)<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                @endforeach
                <button class="rounded-full bg-brand-orange px-5 py-3 font-semibold text-white hover:bg-brand-orangeHover">Simpan data</button>
            </form>
        </section>
        @endif
        <section class="clean-card overflow-hidden"><div class="border-b border-white/10 p-6"><h2 class="font-display text-lg font-bold text-white">Data tercatat</h2></div><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-white/[.03] text-zinc-400"><tr>@foreach($definition['columns'] as $label)<th class="px-4 py-3 font-medium">{{ $label }}</th>@endforeach@if(in_array($module,['borrowings','aspirations','attendance','receipts','letters','assets','committees']))<th class="px-4 py-3">Aksi</th>@endif</tr></thead><tbody>
            @forelse($records as $record)<tr class="border-t border-white/[.06]">@foreach(array_keys($definition['columns']) as $column)<td class="px-4 py-4 text-zinc-200">@if($column === 'amount')Rp {{ number_format($record->$column, 0, ',', '.') }}@elseif($column === 'is_active'){{ $record->$column ? 'Ya' : 'Tidak' }}@else{{ data_get($record, $column) }}@endif</td>@endforeach
            @if($module === 'borrowings')<td class="min-w-72 px-4 py-4"><p class="mb-2 text-xs text-zinc-500">{{ $record->items->map(fn($item) => $item->inventory->name.' ×'.$item->qty)->join(', ') }}</p><form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">@csrf<select name="status" class="{{ $input }}">@foreach(['disetujui','ditolak','dipinjam','dikembalikan'] as $option)<option value="{{ $option }}">{{ str($option)->headline() }}</option>@endforeach</select><input name="admin_note" placeholder="Catatan admin" class="{{ $input }}"><button class="rounded-full border border-brand-yellow/50 px-3 py-2 text-xs text-brand-yellow">Simpan</button></form></td>
            @elseif($module === 'committees')<td class="px-4 py-4"><p class="mb-2 text-xs text-zinc-400">{{ $record->program?->title }}</p><form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">@csrf<label>Status tugas<select name="task_status" class="{{ $input }}">@foreach(['belum_mulai','berjalan','selesai'] as $option)<option value="{{ $option }}" @selected($record->task_status === $option)>{{ str($option)->headline() }}</option>@endforeach</select></label><button class="rounded-full border border-brand-yellow/50 px-3 py-2 text-xs text-brand-yellow">Simpan status tugas</button></form></td>
            @elseif($module === 'aspirations')<td class="min-w-72 px-4 py-4"><p class="mb-2 max-w-sm text-xs text-zinc-400">{{ $record->message }}</p><form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">@csrf<select name="status" class="{{ $input }}"><option value="ditinjau">Ditinjau</option><option value="ditanggapi">Ditanggapi</option><option value="ditutup">Ditutup</option></select><textarea name="response" rows="2" placeholder="Respons pengurus (wajib bila ditanggapi)" class="{{ $input }}"></textarea><button class="rounded-full border border-brand-yellow/50 px-3 py-2 text-xs text-brand-yellow">Publikasikan respons</button></form></td>
            @elseif($module === 'attendance')<td class="px-4 py-4"><form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}">@csrf<button class="rounded-full border border-brand-yellow/50 px-3 py-2 text-xs text-brand-yellow">Buat token QR</button></form><p class="mt-2 text-xs text-zinc-500">{{ $record->attendances_count }} hadir</p></td>
            @elseif($module === 'receipts')<td class="min-w-52 px-4 py-4"><form method="POST" enctype="multipart/form-data" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">@csrf<input required type="file" name="file" class="{{ $input }}"><button class="rounded-full border border-brand-yellow/50 px-3 py-2 text-xs text-brand-yellow">Unggah kuitansi privat</button></form>@foreach($record->receipts as $receipt)<a class="mt-2 block text-xs text-brand-yellow underline" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'receipts', 'id' => $receipt->id]) }}">{{ $receipt->original_name }}</a>@endforeach</td>
            @elseif($module === 'letters' && $record->file_path)<td class="px-4 py-4"><a class="text-xs text-brand-yellow underline" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'letters', 'id' => $record->id]) }}">Unduh privat</a></td>
            @elseif($module === 'assets')<td class="px-4 py-4"><a class="text-xs text-brand-yellow underline" href="{{ $record->is_public ? route('assets.download', $record) : \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'assets', 'id' => $record->id]) }}">{{ $record->is_public ? 'Unduh publik' : 'Unduh privat' }}</a></td>@endif
            </tr>@empty<tr><td colspan="{{ count($definition['columns']) + 1 }}" class="px-5 py-10 text-center text-zinc-500">Belum ada data pada periode ini.</td></tr>@endforelse
            </tbody></table></div><div class="p-5">{{ $records->links() }}</div></section>
    </div>
</x-layouts.app>
