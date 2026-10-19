# Rencana pengerjaan perbaikan HMTS

Tanggal: 19 Oktober 2026. Status: **tahap 1–7 diimplementasikan; tahap 8 mencakup verifikasi otomatis lokal, sementara perangkat nyata, staging pengguna, dan penutupan produksi masih menunggu.**

[Catatan implementasi/penerapan tahap 5–8](implemented/perbaikan_tahap5-8_penerapan_2026-10-19.md). Keputusan profil pengguna: lima misi dan YouTube tetap dipakai; PRD sudah diselaraskan.

[Catatan implementasi tahap 1–4 dan penerapan data lama](implemented/perbaikan_tahap1-4_penerapan_2026-10-19.md) · [Hasil terbaru: 94 tes backend, 209 skenario browser/screenshot, 7 skenario konkurensi MySQL lulus](testing/README.md).

Checklist implementasi di bawah menunjukkan hasil dengan data uji. Migration database utama, pemindahan berkas lama, rekonsiliasi stok nyata, konfigurasi upload produksi, dan kamera fisik belum dilakukan. Ini belum merupakan penutupan rilis tahap 8.

Tujuan: memperbaiki kegagalan yang telah ditemukan, melengkapi alur operasional, lalu memenuhi cakupan PRD dengan bukti pengujian baru.

Dasar pekerjaan:

- [PRD](prd.md), [arsitektur](architecture.md), dan [aturan proyek](agent.md).
- [Hasil testing dan screenshot](testing/README.md): 111 skenario browser, 102 lulus, 9 gagal; 63 tes backend dengan 484 assertion lulus.
- [Pemeriksaan logika tambahan](testing/2026-10-19T04-36-24-016Z/prd-review-probes.json): masalah reservasi tidak bentrok, presensi melewati jadwal, pemilihan sesi bersamaan, serta struktur periode baru yang kosong.
- [Catatan Sprint 3](implemented/sprint3_17-10-2026.md): TOR dan registrasi tim belum menjadi lingkup implementasi terdahulu. Keduanya merupakan pelengkapan fitur PRD.

Angka lulus tes bukan persentase kelengkapan produk. Setiap tahap di bawah mempunyai kriteria selesai sendiri.

## Urutan dan hasil tiap tahap

| Tahap | Prioritas | Pekerjaan | Hasil yang harus bisa diperiksa |
| --- | --- | --- | --- |
| 1 | P0 | Hak akses, login, dokumen internal | Batas admin/superadmin konsisten; notulensi terlindungi; login gagal jelas dan dibatasi |
| 2 | P0 | Bank Aset | Unggahan nyata tampil; unduhan publik berhasil; berkas privat tetap tertutup |
| 3 | P0 | Presensi QR dan rekap | Sesi tepat; waktu dan token valid; operator dapat menyelesaikan sesi dan mengambil rekap |
| 4 | P0 | Reservasi dan stok inventaris | Jadwal tidak bentrok bisa disetujui; kapasitas dan stok fisik tidak rusak |
| 5 | P1 | Periode, pengurus, anggota, arsip | Periode baru dapat diisi dan diaktifkan; identitas publik akurat; periode lama menjadi arsip |
| 6 | P1 | Kegiatan, beranda, surat, konten PRD | TOR/registrasi berfungsi; sorotan kegiatan aktual; surat dapat diproses sampai selesai |
| 7 | P1 | UI desktop dan ponsel | Form mudah dicapai, tabel terbaca, feedback jelas, teks sesuai kemampuan aplikasi |
| 8 | P0 untuk rilis | Regresi, MySQL, perangkat nyata | Bukti tes baru, screenshot, uji konkurensi dan kamera, serta keputusan kesiapan rilis |

Tahap 1 menjadi fondasi pengamanan endpoint berikutnya. Tahap 2–4 dapat dikirim sebagai perbaikan terpisah. Tahap 5 menyediakan aturan periode untuk fitur baru di tahap 6. Perbaikan UI kecil dikerjakan bersama modulnya, lalu diperiksa menyeluruh pada tahap 7. Pengujian berjalan pada setiap tahap; tahap 8 merupakan pemeriksaan gabungan.

## Acuan keputusan produk

Keputusan berikut dipakai sebagai rancangan awal, bukan catatan bahwa kebutuhan baru sudah disahkan:

| Topik | Acuan rencana | Catatan yang harus diselaraskan sebelum implementasi terkait |
| --- | --- | --- |
| Hak perubahan periode | Mengikuti matriks PRD: superadmin | Sudah diterapkan pada tahap 1; endpoint, UI, dan tes kini membatasi perubahan periode pada superadmin. |
| Keanggotaan M1 | Pisahkan pekerjaan pengurus per periode dari perluasan database anggota umum/peminatan | Input pengurus periode baru wajib tersedia. Database anggota umum diperlukan untuk klaim PRD penuh, tetapi tidak boleh mengubah struktur publik menjadi daftar semua anggota. |
| Isi profil organisasi | Lima poin misi dan Mars melalui YouTube | Pengguna mengonfirmasi versi ini pada 19 Oktober 2026; PRD sudah diselaraskan. |
| Berkas dan agenda resmi | Gunakan konten yang tersedia dan dapat diverifikasi | TOR, arsip DWG/PDF/ZIP, foto alat, serta daftar agenda memerlukan bahan asli. Jangan menampilkan kuota, ukuran berkas, hadiah, atau 28 agenda fiktif. |
| Lingkungan | Laravel 13 / Livewire 4 dan dependensi terkunci yang sekarang | Sinkronkan dokumentasi versi; tidak merencanakan downgrade framework atau penggantian stack. |

## Tahap 1 — Hak akses, login, dan dokumen internal

Pekerjaan:

- [x] Selaraskan policy perubahan periode. Terapkan pemeriksaan pada endpoint backend dan tombol UI, termasuk request langsung.
- [x] Sesuaikan tes yang saat ini mengharapkan admin boleh mengubah periode dengan keputusan policy tersebut.
- [x] Tambahkan pembatasan percobaan login menggunakan fasilitas Laravel yang tersedia. Hindari menaruh password atau isi kredensial dalam log.
- [x] Pertahankan modal login terbuka saat validasi gagal, tampilkan pesan di field terkait, dan arahkan fokus ke kesalahan.
- [x] Lindungi lampiran notulensi sebagai dokumen internal: disk privat, autentikasi, scope periode, dan tautan sementara bertanda tangan.
- [x] Rencanakan pemindahan lampiran notulensi lama dari disk publik dengan inventaris berkas dan backup. Salin, verifikasi berkas dan referensinya, lalu hapus salinan publik setelah hasilnya benar. Mengubah link download saja tidak menutup URL publik lama.
- [x] Pastikan penggantian lampiran tidak menghapus berkas lama sebelum berkas baru dan perubahan referensi berhasil disimpan.

Bagian utama: `routes/web.php`, `ManagementController`, modal login, form/tampilan notulensi, serta konfigurasi atau metadata disk jika diperlukan untuk transisi berkas lama.

Kriteria selesai:

- [x] Admin mendapat penolakan sesuai policy pada UI maupun request langsung; superadmin tetap dapat bekerja.
- [x] Login berulang yang gagal dibatasi; pesan tidak membocorkan keberadaan akun; modal error terlihat tanpa dibuka ulang.
- [x] Unduhan notulensi membutuhkan akses yang benar. Tamu, signature hilang/kedaluwarsa, periode lain, dan URL publik lama ditolak.
- [x] Lampiran lama tetap dapat dibuka oleh pengurus berwenang setelah pemindahan; kegagalan unggah pengganti tidak menghilangkan dokumen sebelumnya.

## Tahap 2 — Bank Aset yang menggunakan data dan berkas nyata

Pekerjaan:

- [x] Perbaiki tipe respons `AssetDownloadController` agar cocok dengan respons unduhan Laravel.
- [x] Render tab Bank Aset dari `$assets` yang dikirim komponen. Hapus kartu dan tombol unduh contoh yang tidak memiliki berkas.
- [x] Tampilkan judul, kategori, deskripsi, format, serta ukuran berdasarkan data/berkas aktual.
- [x] Pastikan Master Logo Kit benar-benar berupa paket yang tersedia; jika hanya PNG, label dan keterangannya harus menyatakan PNG.
- [ ] Selaraskan unggah dengan kebutuhan DWG dan berkas lebih dari 20 MB. Rancangan awal batas aset 50 MB; verifikasi validasi, upload sementara Livewire, PHP, dan web server mendukungnya. Gunakan daftar format yang diizinkan serta validasi konten yang sesuai, bukan membuka semua ekstensi. Implementasi, Livewire, PHP uji, dan DWG 28 MiB sudah diverifikasi; konfigurasi server produksi belum diterapkan.
- [x] Pertahankan pemisahan publik/privat, periode, dan signature. Sertakan ekstensi yang benar pada nama unduhan surat/aset.
- [x] Tampilkan keadaan kosong dan pesan berkas tidak tersedia yang jelas, tanpa tombol yang berpura-pura berhasil.

Bagian utama: `AssetDownloadController`, `OperationsController`, `ServiceHub`, view layanan/aset, dan tes unggah-unduh.

Kriteria selesai:

- [x] Aset publik yang diunggah pengurus tampil dan berhasil diunduh sebagai tamu, tanpa HTTP 500.
- [x] Aset privat tidak tampil pada daftar publik dan tidak dapat diunduh melalui endpoint publik.
- [x] Isi, format, ukuran, serta nama berkas sesuai informasi kartu. Seluruh tombol unduh yang tampil mempunyai berkas nyata.
- [x] Format terlarang, berkas terlalu besar, dan berkas hilang ditangani dengan benar.

## Tahap 3 — Presensi QR, lifecycle sesi, dan rekap

Pekerjaan:

- [x] Sertakan ID sesi dalam payload QR bersama token. Contoh alur: `/layanan?tab=qr&session=ID&token=TOKEN`.
- [x] Form manual menyediakan pilihan sesi yang sedang berlangsung. Link lama tanpa ID sesi meminta pemilihan jika sesi ambigu; jangan memilih sesi terbaru secara diam-diam.
- [x] Validasi status, periode, jadwal WITA, token, dan NIM di action bersama yang dipakai pengunjung dan operator.
- [x] Token hanya berlaku untuk sesi yang dituju dan tetap berumur 30 detik. Membuka layar QR tidak otomatis mengaktifkan sesi draf.
- [x] Sediakan aksi mulai/selesaikan sesi dengan transisi yang jelas. Setelah sesi selesai, token dan pencatatan baru harus ditolak.
- [x] Periksa dua layar proyektor agar tidak saling mengganti token yang masih sah tanpa sengaja. Gunakan database/cache bawaan yang sudah ada bila diperlukan.
- [x] Cocokkan countdown dengan waktu kedaluwarsa server dan tangani kegagalan refresh, bukan hanya mengulang angka 30 di browser.
- [x] Lengkapi daftar peserta dengan pagination dan ekspor CSV per sesi. Pisahkan rekap lengkap dari tampilan 12 kehadiran terbaru.
- [x] Perjelas antarmuka kamera publik: mode manual berfungsi penuh; jika scanner mandiri menjadi target, hubungkan library yang sudah terpasang dan tampilkan izin/error kamera yang dapat dipahami.

Bagian utama: `ServiceHub`, `QrSessionDisplay`, dua action presensi, model sesi/kehadiran, route rekap, dan view terkait.

Kriteria selesai:

- [x] Token sesi A tetap diterima untuk A ketika sesi B berjalan bersamaan, dan ditolak untuk B.
- [x] Presensi sebelum jadwal, setelah jadwal, sesi draf/selesai, token kedaluwarsa, serta NIM duplikat ditolak pada jalur publik dan operator.
- [x] Dua peserta berbeda bisa mencatat hadir; pengiriman ganda NIM yang sama tidak membuat dua baris.
- [x] Daftar dan CSV mencakup semua peserta pada sesi yang dipilih serta tidak membuka data periode lain.
- [x] Izin kamera ditolak, kamera tidak tersedia, dan QR gagal dibaca mempunyai fallback manual yang jelas.

## Tahap 4 — Reservasi berdasarkan tanggal dan stok fisik

Aturan yang dituju:

| Status | Pengaruh reservasi jadwal | Pengaruh stok fisik |
| --- | --- | --- |
| Menunggu | Belum menjamin slot | Tidak berubah |
| Disetujui | Menahan kapasitas pada interval yang dipesan | Belum mengurangi unit fisik |
| Dipinjam | Tetap memegang kapasitas; keterlambatan harus diperhitungkan | Berkurang satu kali saat serah-terima |
| Dikembalikan | Reservasi ditutup | Bertambah satu kali saat penerimaan |
| Ditolak | Slot dilepas bila sebelumnya disetujui | Tidak menambah unit yang belum pernah keluar |

Pekerjaan:

- [x] Ubah pemeriksaan kapasitas menjadi jumlah pemakaian bersamaan pada rentang yang diminta. Jangan menjumlahkan dua reservasi yang sebenarnya berurutan sebagai pemakaian bersamaan.
- [x] Tetapkan batas interval waktu secara konsisten. Untuk pilihan tanggal penuh, normalisasikan akhir hari agar hari pengembalian dan peminjaman berikutnya tidak ambigu.
- [x] Pertahankan transaksi, penguncian inventaris berurutan, dan validasi transisi status. Persetujuan bersamaan untuk unit terakhir harus mempunyai satu pemenang.
- [x] Validasi stok fisik kembali saat alat diserahkan. Alat yang terlambat dikembalikan tidak boleh dianggap sudah tersedia hanya karena jadwal rencana berakhir.
- [x] Batasi sunting jumlah inventaris agar tidak bertentangan dengan unit yang masih keluar atau reservasi yang telah dijamin.
- [x] Bedakan label jumlah fisik tersedia sekarang dan ketersediaan untuk tanggal pilihan. Kondisi alat dan jumlah nol harus tercermin pada UI.
- [ ] Rekonsiliasi data lama sebelum arti `available_qty` berubah: sekarang stok dikurangi saat persetujuan. Hitung dan tinjau selisihnya pada salinan database; jangan menerapkan penghitungan ulang buta pada data nyata. Command dry run/apply sudah tersedia dan diuji; data utama belum dikonversi.

Bagian utama: tiga action inventaris/peminjaman, `Borrowing`, `Inventory`, form inventaris, katalog, dan operasi peminjaman.

Kriteria selesai:

- [x] Satu alat dapat disetujui untuk dua jadwal yang tidak saling bertabrakan.
- [x] Jadwal tumpang tindih yang melebihi kapasitas ditolak; batas tanggal dan reservasi berurutan diuji.
- [x] Alur menunggu → disetujui → dipinjam → dikembalikan menjaga stok dengan benar; submit ulang tidak mengubah stok dua kali.
- [x] Penolakan reservasi, perubahan jumlah, serta keterlambatan tidak menghasilkan stok negatif atau melebihi total.
- [x] Persetujuan dan serah-terima bersamaan diuji pada instance MySQL lokal khusus uji terpisah. Pengujian pada staging deployment masih menunggu tahap 8.

## Tahap 5 — Periode baru, pengurus, anggota, dan arsip

Pekerjaan inti pergantian periode:

- [x] Sediakan alur menyiapkan periode draf, struktur divisi/BPH, dan identitas pengurus sebelum aktivasi.
- [x] Izinkan input pengurus periode baru melalui aplikasi. Template dapat menyalin struktur jabatan/divisi; identitas pengurus harus berasal dari input yang benar.
- [x] Tentukan Ketua dan Koordinator dari jabatan yang sesuai, bukan semata baris pertama hasil pengurutan.
- [x] Tampilkan keadaan data belum diisi secara jujur. Hapus fallback identitas lama pada portal operasional/produksi.
- [x] Ambil periode footer dan label lain dari sumber periode yang sama.
- [x] Lengkapi pemilih arsip dengan aturan baca saja. Uji penolakan perubahan data historis melalui endpoint langsung, bukan hanya menyembunyikan tombol.
- [x] Tinjau kewajiban yang masih terbuka sebelum pergantian periode, terutama alat yang sedang dipinjam. Penanganan inventaris lintas periode harus mempertimbangkan kode alat yang saat ini unik secara global.
- [x] Pertahankan kas, kuitansi, aspirasi, serta catatan periode lama sebagai sejarah; jangan menyalinnya sebagai transaksi baru.

Paket perluasan M1 untuk pemenuhan PRD penuh:

- [x] Lengkapi input dan pencarian anggota umum serta pencatatan peminatan jika cakupan PRD M1 dipertahankan.
- [x] Bagan publik tetap menampilkan pengurus sesuai jabatan; daftar anggota umum tidak otomatis menjadi struktur organisasi.
- [x] Tentukan data anggota yang memang boleh dipublikasikan dan bidang yang hanya tersedia bagi pengurus.
- [x] Gunakan perubahan skema seperlunya; jangan mengembalikan kolom `role_type` yang sudah dihapus tanpa dasar kebutuhan yang jelas.

Bagian utama: model/scope periode, `ManagementController`, `AboutController`, view pengurus/profil/footer, seeder pengujian, dan route arsip.

Kriteria selesai:

- [x] Dari periode baru yang kosong, pengurus dapat menyiapkan struktur serta identitas yang benar sampai tampil di portal.
- [x] Periode baru tanpa data tidak menampilkan pengurus lama dengan label periode baru.
- [x] Pemindahan periode tidak mengubah ledger atau identitas dokumen historis.
- [x] Arsip terbaca oleh peran yang sesuai dan tidak dapat diubah. Penyuntingan urutan kartu tidak menukar identitas Ketua/Koordinator.
- [x] Status paket M1 dicatat jelas: selesai sesuai PRD atau PRD direvisi untuk cakupan pengurus saja.

## Tahap 6 — Pelengkapan PRD publik dan administrasi

### 6A. Kegiatan, TOR, dan pendaftaran

- [x] Buat tampilan timeline yang dikelompokkan menurut tanggal/bulan dari `Program` periode aktif; pertahankan filter yang sudah berfungsi.
- [x] Hubungkan unggah, penggantian, serta unduh TOR/silabus dengan `tor_path` yang sudah tersedia. Tombol hanya muncul jika berkas ada dan memang dipublikasikan.
- [x] Tambahkan pengaturan pembukaan pendaftaran dan kuota pada program.
- [x] Sediakan formulir registrasi sesuai jenis kegiatan: tim atau peserta/relawan, menggunakan bidang tetap yang diperlukan.
- [x] Simpan pendaftaran dengan relasi program/periode, identitas yang diperlukan, nomor pendaftaran, dan status. Bukti pendaftaran tidak boleh membuka data peserta lain.
- [x] Tangani pengiriman ganda dan kuota terakhir dengan validasi server, constraint yang sesuai, dan transaksi. Hindari membangun sistem pembayaran atau form builder yang tidak diminta PRD.
- [x] Sediakan daftar/rekap pendaftar untuk pengurus dan penutupan pendaftaran. Klaim sisa kuota berasal dari data nyata.

Selesai jika pengunjung dapat menemukan kegiatan, membaca detail, mengunduh dokumen, mendaftar, dan memperoleh bukti; pengurus dapat melihat hasilnya. Pendaftaran ditolak saat ditutup, kuota habis, data invalid, atau request ganda.

### 6B. Beranda dan profil

- [x] Tampilkan sampai tiga proker terdekat dari data aktual; gunakan status, tanggal, dan tautan yang benar. Jika data kurang dari tiga, tampilkan yang tersedia.
- [x] Hapus array agenda contoh yang tidak digunakan. Isi materi resmi agenda melalui pengelolaan program.
- [x] Catat keputusan versi misi dan media Mars dalam PRD. Verifikasi konten resmi tanpa membuat lirik, statistik, atau klaim kegiatan baru.

Selesai jika perubahan program pada panel tercermin di daftar kegiatan dan sorotan beranda, sementara profil serta periode konsisten.

### 6C. Surat dan disposisi

- [x] Tambahkan penyuntingan metadata/catatan dan perubahan status surat yang sudah didaftarkan.
- [x] Terapkan alur terdaftar → didisposisikan → selesai beserta otorisasi yang mengikuti matriks peran.
- [x] Catat pelaku/waktu pembaruan seperlunya dan pertahankan lampiran privat.
- [x] Pertahankan keunikan nomor agenda serta ekstensi yang benar ketika diunduh.

Selesai jika sebuah surat dapat diproses sejak pendaftaran sampai penyelesaian tanpa membuat surat duplikat hanya untuk mengganti status.

## Tahap 7 — Perbaikan UI dan pengalaman pengguna

- [x] Setelah memilih alat pada ponsel, arahkan scroll/fokus ke formulir dan tampilkan alat terpilih dengan jelas.
- [x] Gunakan foto inventaris jika tersedia; jika belum ada, gunakan placeholder ringkas sehingga tidak menyisakan kotak gelap besar.
- [x] Buat riwayat kas ponsel mudah dibaca: ringkasan per transaksi atau tabel dengan petunjuk geser, nominal utuh, dan akses status/aksi yang jelas.
- [x] Sediakan gambar hero lokal/fallback. Peta atau video eksternal yang gagal tetap mempunyai tautan alternatif dan informasi dasar.
- [x] Rapikan feedback memuat, gagal, berhasil, kosong, kuota penuh, alat habis, dan sesi tidak aktif pada modul terkait.
- [x] Lengkapi label form, fokus keyboard, tombol tutup, penutupan dengan Escape, dan pengembalian fokus pada modal. Periksa penggunaan dengan keyboard dan pembesaran browser.
- [x] Perbaiki teks konfirmasi WhatsApp jika tidak ada data kontak/proses konfirmasi yang benar. Hindari janji notifikasi otomatis yang belum tersedia.
- [x] Selaraskan format/ukuran aset, status ketersediaan, identitas sesi, dan label periode dengan sumber data aktual.
- [x] Tampilkan keadaan izin kamera secara jelas; kotak simulator tidak boleh terlihat seperti kamera aktif.

Kriteria selesai: alur utama dapat diselesaikan pada layar ponsel dan laptop tanpa kehilangan nominal, tombol, pesan error, atau konteks pilihan. Screenshot harus mencakup keadaan interaktif, bukan hanya halaman awal.

## Tahap 8 — Verifikasi akhir dan kesiapan rilis

### Pengujian per tahap

- Tambahkan tes regresi untuk bug yang diperbaiki pada action/endpoint bersama dan pemanggilnya.
- Jalankan tes yang terkait terlebih dahulu; jalankan seluruh suite setelah perubahan lintas modul atau sebelum menutup tahap.
- Perbarui runner Playwright ketika perilaku produk berubah. Skenario independen harus memulihkan halaman yang diperlukan agar satu HTTP 500 tidak menimbulkan kegagalan lanjutan yang menyesatkan.
- Jangan meluluskan tes dengan menghapus assertion, memberi mock keberhasilan unduh, atau menandai bug sebagai dilewati tanpa alasan yang dicatat.

### Pemeriksaan gabungan

- [x] Suite backend, build frontend, route, dan kompilasi view lulus.
- [x] Seluruh skenario lama yang masih berlaku dijalankan ulang; tambahkan skenario fitur baru dan masalah logika tambahan. Jumlah tes boleh berubah sesuai cakupan.
- [x] Simpan laporan Playwright, screenshot desktop/ponsel, detail assertion, serta bukti berkas hasil unduhan.
- [x] Sertakan screenshot modal detail, error login, pilihan alat, status surat, kuota registrasi, arsip, dan sesi selesai.
- [x] Jalankan konkurensi MySQL untuk kas, unit inventaris terakhir, kapasitas reservasi, presensi duplikat, serta kuota pendaftaran terakhir.
- [ ] Uji Android/iOS atau perangkat nyata yang tersedia melalui HTTPS untuk kamera, izin, pembacaan QR, polling, countdown, dan input tanggal.
- [ ] Periksa browser tambahan yang ditargetkan, terutama Safari untuk alur ponsel.
- [ ] Uji pemindahan berkas/data lama pada salinan staging dan lakukan rekonsiliasi hasilnya.
- [ ] Ikuti [panduan deployment](deployment.md): runtime sesuai lockfile, backup/restore, storage persisten, HTTPS, debug nonaktif, serta rollback yang kompatibel.

### Syarat penutupan

1. Semua masalah P0 telah diselesaikan dan mempunyai tes regresi yang relevan.
2. Fitur yang dinyatakan tersedia dapat diselesaikan dari awal sampai akhir dengan data nyata.
3. Data privat dan arsip tidak terbuka melalui URL alternatif atau request langsung.
4. Slot, stok, saldo, kuota, serta presensi tetap benar pada pengiriman ganda dan konkurensi yang diuji.
5. Daftar selisih PRD telah diselesaikan atau PRD telah diperbarui berdasarkan keputusan produk yang jelas.
6. Laporan membedakan hasil lokal, staging, perangkat nyata, dan produksi. Deployment hanya dinyatakan selesai setelah benar-benar dilakukan.

## Cara menjalankan pekerjaan

Setiap tahap ditutup dengan perubahan yang dapat ditinjau, migration/transisi data bila ada, tes relevan, screenshot, dan catatan `docs/implemented/perbaikan_tahapN_YYYY-MM-DD.md`. Catatan berisi hasil aktual serta batas verifikasi, bukan hanya daftar rencana.

Paket pengiriman:

- **Perbaikan utama:** tahap 1–4, dengan alur terkait diverifikasi pada tahap 8. Ini memperbaiki masalah operasional yang sudah terbukti.
- **Pelengkapan produk:** tahap 5–7, lalu pemeriksaan gabungan tahap 8. Ini diperlukan untuk menyatakan cakupan PRD penuh telah terpenuhi.

Pertahankan stack, Gate native, database/cache bawaan, dan CSV native. Tambahkan tabel/kolom hanya untuk kebutuhan yang benar-benar digunakan. Ledger kas tetap permanen dengan koreksi melalui void, dan aspirasi tetap tidak menyimpan identitas pengirim.

Tahap 1–7 telah mengubah aplikasi sesuai catatan implementasi. M1 mencakup anggota umum dan lima peminatan internal; bagan publik hanya memuat pengurus. Migration/transisi data pengguna dan deployment belum dijalankan. Materi agenda/berkas/identitas produksi dan pemeriksaan lingkungan tujuan tetap harus dilengkapi sebelum penutupan rilis tahap 8.
