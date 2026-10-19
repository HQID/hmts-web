# Penerapan perbaikan tahap 1–4

Implementasi dan pengujian menggunakan database/storage uji terpisah. **Migration, pemindahan notulensi, dan rekonsiliasi stok belum dijalankan pada database utama. Belum ada deployment, commit, atau push.** Tahap 5–8 belum dikerjakan sebagai paket pekerjaan.

Catatan implementasi: [tahap 1](perbaikan_tahap1_2026-10-19.md), [tahap 2](perbaikan_tahap2_2026-10-19.md), [tahap 3](perbaikan_tahap3_2026-10-19.md), [tahap 4](perbaikan_tahap4_2026-10-19.md). [Laporan pengujian terbaru](../testing/README.md).

## Sebelum membuka aplikasi dengan kode baru

Ikuti [panduan deployment](../deployment.md), gunakan maintenance, dan backup database beserta kedua storage disk. Uji pemulihan backup dan command berikut pada salinan data terlebih dahulu. Hentikan penulisan lama selama transisi; kode lama mengurangi stok ketika approval, sedangkan kode baru ketika handover.

1. Jalankan migration skema:

   ```sh
   php artisan migrate --force
   ```

2. Periksa inventaris berkas dan perhitungan stok tanpa menerapkan perubahan:

   ```sh
   php artisan hmts:secure-meeting-notes
   php artisan hmts:reconcile-inventory
   ```

3. Tinjau setiap hasil, terutama berkas hilang dan stok yang tidak konsisten. Setelah hasil ditinjau, terapkan:

   ```sh
   php artisan hmts:secure-meeting-notes --apply
   php artisan hmts:reconcile-inventory --apply
   ```

4. Pastikan tidak ada error/nonzero exit code. Command bisa menyelesaikan baris valid sambil melaporkan baris bermasalah; ini bukan transaksi seluruh dataset. Perbaiki sumber masalah dan ulangi, lalu cocokkan stok fisik dengan catatan alat yang keluar. Pastikan semua inventaris lama sudah versi 2 sebelum membuka peminjaman.
5. Cocokkan jumlah referensi notulensi privat, akses file privat oleh pengurus, serta penolakan URL publik lama. Command hanya menangani file yang direferensikan notulensi/manifest; periksa file publik yatim, salinan manual, backup publik, dan cache/CDN bila ada. Simpan backup privat dan manifest `migration-backups/meeting-notes/` untuk pemulihan.
6. Build aset, bersihkan/perbarui cache sesuai panduan deployment, lalu lakukan smoke test login, aset, presensi, dan stok sebelum maintenance dibuka.

Menjalankan migration saja belum menyelesaikan transisi data. Jangan rollback hanya dengan menghapus kolom atau memasang kode lama setelah stok dikonversi; gunakan backup database dan berkas yang saling cocok atau prosedur pemulihan yang sudah diuji.

## Batas upload dan kamera

Konfigurasi aplikasi/Livewire menerima maksimum 50 MiB per aset. PHP web/FPM perlu `upload_max_filesize=50M`, `post_max_size=64M`, dan batas web server/proxy setidaknya sebesar request (contoh nginx `client_max_body_size 64m`). Terapkan pada runtime web, bukan hanya PHP CLI, kemudian uji upload melalui form pada staging. Batas ini belum diubah pada server produksi.

Kamera browser memerlukan HTTPS atau localhost. Verifikasi izin ditolak/diizinkan, kamera belakang, scan QR lintas perangkat, polling, dan countdown pada perangkat target. Tes otomatis saat ini memverifikasi fallback tanpa kamera; tidak menggantikan pengujian kamera fisik.
