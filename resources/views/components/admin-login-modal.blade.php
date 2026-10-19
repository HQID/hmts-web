<div x-data="{ open: @js($errors->getBag('login')->any()) }"
     x-init="if (open) $nextTick(() => $refs.loginEmail.focus())"
     @keydown.escape.window="open = false"
     @click.self="open = false"
     role="dialog" aria-modal="true" aria-labelledby="login-modal-title"
     x-on:open-login-modal.window="open = true" 
     x-show="open" 
     x-trap.inert.noscroll="open"
     x-cloak
     class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="open = false" 
         class="clean-card max-w-md w-full p-6 sm:p-8 relative border border-white/20 shadow-2xl bg-[#121217]"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Close Button -->
        <button type="button" aria-label="Tutup login"
                @click="open = false" 
                class="absolute top-4 right-4 text-zinc-400 hover:text-white font-mono text-sm border border-white/20 w-8 h-8 rounded-full flex items-center justify-center cursor-pointer">
            ✕
        </button>

        <!-- Header Modal -->
        <div class="text-center pb-3 mb-5 border-b border-white/10">
            <img src="{{ asset('images/hmts.png') }}" alt="Logo HMTS FT-UNTAD" class="w-12 h-12 object-contain mx-auto mb-2 drop-shadow">
            <h3 id="login-modal-title" class="font-display font-bold text-xl text-white uppercase">Login Pengurus</h3>
            <p class="text-xs text-zinc-400 mt-1 font-mono">Portal Akses Pengurus HMTS FT-UNTAD</p>
        </div>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4 font-mono text-xs">
            @csrf

            <div>
                <label for="login-email" class="block text-zinc-300 font-semibold mb-1 uppercase">Email Pengurus</label>
                <input id="login-email" 
                       x-ref="loginEmail"
                       type="email" 
                       name="email" 
                       required 
                       value="{{ $errors->getBag('login')->any() ? old('email') : '' }}"
                       placeholder="pengurus@hmts-untad.ac.id" 
                       class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange focus:ring-1 focus:ring-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                @error('email', 'login')
                    <span class="text-red-400 text-[11px] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="login-password" class="block text-zinc-300 font-semibold mb-1 uppercase">Kata Sandi</label>
                <input id="login-password" 
                       type="password" 
                       name="password" 
                       required 
                       placeholder="••••••••" 
                       class="w-full bg-[#181822] border border-white/10 focus:border-brand-orange focus:ring-1 focus:ring-brand-orange rounded-xl px-4 py-2.5 text-white outline-none">
                @error('password', 'login')
                    <span class="text-red-400 text-[11px] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between text-[11px]">
                <label class="flex items-center gap-2 text-zinc-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-[#181822] border-white/10 text-brand-orange focus:ring-brand-orange">
                    <span>Ingat Saya</span>
                </label>
                <span class="text-zinc-500 font-sans">Dual-Role: Superadmin & Admin</span>
            </div>

            <button type="submit" 
                    class="w-full bg-brand-orange hover:bg-brand-orangeHover text-white py-3 rounded-full font-bold uppercase tracking-wider transition-all shadow-md shadow-brand-orange/20 mt-2 cursor-pointer">
                Masuk ke Sistem ↗
            </button>
        </form>
    </div>
</div>
