# Security Testing Checklist

Jalankan pengujian ini pada salinan database/staging, bukan pada data produksi. Sebelum pengujian, buat `.env` dari `.env.example`, jalankan `composer install`, dan impor `database/migrations/20260818_security.sql` satu kali.

## Autentikasi dan sesi

| Endpoint / fitur | Risiko | Cara testing | Hasil yang diharapkan |
|---|---|---|---|
| `POST /login_process.php` admin/kepala | Credential stuffing, enumerasi pengguna | Masukkan username/email atau password yang salah lima kali dari IP yang sama, lalu coba lagi. | Upaya kelima tercatat; upaya berikutnya ditolak selama 15 menit dengan pesan umum. |
| `POST /login_process.php` satpam | Penyalahgunaan nama/shift | Kirim kombinasi satpam/shift salah lima kali atau shift di luar jam aktif. | Ditolak, dicatat, dan terkunci sementara setelah lima percobaan. |
| Semua halaman terautentikasi | Session fixation / sesi lama | Login, buka halaman lain, logout; gunakan tombol Back/Forward atau pakai cookie lama. | Akses selalu dialihkan ke login; sesi lama tidak berlaku. |
| `POST /forgot_password.php` | Enumerasi akun | Coba email yang ada dan tidak ada. | Pesan respons identik; log internal saja yang membedakan error. |
| `GET/POST /reset_password.php` | Token replay/expired | Gunakan token dua kali dan token setelah 15 menit. | Token hanya dapat dipakai sekali dan token kedaluwarsa ditolak. Semua sesi lama pengguna menjadi tidak valid. |

## CSRF, otorisasi, dan input

| Endpoint / fitur | Risiko | Cara testing | Hasil yang diharapkan |
|---|---|---|---|
| Semua `POST` admin, kepala, satpam | CSRF | Kirim ulang form menggunakan Postman tanpa field `csrf_token`, atau ubah token. | Request berhenti dengan HTTP 419 dan pesan umum tanpa perubahan data. |
| `POST /satpam/buku_mutasi/kirim.php` | Finalisasi milik pengguna lain | Login sebagai Satpam A lalu ubah parameter `id_laporan` menjadi laporan Satpam B. | Ditolak; laporan B tidak berubah. |
| `POST /kepala/validasi/validasi.php` | Validasi tanpa peran | Jalankan URL atau request saat login sebagai satpam/admin. | Dialihkan atau ditolak oleh role-based access. |
| Endpoint edit/hapus admin | IDOR | Ubah nilai `id` pada URL/form ke data yang tidak semestinya. | Hanya admin yang dapat memproses; query tetap dibatasi dan aktivitas dicatat. |
| Materi buku saku | Stored XSS | Masukkan `<script>alert(1)</script>` dan tautan `javascript:` pada materi. | Script dan URL berbahaya tidak dirender; tag materi yang diizinkan tetap tampil. |

## Upload dan file

| Endpoint / fitur | Risiko | Cara testing | Hasil yang diharapkan |
|---|---|---|---|
| Upload foto, tanda tangan, lampiran, ikon | Web shell / MIME spoofing | Unggah `shell.php`, gambar palsu, SVG berisi script, serta file > batas ukuran. | Ditolak berdasarkan MIME `finfo`, dimensi gambar, dan ukuran; file tidak tersimpan. |
| `uploads/*` | Eksekusi file | Jika ada file `.php` diunggah secara manual untuk test, akses langsung URL file. | Apache menolak eksekusi/akses sesuai `.htaccess`; directory listing juga nonaktif. |
| Upload PDF buku saku | Dokumen palsu | Ubah ekstensi file bukan PDF menjadi `.pdf`. | Ditolak karena MIME dan signature PDF tidak valid. |

## Output, database, dan server

| Area | Risiko | Cara testing | Hasil yang diharapkan |
|---|---|---|---|
| Semua halaman aplikasi | Kebocoran error/path server | Paksa error database (staging) atau parameter invalid. | Pengguna hanya melihat pesan umum; detail tercatat di `storage/logs/app.log`. |
| Konfigurasi database/SMTP | Kebocoran kredensial | Cari `DB_PASSWORD`, username, password pada file PHP dan buka `/.env`. | Kredensial hanya di `.env`; `/.env` ditolak web server. |
| Header respons | Clickjacking/MIME sniffing | Periksa dengan DevTools Network atau `curl -I`. | Ada `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, dan CSP. Bootstrap/JS tetap termuat. |
| Audit log | Non-repudiation | Login sukses/gagal, tambah/edit/hapus data, kirim/validasi laporan. | Baris baru terdapat di `audit_logs` dengan actor, modul, IP, user agent, dan waktu. |

## Kriteria rilis

- Tidak ada error PHP atau detail query yang tampil di browser.
- Semua form POST memuat `csrf_token` dan request tanpa token ditolak.
- Semua endpoint autentikasi memakai password hash/verify dan rate limiting.
- Semua unggahan menggunakan nama acak dan lolos validasi MIME/ukuran.
- Migration berhasil dan file `.env` tidak ikut di commit atau deployment package.