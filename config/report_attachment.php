<?php

/** Membuat penyimpanan lampiran foto laporan pada database lama maupun baru. */
function ensureLampiranFotoTable(mysqli $conn): bool
{
  $dibuat = mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS lampiran_foto (
      id_lampiran INT(11) NOT NULL AUTO_INCREMENT,
      id_laporan INT(11) NOT NULL,
      id_uraian INT(11) DEFAULT NULL,
      id_inventaris INT(11) DEFAULT NULL,
      nama_file VARCHAR(255) NOT NULL,
      path_file VARCHAR(255) NOT NULL,
      ukuran_file BIGINT(20) DEFAULT NULL,
      uploaded_by INT(11) DEFAULT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id_lampiran),
      KEY idx_lampiran_laporan (id_laporan),
      KEY idx_lampiran_uraian (id_uraian),
      KEY idx_lampiran_inventaris (id_inventaris),
      KEY idx_lampiran_uploader (uploaded_by),
      CONSTRAINT fk_lampiran_laporan FOREIGN KEY (id_laporan) REFERENCES laporan (id_laporan) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT fk_lampiran_uraian FOREIGN KEY (id_uraian) REFERENCES uraian_kegiatan (id_uraian) ON DELETE SET NULL ON UPDATE CASCADE,
      CONSTRAINT fk_lampiran_inventaris FOREIGN KEY (id_inventaris) REFERENCES inventaris (id_inventaris) ON DELETE SET NULL ON UPDATE CASCADE,
      CONSTRAINT fk_lampiran_uploader FOREIGN KEY (uploaded_by) REFERENCES users (id_user) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  ");

  return (bool) $dibuat && ensureLampiranInventarisColumn($conn);
}

/** Memigrasikan tabel lampiran lama agar foto dapat dikaitkan dengan inventaris. */
function ensureLampiranInventarisColumn(mysqli $conn): bool
{
  $kolom = mysqli_query($conn, "SHOW COLUMNS FROM lampiran_foto LIKE 'id_inventaris'");
  if (!$kolom) {
    return false;
  }

  if (mysqli_num_rows($kolom) === 0 && !mysqli_query($conn, 'ALTER TABLE lampiran_foto ADD COLUMN id_inventaris INT(11) DEFAULT NULL AFTER id_uraian')) {
    return false;
  }

  $indeks = mysqli_query($conn, "SHOW INDEX FROM lampiran_foto WHERE Key_name = 'idx_lampiran_inventaris'");
  if (!$indeks) {
    return false;
  }
  if (mysqli_num_rows($indeks) === 0 && !mysqli_query($conn, 'ALTER TABLE lampiran_foto ADD KEY idx_lampiran_inventaris (id_inventaris)')) {
    return false;
  }

  $foreignKey = mysqli_query($conn, "
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'lampiran_foto'
      AND COLUMN_NAME = 'id_inventaris'
      AND REFERENCED_TABLE_NAME = 'inventaris'
    LIMIT 1
  ");
  if (!$foreignKey) {
    return false;
  }

  return mysqli_num_rows($foreignKey) > 0
    || (bool) mysqli_query($conn, 'ALTER TABLE lampiran_foto ADD CONSTRAINT fk_lampiran_inventaris FOREIGN KEY (id_inventaris) REFERENCES inventaris (id_inventaris) ON DELETE SET NULL ON UPDATE CASCADE');
}

/** Menyimpan status ketika daftar inventaris telah disimpan sebagai draft. */
function ensureInventarisDraftColumn(mysqli $conn): bool
{
  $kolom = mysqli_query($conn, "SHOW COLUMNS FROM laporan LIKE 'inventaris_draft_disimpan'");
  if (!$kolom) {
    return false;
  }

  return mysqli_num_rows($kolom) > 0
    || (bool) mysqli_query($conn, 'ALTER TABLE laporan ADD COLUMN inventaris_draft_disimpan TINYINT(1) NOT NULL DEFAULT 0 AFTER inventaris_selesai');
}

/** Menyimpan status ketika uraian kegiatan telah disimpan sebagai draft. */
function ensureUraianDraftColumn(mysqli $conn): bool
{
  $kolom = mysqli_query($conn, "SHOW COLUMNS FROM laporan LIKE 'uraian_draft_disimpan'");
  if (!$kolom) {
    return false;
  }

  return mysqli_num_rows($kolom) > 0
    || (bool) mysqli_query($conn, 'ALTER TABLE laporan ADD COLUMN uraian_draft_disimpan TINYINT(1) NOT NULL DEFAULT 0 AFTER uraian_selesai');
}

/** Mengunggah satu foto lampiran dan mengembalikan metadata atau pesan kesalahan. */
function uploadLampiranFoto(array $file): array
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    return ['ok' => false, 'message' => 'Salah satu lampiran foto gagal diunggah.'];
  }

  if (($file['size'] ?? 0) < 1 || $file['size'] > 5 * 1024 * 1024) {
    return ['ok' => false, 'message' => 'Ukuran setiap foto maksimal 5 MB.'];
  }

  $imageInfo = @getimagesize($file['tmp_name'] ?? '');
  $mime = $imageInfo['mime'] ?? '';
  $extensionByMime = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
  ];

  if (!isset($extensionByMime[$mime])) {
    return ['ok' => false, 'message' => 'Lampiran harus berupa foto JPG, PNG, atau WEBP.'];
  }

  $folder = dirname(__DIR__) . '/uploads/lampiran_laporan';
  if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
    return ['ok' => false, 'message' => 'Folder lampiran tidak dapat dibuat.'];
  }

  try {
    $namaFile = bin2hex(random_bytes(16)) . '.' . $extensionByMime[$mime];
  } catch (Throwable) {
    return ['ok' => false, 'message' => 'Nama file lampiran tidak dapat dibuat.'];
  }

  if (!move_uploaded_file($file['tmp_name'], $folder . '/' . $namaFile)) {
    return ['ok' => false, 'message' => 'Foto lampiran tidak dapat disimpan.'];
  }

  return [
    'ok' => true,
    'nama_file' => $namaFile,
    'path_file' => 'uploads/lampiran_laporan/' . $namaFile,
    'ukuran_file' => (int) $file['size'],
  ];
}

function hapusLampiranUraian(mysqli $conn, int $idLaporan, int $idUraian): void
{
  $stmt = mysqli_prepare($conn, 'SELECT path_file FROM lampiran_foto WHERE id_laporan = ? AND id_uraian = ?');
  mysqli_stmt_bind_param($stmt, 'ii', $idLaporan, $idUraian);
  mysqli_stmt_execute($stmt);
  $lampiran = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

  $hapus = mysqli_prepare($conn, 'DELETE FROM lampiran_foto WHERE id_laporan = ? AND id_uraian = ?');
  mysqli_stmt_bind_param($hapus, 'ii', $idLaporan, $idUraian);
  mysqli_stmt_execute($hapus);

  foreach ($lampiran as $foto) {
    $path = dirname(__DIR__) . '/' . $foto['path_file'];
    if (is_file($path)) {
      @unlink($path);
    }
  }
}

function hapusLampiranInventaris(mysqli $conn, int $idLaporan, int $idInventaris): void
{
  $stmt = mysqli_prepare($conn, 'SELECT path_file FROM lampiran_foto WHERE id_laporan = ? AND id_inventaris = ?');
  mysqli_stmt_bind_param($stmt, 'ii', $idLaporan, $idInventaris);
  mysqli_stmt_execute($stmt);
  $lampiran = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

  $hapus = mysqli_prepare($conn, 'DELETE FROM lampiran_foto WHERE id_laporan = ? AND id_inventaris = ?');
  mysqli_stmt_bind_param($hapus, 'ii', $idLaporan, $idInventaris);
  mysqli_stmt_execute($hapus);

  foreach ($lampiran as $foto) {
    $path = dirname(__DIR__) . '/' . $foto['path_file'];
    if (is_file($path)) {
      @unlink($path);
    }
  }
}
