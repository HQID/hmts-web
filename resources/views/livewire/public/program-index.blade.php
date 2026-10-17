<div>
    <section class="border-b border-white/10 px-4 pb-12 pt-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="mb-4 font-mono text-xs text-brand-yellow">Kalender Proker</p>
            <h1 class="mb-3 font-display text-3xl font-black uppercase text-white sm:text-5xl">Agenda & <span class="text-brand-yellow">Program Kerja</span></h1>
            <p class="max-w-xl text-sm text-zinc-300">Jadwal kegiatan ilmiah, pelatihan rekayasa, kompetisi, dan pengabdian masyarakat pada periode aktif.</p>
        </div>
    </section>
    <section class="border-b border-white/5 bg-[#0D0D12]/70 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-wrap gap-2">
            @foreach($categories as $key => $label)
                <button type="button" wire:click="setCategory('{{ $key }}')" aria-pressed="{{ $selectedCategory === $key ? 'true' : 'false' }}" class="rounded-full px-4 py-2 font-mono text-xs {{ $selectedCategory === $key ? 'bg-brand-orange font-bold text-white' : 'border border-white/10 text-zinc-300 hover:bg-white/5' }}">{{ $label }}</button>
            @endforeach
        </div>
    </section>
    <section class="px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-2">
            @forelse($filteredPrograms as $program)
                <article wire:key="program-{{ $program->id }}" class="clean-card flex flex-col p-6 sm:p-8">
                    <div class="mb-4 flex flex-wrap justify-between gap-2 font-mono text-xs"><span class="text-brand-yellow">{{ $categories[$program->category] ?? $program->category }}</span><span class="rounded-full border border-white/20 px-3 py-1">{{ str($program->status)->headline() }}</span></div>
                    <h2 class="mb-3 font-display text-xl font-bold text-white">{{ $program->title }}</h2>
                    <p class="mb-6 line-clamp-3 text-sm leading-relaxed text-zinc-300">{{ $program->description }}</p>
                    <dl class="mb-6 grid gap-3 border-t border-white/10 pt-4 text-xs">
                        <div><dt class="text-zinc-400">Waktu (WITA)</dt><dd>{{ $program->starts_at?->format('d M Y H:i') ?? 'Belum dijadwalkan' }}@if($program->ends_at) — {{ $program->ends_at->format('d M Y H:i') }}@endif</dd></div>
                        <div><dt class="text-zinc-400">Lokasi</dt><dd>{{ $program->location ?: 'Akan diumumkan' }}</dd></div>
                    </dl>
                    <button type="button" wire:click="openTorModal({{ $program->id }})" class="mt-auto self-start rounded-full bg-brand-orange px-5 py-2.5 font-mono text-xs font-bold text-white hover:bg-brand-orangeHover">Detail kegiatan ↗</button>
                </article>
            @empty
                <p class="clean-card p-12 text-center text-zinc-400 md:col-span-2">Tidak ada program kerja untuk kategori terpilih saat ini.</p>
            @endforelse
        </div>
    </section>
    @if($activeProgram)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" role="dialog" aria-modal="true" aria-labelledby="program-detail-title" x-data x-trap.inert.noscroll="true" @keydown.escape.window="$wire.closeTorModal()">
            <div class="clean-card max-h-[85vh] w-full max-w-lg overflow-y-auto border border-brand-yellow bg-[#121217] p-6 sm:p-8">
                <h2 id="program-detail-title" class="mb-4 font-display text-xl font-bold text-white">{{ $activeProgram->title }}</h2>
                <p class="mb-4 whitespace-pre-line text-sm leading-relaxed text-zinc-300">{{ $activeProgram->description ?: 'Rincian kegiatan akan diumumkan oleh pengurus.' }}</p>
                <p class="text-xs text-zinc-400">{{ $activeProgram->starts_at?->format('d M Y H:i') ?? 'Belum dijadwalkan' }} WITA · {{ $activeProgram->location ?: 'Lokasi belum ditentukan' }}</p>
                <button type="button" wire:click="closeTorModal" class="mt-6 rounded-full border border-white/20 px-5 py-2 text-sm">Tutup</button>
            </div>
        </div>
    @endif
</div>
