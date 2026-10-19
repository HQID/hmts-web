# Tahap 3 — Presensi QR, sesi, dan rekap

Tanggal: 19 Oktober 2026. **Implementasi selesai; backend, browser, dan konkurensi MySQL uji lulus. Kamera fisik belum diverifikasi.**

- QR menyertakan `session` dan `token`. Form manual menyediakan pilihan sesi terbuka; jika beberapa sesi berlangsung, pengguna harus memilih dan tidak otomatis diarahkan ke sesi terbaru.
- Action bersama memeriksa periode, status aktif, jadwal WITA, token dan masa berlaku, serta duplikat NIM. Awal jadwal inklusif, akhir eksklusif. Token berlaku maksimal 30 detik dan dibatasi akhir sesi.
- Membuka proyektor tidak mengaktifkan draf. Operator memulai sesi saat jadwal berlangsung dan menyelesaikannya secara eksplisit. Sesi selesai menolak pencatatan baru.
- Proyektor berbagi token sah melalui cache database; token cache dienkripsi, database sesi menyimpan hash. Polling tidak mengganti token yang masih sah. Rotasi manual mengganti token dan membatalkan token sebelumnya.
- Countdown mengikuti waktu kedaluwarsa server. QR dan token dibersihkan saat sesi tidak dapat digunakan. Pemindaian operator menolak payload sesi lain.
- Kamera publik terhubung ke `html5-qrcode` yang sudah terpasang, dengan kontrol mulai/berhenti serta fallback input manual. Hasil scan mengisi sesi/token; pengguna tetap mengisi NIM dan mengirim form.
- Rekap lengkap per sesi memakai pagination 25 baris dan streamed CSV, termasuk perlindungan formula spreadsheet. Tampilan kehadiran terbaru tetap terpisah.
- Penguncian sesi dilakukan sebelum pembacaan yang membuat snapshot transaksi MySQL. Ini menjaga penolakan NIM ganda dan token konsisten pada request bersamaan.

Bagian utama: dua action presensi, `AttendanceSession`, `QrSessionDisplay`, `AttendanceController`, `ServiceHub`, view proyektor/layanan/rekap, dan `resources/js/app.js`.

Bukti: sesi A/B bersamaan, draf/selesai/di luar jadwal, TTL, duplikat, dua proyektor dan rotasi, rekap 30 peserta/pagination/CSV, isolasi periode. Browser menguji fallback kamera tidak tersedia; dua worker MySQL nyata menguji NIM yang sama dan penerbitan token bersama.

[Hasil pengujian gabungan dan screenshot](../testing/README.md). Izin kamera, pembacaan QR dari kamera nyata, Android/iOS melalui HTTPS, dan Safari masih termasuk verifikasi perangkat pada tahap 8.
