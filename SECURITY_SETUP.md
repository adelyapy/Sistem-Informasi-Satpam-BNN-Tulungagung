# Konfigurasi keamanan produksi

1. Salin `.env.example` menjadi `.env`, lalu isi seluruh variabel database dan SMTP dengan nilai lingkungan Anda.
2. Jalankan `composer install --no-dev --optimize-autoloader` di root proyek untuk memasang PHPMailer.
3. Impor `database/migrations/20260818_security.sql` sekali melalui phpMyAdmin atau MySQL CLI.
4. Isi email unik bagi akun Admin dan Kepala BNN pada tabel `users`. Username lama tetap dapat digunakan sebagai identifier login.
5. Pastikan folder `storage/logs` dapat ditulis oleh layanan web. File log tidak boleh dipublikasikan.

Jangan memasukkan `.env` atau `storage/logs/app.log` ke repository.
