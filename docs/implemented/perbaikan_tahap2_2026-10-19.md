# Tahap 2 — Bank Aset dan unduhan

Tanggal: 19 Oktober 2026. **Implementasi selesai dan verifikasi lokal lulus; konfigurasi upload server produksi belum diterapkan.**

- Tipe respons download publik diperbaiki menjadi `StreamedResponse`, sesuai hasil `Storage::download()`; kegagalan HTTP 500 akibat salah tipe telah diperbaiki.
- Bank Aset menampilkan data unggahan publik dari database dan berkas yang tersedia. Kartu/logo/blueprint/paper/modul contoh dan tombol download palsu dihapus. Format dan ukuran dihitung dari berkas aktual; keadaan kosong atau berkas hilang ditampilkan tanpa tombol palsu.
- Unggah aset menerima PDF, PNG, JPG/JPEG, WEBP, ZIP, dan DWG maksimal 50 MiB. Ekstensi diperiksa bersama MIME; DWG diperiksa melalui header AutoCAD. Berkas terlarang atau melebihi batas ditolak. Validasi ini bukan pemeriksaan isi CAD secara menyeluruh.
- Pemisahan disk publik/privat, signature, dan periode dipertahankan. Nama unduhan aset privat/surat menyertakan ekstensi. Berkas unggahan dibersihkan jika penyimpanan database gagal.
- Batas upload sementara Livewire diselaraskan menjadi 50 MiB. Runner browser memakai PHP `upload_max_filesize=50M` dan `post_max_size=64M`.

Bagian utama: `AssetDownloadController`, `OperationsController`, `DesignAsset`, `AssetDocument`, `ServiceHub`, view layanan, dan `config/livewire.php`.

Bukti: aset publik diunggah dan diunduh sebagai tamu, aset privat tidak ditampilkan dan ditolak melalui endpoint publik, berkas hilang/format terlarang/lebih dari batas ditolak. Playwright memverifikasi upload DWG kecil melalui form, multipart DWG 28 MiB melalui HTTP API browser, label ukuran aktual, dan download DWG tersebut. Fixture DWG menggunakan header valid dan data uji; bukan validasi kemampuan membuka gambar di AutoCAD.

[Hasil pengujian gabungan dan screenshot](../testing/README.md) · [Konfigurasi server yang diperlukan](perbaikan_tahap1-4_penerapan_2026-10-19.md).
