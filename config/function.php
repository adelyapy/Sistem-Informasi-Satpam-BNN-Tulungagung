<?php
require_once "database.php";

/* =========================
   FUNGSI LAMA
========================= */

function e($string)
{
  return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
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

function uploadFoto(array $file): ?string
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 2 * 1024 * 1024) return null;
  $allowed = ['jpg', 'jpeg', 'png'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed, true)) return null;
  $folder = dirname(__DIR__) . '/uploads/foto';
  if (!is_dir($folder)) mkdir($folder, 0755, true);
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  return move_uploaded_file($file['tmp_name'], $folder . '/' . $name) ? $name : null;
}

function uploadTTD(array $file): ?string
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 2 * 1024 * 1024) return null;
  $allowed = ['jpg', 'jpeg', 'png'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed, true)) return null;
  $folder = dirname(__DIR__) . '/uploads/ttd';
  if (!is_dir($folder)) mkdir($folder, 0755, true);
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  return move_uploaded_file($file['tmp_name'], $folder . '/' . $name) ? $name : null;
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
