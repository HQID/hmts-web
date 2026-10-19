# Tahap 1 — Hak akses, login, dan notulensi

Tanggal: 19 Oktober 2026. **Implementasi selesai dan verifikasi lokal lulus; transisi database utama belum dijalankan.**

- Perubahan periode dibatasi Gate superadmin pada endpoint dan UI. Tes lama yang mengizinkan admin biasa telah diselaraskan dengan PRD.
- Login dibatasi 5 kegagalan per pasangan email/IP dan 20 per IP dalam satu menit. Error memakai error bag tersendiri; modal otomatis terbuka, email mendapat fokus, dan pesan tidak mengungkap keberadaan akun.
- Notulensi baru disimpan pada disk `private`. Download membutuhkan login, hak pengurus, periode yang sesuai, dan temporary signed URL 15 menit. Berkas lama yang belum dipindahkan menampilkan status pemindahan tanpa tombol download kosong.
- Penggantian lampiran menyimpan berkas baru terlebih dahulu. Referensi database harus berhasil diperbarui sebelum berkas lama dihapus; jika gagal, berkas baru dibersihkan dan dokumen lama dipertahankan.
- Migration menambahkan `meeting_notes.file_disk`; default database `public` menandai baris lama, sedangkan model membuat baris baru sebagai `private`.
- Command `hmts:secure-meeting-notes` melakukan pemeriksaan tanpa perubahan secara default. `--apply` membuat backup privat dan manifest, memverifikasi SHA-256, memperbarui referensi dalam transaksi, lalu menghapus sumber publik setelah tidak direferensikan. Bisa dijalankan ulang untuk membersihkan sumber yang sempat gagal dihapus. Sumber hilang/berubah dilaporkan sebagai gagal.

Bagian utama: `ManagementController`, model `MeetingNote`, modal login, `routes/web.php`, `SecureMeetingNotes`, serta migration `2026_10_19_000001_secure_notes_and_version_inventory_stock.php`.

Bukti: tes role endpoint/UI, batas login dan pemulihan setelah satu menit, signature hilang/kedaluwarsa, tamu/periode lain, pemindahan dry run/apply/idempotensi, sumber hilang, dan kegagalan penggantian lampiran. Playwright memverifikasi error modal, kedua peran, unggah/unduh privat dan penolakan akses alternatif.

[Hasil pengujian gabungan](../testing/README.md) · [Panduan penerapan data lama](perbaikan_tahap1-4_penerapan_2026-10-19.md).

**Batas:** URL publik berkas lama pada database utama baru tertutup setelah pemindahan `--apply` berhasil dan salinan publik benar-benar terhapus. Penggantian link aplikasi saja tidak menutup file tersebut.
