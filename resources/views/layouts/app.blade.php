<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Command Center HMTS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ambient-canvas min-h-[100dvh] font-sans text-zinc-200">
    <div class="min-h-[100dvh] lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="border-b border-white/10 bg-[#0d0d12]/90 p-5 backdrop-blur-xl lg:min-h-[100dvh] lg:border-r lg:border-b-0">
            <a href="{{ route('app.dashboard') }}" class="mb-8 flex items-center gap-3">
                <img src="{{ asset('images/hmts.png') }}" alt="HMTS FT-UNTAD" class="h-10 w-10 object-contain">
                <span class="font-display text-sm font-bold text-white">COMMAND CENTER</span>
            </a>
            <nav class="grid gap-1 text-sm">
                @foreach (['dashboard' => ['Ringkasan', 'dashboard'], 'members' => ['Kepengurusan', 'members.index'], 'programs' => ['Program Kerja', 'programs.index'], 'attendance' => ['Presensi QR', 'attendance.index'], 'borrowings' => ['Inventaris & Peminjaman', 'borrowings.index'], 'cash' => ['Kas & Kuitansi', 'cash.index'], 'letters' => ['Surat & Disposisi', 'letters.index'], 'meeting' => ['Notulensi', 'meeting-notes.index'], 'aspiration' => ['Aspirasi', 'aspirations.index'], 'assets' => ['Bank Aset', 'assets.index']] as $key => [$label, $route])
                    <a href="{{ route('app.'.$route) }}" class="rounded-xl px-3 py-2.5 transition {{ request()->routeIs('app.'.$route) ? 'bg-brand-orange font-semibold text-white' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="mt-8">@csrf<button class="w-full rounded-xl border border-white/10 px-3 py-2.5 text-left text-sm text-zinc-400 hover:border-white/20 hover:text-white">Keluar</button></form>
        </aside>
        <main class="p-5 sm:p-8 lg:p-10">{{ $slot }}</main>
    </div>
</body>
</html>
