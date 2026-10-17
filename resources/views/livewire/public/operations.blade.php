<x-layouts.app>
    @php
        $input = 'rounded-xl border border-white/10 bg-[#181822] px-3.5 py-2.5 text-sm text-white focus:border-brand-yellow focus:outline-none transition-colors w-full';

        $fieldLabels = [
            'title' => match($module) {
                'assets' => 'Nama / Judul Aset',
                'attendance' => 'Nama Sesi Presensi',
                'letters' => 'Perihal / Judul',
                default => 'Judul',
            },
            'category' => 'Kategori Aset',
            'description' => 'Deskripsi / Keterangan',
            'is_public' => 'Publikasikan untuk Umum (Aset Terbuka)',
            'file' => 'Pilih Berkas Dokumen',
            'agenda_number' => 'Nomor Agenda Surat',
            'letter_number' => 'Nomor Surat Resmi',
            'type' => 'Jenis Surat',
            'subject' => 'Perihal / Subjek Surat',
            'sender_recipient' => 'Asal Pengirim / Tujuan Penerima',
            'received_or_sent_on' => 'Tanggal Surat Masuk / Keluar',
            'disposition_note' => 'Catatan Disposisi',
            'status' => 'Status Operasional',
            'starts_at' => 'Waktu Mulai',
            'ends_at' => 'Waktu Selesai',
            'location' => 'Lokasi / Ruangan',
            'name' => 'Nama Lengkap',
            'nim' => 'NIM (Nomor Induk Mahasiswa)',
            'position' => 'Jabatan / Posisi Panitia',
            'program_id' => 'Program Kerja Terkait',
            'member_id' => 'Pengurus HMTS (Opsional)',
            'task_status' => 'Status Pelaksanaan Tugas',
            'start_year' => 'Tahun Mulai Periode',
            'end_year' => 'Tahun Selesai Periode',
            'is_active' => 'Status Periode Aktif',
            'email' => 'Alamat Email',
            'password' => 'Kata Sandi Akun (Minimal 12 Karakter)',
            'role' => 'Hak Akses / Peran Akun',
        ];

        $optionLabels = [
            'masuk' => 'Surat Masuk',
            'keluar' => 'Surat Keluar',
            'terdaftar' => 'Terdaftar',
            'didisposisikan' => 'Didisposisikan',
            'selesai' => 'Selesai',
            'draft' => 'Draf (Belum Aktif)',
            'aktif' => 'Aktif',
            'belum_mulai' => 'Belum Dimulai',
            'berjalan' => 'Sedang Berjalan',
            'admin' => 'Admin Divisi',
            'superadmin' => 'Super Admin (BPH)',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dipinjam' => 'Sedang Dipinjam',
            'dikembalikan' => 'Sudah Dikembalikan',
            'ditinjau' => 'Ditinjau',
            'ditanggapi' => 'Ditanggapi',
            'ditutup' => 'Ditutup',
        ];
    @endphp

    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-mono text-xs text-brand-yellow font-semibold tracking-wider uppercase">Layanan Operasional</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">{{ $definition['title'] }}</h1>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-5 rounded-xl border border-brand-yellow/30 bg-brand-yellow/10 p-3.5 text-sm text-brand-yellow flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-brand-yellow"></span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-400/30 bg-red-400/10 p-3.5 text-sm text-red-200 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-red-400"></span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="grid gap-6 {{ empty($definition['rules']) ? '' : 'xl:grid-cols-[.8fr_1.2fr]' }}">
        @if(!empty($definition['rules']))
        <section class="clean-card p-6">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-brand-orange"></span>
                <span class="font-mono text-xs text-brand-orange font-semibold uppercase tracking-wider">Entri Data</span>
            </div>
            <h2 class="font-display text-lg font-bold text-white">Tambah Data Baru</h2>

            <form method="POST" enctype="multipart/form-data" action="{{ route('app.operations.store', $module) }}" class="mt-5 grid gap-4">
                @csrf
                @foreach($definition['rules'] as $field => $rules)
                    @php
                        $label = $fieldLabels[$field] ?? str($field)->replace('_',' ')->headline();
                        $required = in_array('required', explode('|', $rules), true);
                        $value = $field === 'password' ? '' : old($field, '');
                        preg_match('/(?:^|\|)max:(\d+)/', $rules, $maxMatch);
                        $max = $maxMatch[1] ?? null;
                    @endphp

                    @if($field === 'program_id')
                        <label class="grid gap-2 text-sm text-zinc-300">
                            <span>{{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif</span>
                            <select @required($required) name="program_id" class="{{ $input }}">
                                <option value="">Pilih Program Kerja Terkait</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" @selected($value == $program->id)>{{ $program->title }}</option>
                                @endforeach
                            </select>
                        </label>
                    @elseif($field === 'member_id')
                        <label class="grid gap-2 text-sm text-zinc-300">
                            <span>{{ $label }}</span>
                            <select name="member_id" class="{{ $input }}">
                                <option value="">Panitia Eksternal (Bukan Pengurus)</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" @selected($value == $member->id)>{{ $member->name }}{{ $member->nim ? ' — '.$member->nim : '' }}</option>
                                @endforeach
                            </select>
                        </label>
                    @elseif($field === 'file')
                        <label class="grid gap-2 text-sm text-zinc-300">
                            <span>{{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif</span>
                            <input @required($required) type="file" name="file" class="{{ $input }} cursor-pointer file:mr-3.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-yellow/15 file:text-brand-yellow hover:file:bg-brand-yellow/25 file:cursor-pointer">
                            <span class="text-xs text-zinc-500 font-mono">
                                @if($module === 'assets') Format: PDF, PNG, JPG, JPEG, WEBP, ZIP. Maksimal 20 MB.
                                @elseif($module === 'letters') Format: PDF, DOC, DOCX. Maksimal 10 MB.
                                @else Maksimal ukuran berkas 10 MB.
                                @endif
                            </span>
                        </label>
                    @elseif(in_array($field, ['description','disposition_note']))
                        <label class="grid gap-2 text-sm text-zinc-300">
                            <span>{{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif</span>
                            <textarea name="{{ $field }}" rows="3" maxlength="{{ $max }}" placeholder="Tuliskan keterangan lengkap..." class="{{ $input }}">{{ $value }}</textarea>
                        </label>
                    @elseif(in_array($field, ['status','type','task_status','role']))
                        @php
                            $options = [
                                'status' => $module === 'letters' ? ['terdaftar','didisposisikan','selesai'] : ['draft','aktif','selesai'],
                                'type' => ['masuk','keluar'],
                                'task_status' => ['belum_mulai','berjalan','selesai'],
                                'role' => ['admin','superadmin']
                            ][$field];
                        @endphp
                        <label class="grid gap-2 text-sm text-zinc-300">
                            <span>{{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif</span>
                            <select @required($required) name="{{ $field }}" class="{{ $input }}">
                                @foreach($options as $option)
                                    <option value="{{ $option }}" @selected($value === $option)>{{ $optionLabels[$option] ?? str($option)->headline() }}</option>
                                @endforeach
                            </select>
                        </label>
                    @elseif($field === 'is_active' || $field === 'is_public')
                        <label class="flex items-center gap-2.5 text-sm text-zinc-300 cursor-pointer pt-1">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, false)) class="w-4 h-4 rounded border-white/20 bg-[#181822] text-brand-yellow focus:ring-brand-yellow cursor-pointer">
                            <span>{{ $label }}</span>
                        </label>
                    @elseif(in_array($field, ['starts_at', 'ends_at']))
                        @php
                            $dateVal = '';
                            $timeVal = '';
                            if ($value instanceof \DateTimeInterface) {
                                $dateVal = $value->format('Y-m-d');
                                $timeVal = $value->format('H:i');
                            } elseif (is_string($value) && !empty($value)) {
                                $raw = str_replace('T', ' ', $value);
                                $parts = explode(' ', $raw);
                                $dateVal = $parts[0] ?? '';
                                $timeVal = isset($parts[1]) ? substr($parts[1], 0, 5) : '';
                            }
                            if (empty($dateVal) && $required) {
                                $dateVal = now()->format('Y-m-d');
                            }
                            if (empty($timeVal) && $required) {
                                $timeVal = $field === 'starts_at' ? '08:00' : '17:00';
                            }
                        @endphp
                        <div class="grid gap-2 text-sm text-zinc-300" data-datetime-picker-group>
                            <span>{{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0 w-full">
                                <label class="flex flex-col gap-1.5 text-xs text-zinc-400 min-w-0 w-full">
                                    <span class="flex items-center gap-1.5 font-medium text-zinc-300">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-brand-yellow"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        Pilih Tanggal
                                    </span>
                                    <input type="date" 
                                           name="{{ $field }}_date" 
                                           value="{{ old($field . '_date', $dateVal) }}" 
                                           @required($required)
                                           onclick="this.showPicker()"
                                           class="{{ $input }} [color-scheme:dark] cursor-pointer focus:border-brand-yellow transition-colors dt-date-input">
                                </label>
                                <label class="flex flex-col gap-1.5 text-xs text-zinc-400 min-w-0 w-full">
                                    <span class="flex items-center gap-1.5 font-medium text-zinc-300">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-brand-yellow"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        Pilih Waktu (WITA)
                                    </span>
                                    <input type="time" 
                                           name="{{ $field }}_time" 
                                           value="{{ old($field . '_time', $timeVal) }}" 
                                           @required($required)
                                           onclick="this.showPicker()"
                                           class="{{ $input }} [color-scheme:dark] cursor-pointer focus:border-brand-yellow transition-colors dt-time-input">
                                </label>
                            </div>
                            <input type="hidden" name="{{ $field }}" value="{{ old($field, ($dateVal && $timeVal) ? $dateVal . ' ' . $timeVal : '') }}" class="dt-hidden-input">
                        </div>
                    @elseif(str_contains($field, 'date') || str_contains($field, '_on'))
                        <label class="grid gap-2 text-sm text-zinc-300">
                            <span class="flex items-center gap-1.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-brand-yellow"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                {{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif
                            </span>
                            <input @required($required) 
                                   type="date" 
                                   name="{{ $field }}" 
                                   value="{{ $value ?: now()->format('Y-m-d') }}" 
                                   onclick="this.showPicker()"
                                   class="{{ $input }} [color-scheme:dark] cursor-pointer">
                        </label>
                    @else
                        @php
                            $type = in_array($field, ['start_year', 'end_year']) ? 'number' : ($field === 'password' ? 'password' : ($field === 'email' ? 'email' : 'text'));
                        @endphp
                        <label class="grid gap-2 text-sm text-zinc-300">
                            <span>{{ $label }} @if($required)<strong class="text-brand-yellow">*</strong>@endif</span>
                            <input @required($required) 
                                   type="{{ $type }}" 
                                   name="{{ $field }}" 
                                   value="{{ $value }}" 
                                   @if($type === 'number') min="2020" max="65535" step="1" @elseif($max) maxlength="{{ $max }}" @endif 
                                   class="{{ $input }}">
                        </label>
                    @endif
                    @error($field)<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                @endforeach
                <button class="mt-2 rounded-full bg-brand-orange px-6 py-3 font-semibold text-white hover:bg-brand-orangeHover shadow-lg transition-colors cursor-pointer">Simpan Data Baru</button>
            </form>

            <script>
                document.querySelectorAll('[data-datetime-picker-group]').forEach(group => {
                    const dateInput = group.querySelector('.dt-date-input');
                    const timeInput = group.querySelector('.dt-time-input');
                    const hiddenInput = group.querySelector('.dt-hidden-input');
                    const update = () => {
                        if (dateInput.value) {
                            hiddenInput.value = dateInput.value + ' ' + (timeInput.value || '00:00');
                        } else {
                            hiddenInput.value = '';
                        }
                    };
                    dateInput.addEventListener('change', update);
                    timeInput.addEventListener('change', update);
                });
            </script>
        </section>
        @endif

        <section class="clean-card overflow-hidden">
            <div class="border-b border-white/10 p-6">
                <h2 class="font-display text-lg font-bold text-white">Data Tercatat</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/[.03] text-zinc-400 font-mono text-xs uppercase">
                        <tr>
                            @foreach($definition['columns'] as $label)
                                <th class="px-4 py-3.5 font-medium">{{ $label }}</th>
                            @endforeach
                            @if(in_array($module,['borrowings','aspirations','attendance','receipts','letters','assets','committees']))
                                <th class="px-4 py-3.5">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr class="border-t border-white/[.06] hover:bg-white/[0.02] transition-colors">
                                @foreach(array_keys($definition['columns']) as $column)
                                    <td class="px-4 py-4 text-zinc-200">
                                        @if($column === 'amount')
                                            Rp {{ number_format($record->$column, 0, ',', '.') }}
                                        @elseif($column === 'is_active')
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-mono {{ $record->$column ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-zinc-500/10 text-zinc-400 border border-white/10' }}">
                                                {{ $record->$column ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        @elseif(in_array($column, ['status', 'type', 'task_status', 'role']))
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-mono bg-white/5 border border-white/10 text-zinc-300">
                                                {{ $optionLabels[$record->$column] ?? str($record->$column)->headline() }}
                                            </span>
                                        @elseif(data_get($record, $column) instanceof \DateTimeInterface)
                                            <span class="font-mono text-xs text-zinc-300">{{ data_get($record, $column)->format('d M Y H:i') }}</span>
                                        @else
                                            {{ data_get($record, $column) }}
                                        @endif
                                    </td>
                                @endforeach

                                @if($module === 'borrowings')
                                    <td class="min-w-72 px-4 py-4">
                                        <p class="mb-2 text-xs text-zinc-500">{{ $record->items->map(fn($item) => $item->inventory->name.' ×'.$item->qty)->join(', ') }}</p>
                                        <form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">
                                            @csrf
                                            <select name="status" class="{{ $input }}">
                                                @foreach(['disetujui','ditolak','dipinjam','dikembalikan'] as $option)
                                                    <option value="{{ $option }}">{{ $optionLabels[$option] ?? str($option)->headline() }}</option>
                                                @endforeach
                                            </select>
                                            <input name="admin_note" placeholder="Catatan pengurus / admin..." class="{{ $input }}">
                                            <button class="rounded-full border border-brand-yellow/50 px-3 py-1.5 text-xs text-brand-yellow hover:bg-brand-yellow hover:text-black transition-colors cursor-pointer">Simpan Status</button>
                                        </form>
                                    </td>
                                @elseif($module === 'committees')
                                    <td class="px-4 py-4">
                                        <p class="mb-2 text-xs text-zinc-400">{{ $record->program?->title }}</p>
                                        <form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">
                                            @csrf
                                            <label class="text-xs text-zinc-400">
                                                <span>Status Tugas</span>
                                                <select name="task_status" class="{{ $input }} mt-1">
                                                    @foreach(['belum_mulai','berjalan','selesai'] as $option)
                                                        <option value="{{ $option }}" @selected($record->task_status === $option)>{{ $optionLabels[$option] ?? str($option)->headline() }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <button class="rounded-full border border-brand-yellow/50 px-3 py-1.5 text-xs text-brand-yellow hover:bg-brand-yellow hover:text-black transition-colors cursor-pointer">Simpan Status Tugas</button>
                                        </form>
                                    </td>
                                @elseif($module === 'aspirations')
                                    <td class="min-w-72 px-4 py-4">
                                        <p class="mb-2 max-w-sm text-xs text-zinc-400">{{ $record->message }}</p>
                                        <form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">
                                            @csrf
                                            <select name="status" class="{{ $input }}">
                                                <option value="ditinjau">Ditinjau</option>
                                                <option value="ditanggapi">Ditanggapi</option>
                                                <option value="ditutup">Ditutup</option>
                                            </select>
                                            <textarea name="response" rows="2" placeholder="Tanggapan resmi pengurus (wajib bila ditanggapi)..." class="{{ $input }}"></textarea>
                                            <button class="rounded-full border border-brand-yellow/50 px-3 py-1.5 text-xs text-brand-yellow hover:bg-brand-yellow hover:text-black transition-colors cursor-pointer">Publikasikan Tanggapan</button>
                                        </form>
                                    </td>
                                @elseif($module === 'attendance')
                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('app.operations.operate', [$module, $record->id]) }}">
                                            @csrf
                                            <button class="rounded-full border border-brand-yellow/50 px-3.5 py-1.5 text-xs text-brand-yellow hover:bg-brand-yellow hover:text-black transition-colors cursor-pointer">Buat Token QR Baru</button>
                                        </form>
                                        <p class="mt-2 text-xs font-mono text-zinc-500">{{ $record->attendances_count }} peserta hadir</p>
                                    </td>
                                @elseif($module === 'receipts')
                                    <td class="min-w-52 px-4 py-4">
                                        <form method="POST" enctype="multipart/form-data" action="{{ route('app.operations.operate', [$module, $record->id]) }}" class="grid gap-2">
                                            @csrf
                                            <input required type="file" name="file" class="{{ $input }}">
                                            <button class="rounded-full border border-brand-yellow/50 px-3 py-1.5 text-xs text-brand-yellow hover:bg-brand-yellow hover:text-black transition-colors cursor-pointer">Unggah Kuitansi Privat</button>
                                        </form>
                                        @foreach($record->receipts as $receipt)
                                            <a class="mt-2 block text-xs text-brand-yellow underline" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'receipts', 'id' => $receipt->id]) }}">{{ $receipt->original_name }}</a>
                                        @endforeach
                                    </td>
                                @elseif($module === 'letters' && $record->file_path)
                                    <td class="px-4 py-4">
                                        <a class="text-xs text-brand-yellow hover:underline inline-flex items-center gap-1 font-semibold" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'letters', 'id' => $record->id]) }}">
                                            <span>Unduh Dokumen Surat</span>
                                            <span>↗</span>
                                        </a>
                                    </td>
                                @elseif($module === 'assets')
                                    <td class="px-4 py-4">
                                        <a class="text-xs text-brand-yellow hover:underline inline-flex items-center gap-1 font-semibold" href="{{ $record->is_public ? route('assets.download', $record) : \Illuminate\Support\Facades\URL::temporarySignedRoute('app.operations.download', now()->addMinutes(15), ['module' => 'assets', 'id' => $record->id]) }}">
                                            <span>{{ $record->is_public ? 'Unduh Publik' : 'Unduh Privat' }}</span>
                                            <span>↗</span>
                                        </a>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($definition['columns']) + 1 }}" class="px-5 py-10 text-center text-zinc-500">Belum ada data tercatat pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-5">{{ $records->links() }}</div>
        </section>
    </div>
</x-layouts.app>
