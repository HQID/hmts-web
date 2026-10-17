# Perbaikan input data — 17 Oktober 2026

## Masalah dan akar penyebab

Pengisian anggota dengan `sort_order` kosong menghasilkan SQLSTATE 1048 pada MySQL. Middleware Laravel mengubah string kosong menjadi `null`; aturan `nullable|integer` mengizinkan nilai tersebut, kemudian controller memasukkannya secara eksplisit ke kolom non-null. Default database `0` hanya berlaku jika kolom tidak dikirim, bukan ketika nilainya `null`.

Tes HTTP dengan payload browser berhasil mereproduksi kegagalan yang sama pada constraint non-null SQLite. Suite regresi baru sebelum perbaikan: **11 tes, 1 lulus, 10 gagal**. Suite 32 tes Sprint 3 sebelumnya belum mencakup submit anggota dengan field opsional kosong; hasil lulus tersebut bukan bukti bahwa semua kombinasi input sudah aman.

## Perubahan

- Anggota: `sort_order` hilang/kosong/null dinormalisasi menjadi `0` setelah validasi, sementara nol dan urutan positif eksplisit dipertahankan. Rentang dibatasi 0–65535 sesuai unsigned small integer. Form memberi default nol, batas angka, dan keterangan opsional. Nilai nullable yang sah (divisi/bio/NIM) tidak dihapus atau dipaksa menjadi string kosong.
- Relasi anggota ke divisi dan notulensi ke proker divalidasi dalam periode tujuan. Tidak menambah CRUD divisi baru; tidak ditemukan endpoint pembuatan divisi pada rute saat ini.
- Inventaris: kode unik global sesuai index database, kuantitas 0–4294967295, jumlah tersedia tidak melebihi total, dan boolean `is_active` dinormalisasi secara eksplisit.
- Periode: batas tahun 65535, boolean eksplisit, serta deaktivasi periode lama dan pembuatan periode baru dalam satu transaksi. Input tidak valid ditolak sebelum deaktivasi.
- Aset: `is_public` hilang/kosong/null menjadi false; flag visibilitas dan disk penyimpanan selalu memakai nilai boolean yang sama, sehingga aset private tidak mengikuti default publik database.
- Surat: nomor agenda unik global; lampiran, nomor surat, dan pengirim/penerima tetap opsional. Form tidak lagi menawarkan status presensi untuk surat.
- Form operasional: hidden `0` untuk checkbox, atribut `required` mengikuti aturan backend, nilai `old()` dipulihkan selain password/file, dan pesan error per field ditampilkan.
- Batas aman 5000 karakter pada kolom TEXT deskripsi proker/aset, spesifikasi inventaris, disposisi surat, dan tujuan peminjaman. LONGTEXT notulensi tidak dipersempit.
- Peminjaman: batas nama 255/NIM 30/tujuan 5000 diterapkan di Livewire dan action bersama. Error action dipetakan ke field formulir, termasuk pesan alat yang sebelumnya tidak ditampilkan.
- Presensi: validasi NIM dan token di action bersama melindungi pemanggil publik maupun scanner operator. Pencarian anggota memakai periode sesi secara eksplisit. Token tanpa masa berlaku ditolak sebagai validasi, bukan memanggil method pada null.
- Konflik unique saat insert inventaris, surat, akun kas, dan presensi ditangani khusus melalui `UniqueConstraintViolationException`, lalu dikonfirmasi terhadap field unik terkait sebelum menjadi validation error. Kegagalan yang tidak dikenali tetap dilempar. Lampiran surat yang terlanjur disimpan dibersihkan pada konflik unique.
- Nama asli berkas kuitansi dibatasi 255 karakter sebelum file disimpan.
- TestCase menolak menjalankan suite jika konfigurasi efektif bukan SQLite `:memory:`, sebelum lifecycle RefreshDatabase berjalan.

Tidak ada perubahan schema/migration, dependency baru, Redis, perubahan kredensial, commit, push, atau deployment. Database MySQL pengguna tidak dimigrasikan atau dihapus. Perubahan Sprint 2/3 yang sudah ada dipertahankan.

## Bukti pengujian

Seluruh perintah PHP verifikasi menggunakan `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: CACHE_STORE=array SESSION_DRIVER=array QUEUE_CONNECTION=sync`.

| Pemeriksaan | Hasil aktual |
| --- | --- |
| Suite regresi awal sebelum fix | 11 tes: 1 lulus, 10 gagal; blank sort_order menghasilkan HTTP 500/non-null constraint |
| Suite penuh setelah perbaikan dan format | **52 tes, 385 assertions, semua lulus** (20244 ms pada eksekusi akhir) |
| Laravel Pint pada 8 file PHP yang disentuh | Berhasil |
| `npm run build` | Berhasil; Vite 8.3.4, 27 modules |
| `php artisan view:cache` dengan env testing | Berhasil |
| `git diff --check` | Berhasil |

`DataEntryValidationTest` menambah 20 tes: payload blank/hilang/null/whitespace/nol/positif, batas numerik, relasi lintas periode, duplicate global lintas arsip, checkbox private/nonaktif, submit minimal modul, panjang teks/NIM, action langsung, scanner operator, panjang nama kuitansi, HTML optional/old input, dan simulasi insert race lewat event model. Simulasi tersebut benar-benar memicu constraint unique SQLite setelah pemeriksaan awal, tetapi **bukan uji dua koneksi MySQL bersamaan**.

## Batas verifikasi dan UAT

SQLite tidak menegakkan panjang varchar dan unsigned persis seperti MySQL strict. Tes mengassert validation error dan nilai normalisasi secara eksplisit; tidak mengklaim verifikasi strict-mode/row-lock MySQL. Pengujian MySQL hanya boleh dilakukan di database staging/disposable terpisah yang diotorisasi, bukan `hmts-web` pengguna.

Ulangi di browser: tambah anggota dengan divisi valid dan urutan kosong, tambah inventaris dengan kode duplikat, surat tanpa lampiran, aset tanpa centang publik, serta periode tanpa centang aktif. Pastikan field invalid menampilkan pesan dan mempertahankan isian. Kamera/JavaScript perangkat nyata dan deployment tetap belum diverifikasi; tidak ada jaminan seluruh aplikasi bebas bug.
