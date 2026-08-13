<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/material_pdf.php';

$metadataFile = $argv[1] ?? '';
if ($metadataFile === '' || !is_file($metadataFile) || !ensureMateriPdfColumns($conn)) {
  fwrite(STDERR, "Metadata PDF materi tidak dapat diproses.\n");
  exit(1);
}

try {
  $metadata = json_decode((string) file_get_contents($metadataFile), true, 512, JSON_THROW_ON_ERROR);
  mysqli_begin_transaction($conn);
  $update = mysqli_prepare($conn, 'UPDATE materi_buku_saku SET pdf_path = ?, pdf_size = ?, pdf_generated_at = NOW() WHERE id_materi = ?');

  foreach ($metadata as $materi) {
    $path = (string) ($materi['pdf_path'] ?? '');
    $size = (int) ($materi['pdf_size'] ?? 0);
    $idMateri = (int) ($materi['id_materi'] ?? 0);
    if ($path === '' || $size < 1 || $idMateri < 1) {
      throw new RuntimeException('Metadata PDF tidak lengkap.');
    }
    mysqli_stmt_bind_param($update, 'sii', $path, $size, $idMateri);
    if (!mysqli_stmt_execute($update)) {
      throw new RuntimeException('Metadata PDF gagal disimpan.');
    }
  }

  mysqli_commit($conn);
  echo count($metadata) . " metadata PDF materi diperbarui.\n";
} catch (Throwable $error) {
  mysqli_rollback($conn);
  fwrite(STDERR, $error->getMessage() . "\n");
  exit(1);
}
