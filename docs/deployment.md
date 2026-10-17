# Deploy single VPS — Zero-Redis

Panduan operasional, bukan catatan bahwa deployment sudah dilakukan. Gunakan rilis yang sudah ditinjau; jangan mengunggah `.env`, `.git`, database, atau dokumen privat ke web root.

## Runtime dan persiapan

- Stack aktual: Laravel 13, Livewire 4. Walaupun `composer.json` menyatakan PHP `^8.3`, **lockfile saat ini membutuhkan PHP >=8.4.1** melalui Symfony 8. Gunakan PHP 8.4.1+ yang kompatibel (verifikasi lokal memakai 8.5.11); jangan downgrade mengikuti contoh versi lama di PRD. Jalankan `composer check-platform-reqs --no-dev` pada server tujuan.
- PHP-FPM, Nginx/Apache, ekstensi sesuai Composer (termasuk PDO MySQL, mbstring, XML, fileinfo, GD sesuai pemakaian gambar), MySQL 8+/MariaDB 10.11+, HTTPS.
- Build memakai Vite 8: Node 22.12+ yang kompatibel. Build dapat dilakukan di CI/mesin build, lalu kirim `public/build` beserta manifest ke VPS. Node/dev server tidak perlu berjalan di produksi.
- Document root **hanya** `<release>/public`. Jangan mengekspos root repository atau `storage/app/private`. Tolak akses dotfiles, kecuali `.well-known` untuk sertifikat.
- Akun database khusus aplikasi, bukan root. Hanya `storage` dan `bootstrap/cache` yang writable oleh proses PHP; jangan memakai permission `777`.

Konfigurasi `.env` produksi (isi nilai rahasia sendiri, bukan nilai contoh):

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-organisasi.example
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hmts
DB_USERNAME=hmts_app
DB_PASSWORD=<rahasia>
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true
```

`APP_KEY` dibuat **sekali** pada instalasi baru dan disimpan aman. Jangan menjalankan `key:generate` pada upgrade: sesi, enkripsi, dan signed URL bergantung pada key yang sama. Timezone aplikasi ditetapkan `Asia/Makassar` di `config/app.php`.

## Rilis dan upgrade

1. Backup database, `.env`/key, `storage/app/private`, dan `storage/app/public` ke lokasi terpisah dengan akses terbatas. Uji pemulihan; CSV bukan pengganti backup.
2. Siapkan direktori rilis baru. Pakai `.env` dan storage persisten yang benar; jangan menimpa upload dengan isi repository.
3. Instal dependency dari lockfile dan bangun aset (build di mesin terpisah diperbolehkan):

   ```sh
   composer install --no-dev --prefer-dist --optimize-autoloader
   composer check-platform-reqs --no-dev
   npm ci
   npm run build
   ```

4. Tinjau perubahan migration sebelum eksekusi. Jadwalkan maintenance untuk perubahan yang tidak backward-compatible. Pada target yang sudah benar dan setelah backup:

   ```sh
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

   `storage:link` hanya mengekspos `storage/app/public`; **jangan** membuat link untuk disk private. Pada shared storage, pastikan link menunjuk ke storage persisten yang benar. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder demo di produksi.
5. Arahkan document root/symlink rilis ke versi baru, reload PHP-FPM bila diperlukan. Nonaktifkan maintenance jika sebelumnya diaktifkan. Pastikan cache tidak menyimpan konfigurasi rilis lama.
6. Provision akun superadmin awal melalui prosedur operator tepercaya (console/Tinker), gunakan password unik yang kuat dan hashing Laravel. Tidak ada kredensial awal publik dalam panduan ini. Jangan membuka endpoint registrasi admin publik. Melalui UI superadmin buat periode aktif; melalui `/app/kas` buat akun kas saldo nol, lalu catat saldo awal sebagai pemasukan ledger.

Belum ada job aplikasi yang mengimplementasikan `ShouldQueue` saat pemeriksaan 17 Oktober 2026. Tidak perlu menambah worker/Supervisor hanya untuk pola deployment. Jika job benar-benar ditambahkan, operasikan `queue:work` dengan process manager dan jalankan `queue:restart` setiap rilis. Begitu pula scheduler: aktifkan cron hanya saat ada tugas terjadwal yang diperlukan. Tidak perlu Redis, WebSocket, atau layanan tambahan.

## Smoke test dan batas verifikasi

- `/up`, empat halaman publik, login/logout, redirect tamu dan pembatasan admin/superadmin.
- Periode aktif harus benar. Tanpa periode aktif/terpilih, query operasional kini mengembalikan kosong, bukan membuka seluruh arsip.
- Buat pemasukan/pengeluaran kecil di staging; pastikan saldo, audit void khusus superadmin, penolakan void kedua, CSV dan periode.
- Unggah kuitansi staging; signed URL valid dapat diunduh oleh pengurus, URL tanpa signature/kedaluwarsa ditolak, URL publik tidak mengekspos berkas.
- Uji dua permintaan kas bersamaan di **database MySQL staging terpisah**. Suite SQLite in-memory tidak membuktikan perilaku `lockForUpdate()` MySQL.
- Uji kamera QR pada perangkat nyata melalui HTTPS, refresh 30 detik, NIM duplikat, peminjaman dan aspirasi. Tes PHP tidak membuktikan izin kamera/browser.
- Periksa log, kapasitas disk, sertifikat, backup terjadwal, dan respons error tanpa stack trace. Jangan menyimpan isi aspirasi/kredensial di log operasional.

### Perhatian timezone untuk data lama

Konfigurasi sebelumnya UTC, sekarang WITA (`Asia/Makassar`). Kolom `datetime` tidak menyimpan zona waktu. Jangan melakukan konversi massal otomatis: pastikan apakah data lama dimasukkan sebagai jam UTC atau jam WITA terlebih dahulu. Untuk data lama berbasis UTC, rencanakan migrasi terkontrol dengan backup dan rekonsiliasi; untuk data yang memang sudah jam WITA, jangan menggeser delapan jam lagi. Audit `timestamp`/zona sesi database di staging juga. Belum ada migrasi data waktu yang dijalankan oleh perubahan ini.

## Rollback

Kembalikan kode dan aset ke rilis sebelumnya yang kompatibel dengan skema saat ini. Jangan menjalankan rollback migration secara buta: beberapa `down()` menghapus tabel/ledger. Jika perlu memulihkan database, gunakan backup yang sudah diuji dan rekonsiliasi transaksi/upload setelah waktu backup bersama operator. Pertahankan `APP_KEY` dan storage; bangun kembali config/route/view cache sesuai rilis yang dipilih.
