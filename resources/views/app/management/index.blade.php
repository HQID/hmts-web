<x-layouts.app>
    @php
        $input = 'rounded-xl border border-white/10 bg-[#181822] px-3 py-2.5 text-white';
    @endphp
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div><p class="font-mono text-xs text-brand-yellow">PERIODE AKTIF</p><h1 class="mt-2 font-display text-3xl font-bold text-white">{{ $definition['title'] }}</h1></div>
        @if($module === 'members')<a href="{{ route('app.exports.members') }}" class="text-brand-yellow underline">Unduh CSV</a>@endif
        @if($module === 'programs')<a href="{{ route('app.operations.index', 'committees') }}" class="text-brand-yellow underline">Kelola panitia</a>@endif
    </div>
    @if(session('status'))<div role="status" class="mb-6 rounded-xl border border-brand-yellow/30 p-3 text-brand-yellow">{{ session('status') }}</div>@endif
    <div class="grid gap-6 xl:grid-cols-[.9fr_1.1fr]">
        <section class="clean-card p-6">
            <h2 class="font-display text-lg font-bold text-white">{{ isset($editing) ? 'Sunting program kerja' : 'Tambah data' }}</h2>
            <form method="POST" action="{{ isset($editing) ? route('app.programs.update', $editing) : route('app.'.$module.'.store') }}" class="mt-5 grid gap-4">
                @csrf
                @isset($editing) @method('PUT') @endisset
                @foreach($definition['rules'] as $field => $rules)
                    @php
                        $label = str($field)->replace('_', ' ')->headline();
                        $value = old($field, isset($editing) ? data_get($editing, $field) : ($field === 'sort_order' ? 0 : ''));
                        $required = in_array('required', explode('|', $rules), true);
                        preg_match('/(?:^|\|)max:(\d+)/', $rules, $maxMatch);
                        $max = $maxMatch[1] ?? null;
                        $options = match($field) {
                            'status' => ['rencana', 'aktif', 'selesai', 'dibatalkan'],
                            'role_type' => ['ketua', 'bph', 'coordinator', 'staff'],
                            'condition' => ['baik', 'rusak_ringan', 'rusak_berat'],
                            'category' => $module === 'programs' ? ['competition', 'workshop', 'community', 'seminar'] : [],
                            default => [],
                        };
                    @endphp
                    <label class="grid gap-2 text-sm text-zinc-300">{{ $label }}
                    @if(in_array($field, ['division_id', 'program_id']))
                        <select name="{{ $field }}" class="{{ $input }}"><option value="">Pilih bila diperlukan</option>@foreach($field === 'division_id' ? $divisions : $programs as $option)<option value="{{ $option->id }}" @selected($value == $option->id)>{{ $option->name ?? $option->title }}</option>@endforeach</select>
                    @elseif($options)
                        <select name="{{ $field }}" class="{{ $input }}">@foreach($options as $option)<option value="{{ $option }}" @selected($value === $option)>{{ str($option)->headline() }}</option>@endforeach</select>
                    @elseif(in_array($field, ['description', 'content', 'participants', 'follow_up', 'disposition_note', 'specification', 'bio']))
                        <textarea name="{{ $field }}" rows="3" @required($required) @if($max) maxlength="{{ $max }}" @endif class="{{ $input }}">{{ $value }}</textarea>
                    @elseif($field === 'is_active')
                        <input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                    @else
                        @php
                            $type = in_array($field, ['starts_at', 'ends_at', 'held_at']) ? 'datetime-local' : (in_array($field, ['total_qty', 'available_qty', 'sort_order']) ? 'number' : 'text');
                            if ($value instanceof \DateTimeInterface) $value = $value->format('Y-m-d\TH:i');
                        @endphp
                        <input type="{{ $type }}" name="{{ $field }}" value="{{ $value }}" @required($required) @if($type === 'number') min="0" max="{{ $max }}" step="1" @elseif($type === 'text' && $max) maxlength="{{ $max }}" @endif class="{{ $input }}">
                        @if($field === 'sort_order')<span class="text-xs text-zinc-500">Opsional; kosong menggunakan urutan 0.</span>@endif
                    @endif
                    </label>
                    @error($field)<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                @endforeach
                <button class="rounded-full bg-brand-orange px-5 py-3 font-semibold text-white hover:bg-brand-orangeHover">Simpan data</button>
                @isset($editing)<a href="{{ route('app.programs.index') }}">Batal</a>@endisset
            </form>
        </section>
        <section class="clean-card overflow-hidden">
            <div class="border-b border-white/10 p-6"><h2 class="font-display text-lg font-bold text-white">Data tercatat</h2></div>
            <div class="overflow-x-auto"><table class="w-full text-left text-sm">
                <thead class="bg-white/[.03] text-zinc-400"><tr>@foreach($definition['columns'] as $label)<th class="px-5 py-3">{{ $label }}</th>@endforeach @if($module === 'programs')<th class="px-5 py-3">Jadwal & aksi</th>@endif</tr></thead>
                <tbody>@forelse($records as $record)<tr class="border-t border-white/10">@foreach(array_keys($definition['columns']) as $column)<td class="px-5 py-4">{{ data_get($record, $column) }}</td>@endforeach
                    @if($module === 'programs')<td class="px-5 py-4"><p class="mb-2 text-xs">{{ $record->starts_at?->format('d M Y H:i') ?? 'Belum dijadwalkan' }}</p><a href="{{ route('app.programs.edit', $record) }}" class="text-brand-yellow underline">Sunting</a></td>@endif
                </tr>@empty<tr><td colspan="{{ count($definition['columns']) + 1 }}" class="px-5 py-10 text-center text-zinc-400">Belum ada data pada periode ini.</td></tr>@endforelse</tbody>
            </table></div><div class="p-5">{{ $records->links() }}</div>
        </section>
    </div>
</x-layouts.app>
