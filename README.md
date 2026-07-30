# Buku Mutasi Satpam

Aplikasi PHP native dan MariaDB untuk pencatatan buku mutasi satpam, inventaris, uraian kegiatan, serta validasi laporan.

## Menjalankan secara lokal

1. Letakkan folder proyek di `C:\xampp\htdocs`.
2. Buat database `db_sistem_informasi_satpam` lalu impor `db_sistem_informasi_satpam.sql` melalui phpMyAdmin.
3. Sesuaikan koneksi lokal pada `config/database.php` jika kredensial MySQL Anda berbeda.
4. Akses `http://localhost/buku_mutasi_satpam/`.

Kredensial seed hanya untuk pengembangan lokal:

| Peran | Password |
| --- | --- |
| Admin | `admin123` |
| Kepala BNN | `kepala123` |

Ganti password tersebut sebelum aplikasi digunakan di lingkungan selain lokal.

## Memperbarui instalasi lama

Jika database sudah pernah diimpor, backup database terlebih dahulu lalu jalankan `database/migrations/20260730_stabilize_foundation.sql` satu kali. Migrasi ini menambahkan kolom audit, menyelaraskan status login anggota shift, dan menjamin satu laporan untuk satu jadwal.