# Progress Sprint 2 - 12 Oktober 2026

## Integrasi yang selesai

- Katalog layanan memuat inventaris periode aktif dari database.
- Pengajuan peminjaman membuat `borrowings` dan `borrowing_items` dengan pemeriksaan bentrok jadwal.
- Aspirasi publik tersimpan anonim, mempunyai tiket unik, dan pelacak membaca status aktual.
- Presensi memeriksa sesi aktif, token enam digit berumur 30 detik, serta mencegah NIM hadir dua kali.
- Seeder membuat empat instrumen awal untuk katalog layanan.
- Ekspor CSV anggota dan kas tersedia pada rute internal terautentikasi.
- `/app/peminjaman` sekarang memverifikasi, menolak, menandai dipinjam/dikembalikan, dan menyesuaikan stok inventaris.
- `/app/aspirasi` sekarang memoderasi serta menanggapi tiket anonim tanpa menyimpan identitas pengirim.
- Pengurus dapat membuat sesi QR, menerbitkan token dinamis 30 detik, dan melihat jumlah kehadiran.
- Surat serta kuitansi diunggah ke disk privat; aset dapat dipilih publik atau privat. Unduhan privat memerlukan login pengurus dan URL bertanda tangan yang berlaku 15 menit.
- Panitia terhubung ke program kerja dan anggota pada database.
- Superadmin dapat membuat periode aktif dan akun admin; admin biasa ditolak oleh Gate.

## Verifikasi

- Semua migrasi MySQL berstatus `Ran`.
- Seeder MySQL berhasil dijalankan.
- `php artisan test`: 12 passed, 35 assertions, termasuk alur persetujuan peminjaman, moderasi aspirasi, dan larangan akses periode bagi admin biasa.
- `npm.cmd run build`: berhasil.
- `git diff --check`: berhasil.

## Lanjutan yang perlu dipoles sebelum rilis

- Tambahkan pengujian unggah/unduh berkas, ekspor CSV, dan token QR hingga cakupan alur kritis lebih luas.
- Tambahkan rekap presensi yang dapat diunduh dan CRUD penyuntingan/arsip untuk data operasional bila diperlukan operator.
- Lakukan UAT dengan akun admin dan superadmin di browser terhadap berkas nyata serta volume data produksi.
