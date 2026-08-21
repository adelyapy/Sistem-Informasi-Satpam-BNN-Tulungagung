<?php
require_once "database.php";

/* =========================
   FUNGSI LAMA
========================= */

function e($string)
{
  return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/** Menampilkan rich text materi dengan tag dan atribut yang telah dibatasi. */
function sanitizeRichHtml(string $html): string
{
  $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><a><img><table><thead><tbody><tr><th><td><hr><span>';
  $clean = strip_tags($html, $allowedTags);

  $clean = preg_replace_callback('/<(a|img|span)\\b([^>]*)>/i', static function (array $match): string {
    $tag = strtolower($match[1]);
    $attributes = '';
    preg_match_all('/\\s+(href|src|alt|title|target|rel|class)\\s*=\\s*(["\\\'])(.*?)\\2/i', $match[2], $found, PREG_SET_ORDER);
    foreach ($found as $attribute) {
      $name = strtolower($attribute[1]);
      $value = trim($attribute[3]);
      if (($name === 'href' || $name === 'src') && !preg_match('#^(https?://|/|uploads/|data:image/)#i', $value)) continue;
      if ($tag === 'span' && $name !== 'class') continue;
      if ($tag === 'a' && !in_array($name, ['href', 'title', 'target', 'rel', 'class'], true)) continue;
      if ($tag === 'img' && !in_array($name, ['src', 'alt', 'title', 'class'], true)) continue;
      $attributes .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
    }
    if ($tag === 'a' && str_contains($attributes, 'target="_blank"') && !str_contains($attributes, ' rel=')) $attributes .= ' rel="noopener noreferrer"';
    return '<' . $tag . $attributes . '>';
  }, $clean);

  return $clean ?? '';
}

function redirect($url)
{
  header("Location: $url");
  exit;
}

function formatTanggal($tanggal)
{
  if (empty($tanggal) || $tanggal == "0000-00-00") return "-";
  $bulan = [1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
  $p = explode("-", $tanggal);
  return $p[2] . " " . $bulan[(int)$p[1]] . " " . $p[0];
}

function formatJam($jam)
{
  return date("H:i", strtotime($jam));
}

function generateKodeSatpam(mysqli $conn): string
{
  $result = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(kode_satpam, 4) AS UNSIGNED)) AS nomor FROM users WHERE kode_satpam LIKE 'STP%'");
  $nomor = (int) (mysqli_fetch_assoc($result)['nomor'] ?? 0) + 1;
  return 'STP' . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
}

/** Menyimpan gambar dari unggahan secara aman; nama asli pengguna tidak pernah dipakai. */
function uploadGambarAman(array $file, string $folderRelatif, int $maksUkuran = 2097152): ?string
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '') || ($file['size'] ?? 0) < 1 || ($file['size'] ?? 0) > $maksUkuran) return null;

  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
  $ekstensi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  if (!isset($ekstensi[$mime]) || @getimagesize($file['tmp_name']) === false) return null;

  $folder = dirname(__DIR__) . '/uploads/' . trim($folderRelatif, '/');
  if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) return null;
  try {
    $nama = bin2hex(random_bytes(16)) . '.' . $ekstensi[$mime];
  } catch (Throwable) {
    return null;
  }

  return move_uploaded_file($file['tmp_name'], $folder . '/' . $nama) ? $nama : null;
}

function uploadFoto(array $file): ?string
{
  return uploadGambarAman($file, 'foto');
}

function uploadTTD(array $file): ?string
{
  return uploadGambarAman($file, 'ttd');
}

/** Unggah buku saku secara ketat: hanya dokumen PDF hingga 10 MB. */
function uploadBukuSakuPdf(array $file): array
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    return ['ok' => false, 'message' => 'Silakan pilih file PDF.'];
  }

  if (($file['size'] ?? 0) < 1 || $file['size'] > 10 * 1024 * 1024) {
    return ['ok' => false, 'message' => 'Ukuran file PDF maksimal 10 MB.'];
  }

  if (strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION)) !== 'pdf') {
    return ['ok' => false, 'message' => 'File harus berformat PDF.'];
  }

  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
  $handle = @fopen($file['tmp_name'], 'rb');
  $signature = $handle ? fread($handle, 4) : '';
  if ($handle) {
    fclose($handle);
  }

  if ($mime !== 'application/pdf' || $signature !== '%PDF') {
    return ['ok' => false, 'message' => 'File yang diunggah bukan dokumen PDF yang valid.'];
  }

  $folder = dirname(__DIR__) . '/uploads/buku_saku';
  if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
    return ['ok' => false, 'message' => 'Folder buku saku tidak dapat dibuat.'];
  }

  try {
    $namaFile = bin2hex(random_bytes(16)) . '.pdf';
  } catch (Throwable) {
    return ['ok' => false, 'message' => 'Nama file buku tidak dapat dibuat.'];
  }

  if (!move_uploaded_file($file['tmp_name'], $folder . '/' . $namaFile)) {
    return ['ok' => false, 'message' => 'File PDF gagal diunggah.'];
  }

  return [
    'ok' => true,
    'nama_file' => $namaFile,
    'path_file' => 'uploads/buku_saku/' . $namaFile,
    'ukuran_file' => (int) $file['size'],
  ];
}

/* =========================
   HELPER SHIFT
========================= */

function getLaporanAktif(mysqli $conn, $idJadwal)
{
  $q = mysqli_prepare($conn, "
        SELECT *
        FROM laporan
        WHERE id_jadwal=?
        LIMIT 1
    ");
  mysqli_stmt_bind_param($q, "i", $idJadwal);
  mysqli_stmt_execute($q);
  return mysqli_fetch_assoc(mysqli_stmt_get_result($q));
}

function createLaporanShift(mysqli $conn, $idJadwal, $createdBy)
{
  $tanggal = date("Y-m-d");

  $q = mysqli_prepare($conn, "
        INSERT INTO laporan
        (
            id_jadwal,
            created_by,
            tanggal_laporan
        )
        VALUES
        (?,?,?)
    ");

  mysqli_stmt_bind_param(
    $q,
    "iis",
    $idJadwal,
    $createdBy,
    $tanggal
  );

  mysqli_stmt_execute($q);

  return mysqli_insert_id($conn);
}

function isAnggotaShift(mysqli $conn, $idLaporan, $idSatpam)
{
  $q = mysqli_prepare($conn, "
        SELECT id_anggota
        FROM anggota_shift
        WHERE id_laporan=?
        AND id_satpam=?
        LIMIT 1
    ");
  mysqli_stmt_bind_param($q, "ii", $idLaporan, $idSatpam);
  mysqli_stmt_execute($q);
  $r = mysqli_stmt_get_result($q);
  return mysqli_num_rows($r) > 0;
}

function tambahAnggotaShift(mysqli $conn, $idLaporan, $idSatpam)
{

  if (isAnggotaShift($conn, $idLaporan, $idSatpam)) {
    return true;
  }

  $status = "sudah_login";

  $q = mysqli_prepare($conn, "
        INSERT INTO anggota_shift
        (
            id_laporan,
            id_satpam,
            status_login,
            login_at
        )
        VALUES
        (?,?,?,NOW())
    ");

  mysqli_stmt_bind_param(
    $q,
    "iis",
    $idLaporan,
    $idSatpam,
    $status
  );

  return mysqli_stmt_execute($q);
}

function updateLoginAnggota(mysqli $conn, $idLaporan, $idSatpam)
{

  $status = "sudah_login";

  $q = mysqli_prepare($conn, "
        UPDATE anggota_shift
        SET
            status_login=?,
            login_at=NOW()
        WHERE
            id_laporan=?
        AND
            id_satpam=?
    ");

  mysqli_stmt_bind_param(
    $q,
    "sii",
    $status,
    $idLaporan,
    $idSatpam
  );

  return mysqli_stmt_execute($q);
}

function getAnggotaShift(mysqli $conn, $idLaporan)
{

  $q = mysqli_prepare($conn, "
        SELECT
            u.id_user,
            u.nama
        FROM anggota_shift a
        INNER JOIN users u
            ON u.id_user=a.id_satpam
        WHERE a.id_laporan=?
        ORDER BY u.nama
    ");

  mysqli_stmt_bind_param($q, "i", $idLaporan);
  mysqli_stmt_execute($q);

  return mysqli_stmt_get_result($q);
}

function getNamaAnggotaShift(mysqli $conn, $idLaporan)
{

  $hasil = [];

  $r = getAnggotaShift($conn, $idLaporan);

  while ($d = mysqli_fetch_assoc($r)) {
    $hasil[] = $d["nama"];
  }

  return $hasil;
}
