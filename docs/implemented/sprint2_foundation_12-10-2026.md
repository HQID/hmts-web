# Fondasi Implementasi Sprint 2

**Tanggal:** 12 Oktober 2026  
**Status:** fondasi domain selesai, Command Center `/app` sedang diimplementasikan.

## Lingkup yang ditambahkan

- Migrasi operasional untuk inventaris, pengajuan dan item peminjaman, sesi dan catatan presensi, serta aspirasi.
- Model Eloquent beserta relasi dan `ActivePeriodScope` pada setiap entitas operasional yang memiliki `period_id`.
- Action anti-bentrok peminjaman dan pembuatan pengajuan.
- Action token presensi QR enam digit yang di-hash dan kedaluwarsa dalam 30 detik, serta pencatatan presensi yang mencegah duplikasi NIM.
- Action pengiriman aspirasi anonim yang hanya menyimpan periode, kategori, isi, status, dan kode pelacakan. Tidak ada `user_id`, nama, IP, atau user agent.
- Fondasi domain internal untuk program kerja, panitia, kas, kuitansi, surat, notulensi, dan bank aset.
- Command Center `/app` yang dilindungi autentikasi, termasuk dashboard ringkasan dan halaman manajemen untuk anggota, program kerja, inventaris, presensi, surat, notulensi, serta aset.
- Ledger kas dengan pencatatan transaksi dan pembatalan `void` yang dibatasi ke superadmin.

## Batas yang disengaja

- Tidak ada view, markup, CSS, atau komponen publik yang diubah.
- Halaman publik `/layanan` masih memakai perilaku simulasi Sprint 1 dan belum dipasangkan ke data produksi. Halaman publik sengaja tidak diubah selama fokus pekerjaan berada pada `/app`.
- Antarmuka internal untuk peminjaman, aspirasi, presensi QR, unggah berkas aset, kuitansi, surat privat, dan notulensi masih perlu dilengkapi dengan action serta kebijakan otorisasinya masing-masing.
- Modul manajemen periode dan akun admin khusus superadmin belum dibuat.

## Verifikasi 12 Oktober 2026

- MySQL berhasil digunakan. Seluruh migrasi proyek berstatus `Ran`, termasuk migrasi fondasi periode/divisi/anggota, layanan mahasiswa, dan manajemen internal.
- `php artisan test`: **9 passed, 29 assertions**. Cakupan mencakup empat halaman publik, proteksi autentikasi `/app`, penyimpanan anggota, pencatatan kas, larangan void oleh admin, dan void oleh superadmin.
- `npm.cmd run build`: berhasil. Manifest Vite dan aset produksi terbentuk.
- `git diff --check`: tidak menemukan whitespace error.

## Status Modul PRD

| Modul | Status saat ini |
| --- | --- |
| M1 Anggota, divisi, peminatan | Model, migrasi, seeder, dan halaman tambah/list internal tersedia. CRUD lengkap belum selesai. |
| M2 Presensi QR | Model, migrasi, token 30 detik, dan halaman pembuatan sesi tersedia. Pemindaian dan rekap internal belum disambungkan. |
| M3-M4 Proker dan kepanitiaan | Model/migrasi serta halaman program kerja tersedia. Panitia dan pengelolaan tugas belum disambungkan ke UI. |
| M5-M6 Kas dan kuitansi | Model/migrasi, aksi ledger, halaman catat transaksi, serta void superadmin tersedia. Upload kuitansi dan ekspor CSV belum dibuat. |
| M7-M8 Inventaris dan peminjaman | Model/migrasi, aksi deteksi bentrok, dan halaman inventaris tersedia. Pengajuan, verifikasi, dan pengembalian belum disambungkan ke UI. |
| M9 Surat dan disposisi | Model/migrasi serta halaman register surat tersedia. Unggah privat dan alur disposisi belum selesai. |
| M10 Notulensi | Model/migrasi dan halaman pencatatan tersedia. Edit, ekspor, dan tindak lanjut belum selesai. |
| M11 Aspirasi anonim | Model/migrasi serta aksi pengiriman anonim tersedia. Moderasi dan respons internal belum disambungkan. |
| M12 Bank aset | Model/migrasi dan halaman pencatatan metadata tersedia. Upload dan kontrol unduhan belum disambungkan. |
