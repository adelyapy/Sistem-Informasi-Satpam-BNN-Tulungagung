<?php

/** Mengecek apakah modul materi buku saku tersedia pada database aktif. */
function materiBukuSakuTableExists(mysqli $conn): bool
{
  $result = mysqli_query($conn, "SHOW TABLES LIKE 'materi_buku_saku'");
  return $result && mysqli_num_rows($result) > 0;
}

/** Menyimpan metadata PDF untuk setiap materi buku saku. */
function ensureMateriPdfColumns(mysqli $conn): bool
{
  // Database lama hanya mempunyai tabel buku_saku. Halaman PDF tetap dapat
  // digunakan untuk menampilkan semua dokumen yang pernah diunggah.
  if (!materiBukuSakuTableExists($conn)) {
    return true;
  }

  $kolom = [
    'pdf_path' => "ALTER TABLE materi_buku_saku ADD COLUMN pdf_path VARCHAR(255) DEFAULT NULL AFTER icon",
    'pdf_size' => "ALTER TABLE materi_buku_saku ADD COLUMN pdf_size BIGINT(20) DEFAULT NULL AFTER pdf_path",
    'pdf_generated_at' => "ALTER TABLE materi_buku_saku ADD COLUMN pdf_generated_at DATETIME DEFAULT NULL AFTER pdf_size",
  ];

  foreach ($kolom as $nama => $sql) {
    $cek = mysqli_query($conn, "SHOW COLUMNS FROM materi_buku_saku LIKE '$nama'");
    if (!$cek) {
      return false;
    }
    if (mysqli_num_rows($cek) === 0 && !mysqli_query($conn, $sql)) {
      return false;
    }
  }

  return true;
}

function pdfMateriTersedia(array $materi): bool
{
  $path = (string) ($materi['pdf_path'] ?? '');
  return $path !== '' && is_file(dirname(__DIR__) . '/' . $path);
}
