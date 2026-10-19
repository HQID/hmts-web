# Tahap 4 — Reservasi dan stok fisik

Tanggal: 19 Oktober 2026. **Implementasi selesai; backend, browser, dan konkurensi MySQL uji lulus. Rekonsiliasi database utama belum dijalankan.**

| Status | Reservasi | Stok fisik |
| --- | --- | --- |
| Menunggu | Belum menahan kapasitas | Tidak berubah |
| Disetujui | Menahan kapasitas sesuai interval | Tidak berubah |
| Dipinjam | Tetap menahan kapasitas | Berkurang sekali saat serah-terima |
| Dikembalikan | Ditutup | Bertambah sekali saat penerimaan |
| Ditolak | Dilepas | Tidak bertambah dari persetujuan saja |

- Kapasitas dihitung dari puncak pemakaian bersamaan, bukan jumlah seluruh reservasi yang menyentuh rentang. Dua jadwal berurutan dapat memakai unit yang sama. Interval menggunakan `[awal, akhir)`; akhir tanggal pilihan publik menjadi tengah malam hari berikutnya sehingga hari akhir ikut dipesan.
- Persetujuan mempertahankan stok fisik. Serah-terima hanya pada jendela peminjaman, memeriksa unit fisik kembali, dan mengurangi stok sekali. Return ganda/transisi ilegal ditolak. Alat yang sudah terlambat tetap menahan kapasitas sampai benar-benar dikembalikan.
- Perubahan jumlah inventaris tidak dapat melampaui total setelah memperhitungkan alat keluar, atau mengurangi kapasitas di bawah puncak reservasi yang sudah dijamin.
- Katalog membedakan stok fisik sekarang dari reservasi tanggal; kondisi/nonaktif/nol tercermin pada UI. Klaim konfirmasi WhatsApp otomatis yang belum tersedia dihapus. Pilihan status operator mengikuti transisi yang sah.
- Transaksi mengunci inventaris menurut urutan ID sebelum pembacaan yang membuat snapshot MySQL. Pengujian awal menemukan dua approval dapat lolos dari snapshot lama walaupun memakai row lock; penguncian dan pembacaan diperbaiki, lalu kedua skenario unit terakhir lulus.
- Migration memberi data lama `stock_version=1`, sementara model baru membuat versi 2. Stok versi lama tidak boleh dipakai dalam alur baru sampai direkonsiliasi.
- `hmts:reconcile-inventory` memeriksa seluruh periode secara dry run. `--apply` menambahkan kembali jumlah reservasi disetujui ke stok lama untuk memperoleh stok fisik. Baris tidak konsisten dilaporkan dan tetap versi 1, tanpa penghitungan ulang buta. Pengulangan tidak mengonversi stok dua kali.

Bukti: jadwal tidak tumpang tindih, puncak overlap/batas interval, overdue, serah-terima di masa depan, sunting kapasitas, penolakan/return ganda, dry run/apply/idempotensi, dan stok lama tidak konsisten. Playwright mengikuti approval → handover → return; MySQL terpisah menguji dua approval unit terakhir serta dua serah-terima unit terakhir.

[Hasil pengujian gabungan](../testing/README.md) · [Panduan rekonsiliasi sebelum digunakan](perbaikan_tahap1-4_penerapan_2026-10-19.md).
