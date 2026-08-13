<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/material_pdf.php';

if (!ensureMateriPdfColumns($conn)) {
  fwrite(STDERR, "Kolom PDF materi tidak dapat disiapkan.\n");
  exit(1);
}

$query = mysqli_query($conn, '
  SELECT m.id_materi, m.judul, m.isi, k.id_kategori, k.nama_kategori
  FROM materi_buku_saku m
  INNER JOIN kategori_buku_saku k ON k.id_kategori = m.id_kategori
  ORDER BY k.nama_kategori ASC, m.judul ASC
');

if (!$query) {
  fwrite(STDERR, "Materi buku saku tidak dapat diambil.\n");
  exit(1);
}

echo json_encode(mysqli_fetch_all($query, MYSQLI_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
