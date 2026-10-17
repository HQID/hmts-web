# Laporan Penutupan Sprint 2 - 17 Oktober 2026

## Ringkasan Penutupan Sprint 2

Implementasi Sprint 2 mengikuti prinsip **Zero-Redis Monolith** dan spesifikasi **PRD v6.0**, berbasis Laravel, MySQL, dan Livewire tanpa ketergantungan pada Redis eksternal. Judul penutupan ini mencatat penyelesaian implementasi, bukan jaminan bebas bug atau bukti kesiapan produksi. Batas verifikasi dicatat di bawah:

### 1. Presensi QR Dinamis (M2) — Zero-Redis
- **Mekanisme Token:** Menggunakan token numerik 6-digit dinamis berumur 30 detik (`token_expires_at`) dengan hash aman (`token_hash`) yang tersimpan langsung pada tabel `attendance_sessions` di MySQL melalui `GenerateDynamicQrTokenAction`.
- **Validasi & Kehadiran:** Divalidasi langsung oleh `RecordAttendanceAction` menggunakan `Hash::check()` terhadap database MySQL dengan constraint keunikan `['attendance_session_id', 'nim']`.
- **Layar Tampilan Proyektor:** Komponen Livewire `App\Livewire\Attendance\QrSessionDisplay` (`/app/presensi/qr?sessionId=X`) menyediakan kode QR resolusi tinggi (SVG) untuk proyektor, angka 6-digit besar, progress bar waktu mundur 30 detik, polling Livewire terintegrasi, dan daftar kehadiran langsung (real-time live feed).
- **Pemindai Kamera:** Modul `html5-qrcode` tersedia pada layar operator. Integrasi kamera mandiri di ServiceHub publik belum lengkap; izin kamera, pembacaan QR, dan perilaku mobile belum diverifikasi pada browser/perangkat nyata.

### 2. Peminjaman Alat Lab Bebas Bentrok (M7 & M8)
- **Lifecycle & State Machine:** Ditegakkan melalui `Borrowing::allowedStatuses()` (`menunggu` -> `disetujui`/`ditolak` -> `dipinjam` -> `dikembalikan`).
- **Pencegahan Race Condition:** Dijalankan dalam `DB::transaction()` dengan pessimistic locking `lockForUpdate()` pada record peminjaman dan baris inventaris terkait di `UpdateBorrowingStatusAction`.
- **Pemeriksaan Bentrok:** `CheckBorrowingConflictAction` memeriksa tumpang tindih rentang waktu peminjaman aktif (`disetujui`, `dipinjam`, `sedang_dipinjam`) terhadap total kuantitas inventaris.

### 3. Kotak Aspirasi Anonim & Pelacak Tiket (M11)
- **Perlindungan Anti-Spam:** Menerapkan *Native Honeypot* (`$aspirationHoneypot`) tanpa captcha berbayar/eksternal.
- **Rate Limiting:** Menggunakan pembatas laju bawaan `RateLimiter` Laravel (maksimal 5 kiriman per 10 menit per IP).
- **Format Tiket:** Dihasilkan secara unik dengan format `ASP-YYYY-XXXXXX` melalui `SubmitAspirationAction` dan dapat dilacak status serta respon pengurusnya di tab Layanan Mahasiswa.

### 4. Manajemen Periode Multi-Tenancy
- **Invalidasi Cache Otomatis:** Hook model Eloquent `Period::saved()` dan `Period::deleted()` serta `cache()->forget('active_period_id')` memastikan perpindahan periode aktif langsung tersegarkan di seluruh query `ActivePeriodScope` dan `Period::current()`.

### 5. Pengujian & Verifikasi Fitur
- Pengujian otomatis pada `tests/Feature/OperationsFlowTest.php`:
  - `test_admin_can_access_qr_session_display`
  - `test_dynamic_qr_token_generation_and_attendance_recording`
  - `test_borrowing_status_transition_lifecycle`
  - `test_period_cache_is_invalidated_when_switching_active_period`
  - `test_aspiration_honeypot_prevents_spam_storage`

### Koreksi verifikasi — 17 Oktober 2026

- Klaim audit berhasil pada percakapan sebelumnya ketika eksekusi perintah terblokir tidak menjadi bukti pengujian.
- Baseline yang benar-benar dijalankan saat memulai Sprint 3: `php artisan test` **17 tests, 51 assertions, lulus** (SQLite in-memory).
- Setelah perubahan Sprint 3 dan penambahan tes: **32 tests, 193 assertions, lulus**; `npm run build` dan kompilasi Blade juga berhasil. Rincian pada `sprint3_17-10-2026.md`.
- MySQL row-lock/concurrency, kamera QR perangkat nyata, countdown/polling browser, dan deployment VPS belum diverifikasi. Tidak ada klaim bahwa seluruh jalur tersebut bebas bug. Database MySQL lokal/produksi tidak dipakai untuk RefreshDatabase.
