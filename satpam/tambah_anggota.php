<?php

require_once '../config/satpam_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: dashboard.php');
  exit;
}

$idLaporan = filter_input(INPUT_POST, 'id_laporan', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$idSatpam = filter_input(INPUT_POST, 'id_satpam', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$idPengaju = (int) ($_SESSION['id_user'] ?? 0);

if (!$idLaporan || !$idSatpam || $idPengaju < 1) {
  $_SESSION['anggota_error'] = 'Data anggota shift tidak valid.';
  header('Location: dashboard.php');
  exit;
}

if ($idSatpam === $idPengaju) {
  $_SESSION['anggota_error'] = 'Anda sudah menjadi anggota pada laporan ini dan tidak dapat menambahkan diri sendiri lagi.';
  header('Location: dashboard.php');
  exit;
}

// Hanya anggota laporan aktif yang dapat menambah rekan pada laporan yang sama.
$laporanStmt = mysqli_prepare($conn, '
  SELECT l.id_laporan
  FROM laporan l
  INNER JOIN anggota_shift anggota ON anggota.id_laporan = l.id_laporan
  WHERE l.id_laporan = ? AND anggota.id_satpam = ? AND l.status = \'draft\'
  LIMIT 1
');
mysqli_stmt_bind_param($laporanStmt, 'ii', $idLaporan, $idPengaju);
mysqli_stmt_execute($laporanStmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt))) {
  $_SESSION['anggota_error'] = 'Laporan tidak tersedia atau sudah difinalisasi.';
  header('Location: dashboard.php');
  exit;
}

$satpamStmt = mysqli_prepare($conn, "SELECT id_user FROM users WHERE id_user = ? AND role = 'satpam' AND status = 'aktif' LIMIT 1");
mysqli_stmt_bind_param($satpamStmt, 'i', $idSatpam);
mysqli_stmt_execute($satpamStmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($satpamStmt))) {
  $_SESSION['anggota_error'] = 'Satpam yang dipilih tidak tersedia atau tidak aktif.';
  header('Location: dashboard.php');
  exit;
}

$cekAnggota = mysqli_prepare($conn, 'SELECT id_anggota FROM anggota_shift WHERE id_laporan = ? AND id_satpam = ? LIMIT 1');
mysqli_stmt_bind_param($cekAnggota, 'ii', $idLaporan, $idSatpam);
mysqli_stmt_execute($cekAnggota);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($cekAnggota))) {
  $_SESSION['anggota_error'] = 'Satpam tersebut sudah menjadi anggota shift pada laporan ini.';
  header('Location: dashboard.php');
  exit;
}

try {
  $statusLogin = 'belum_login';
  $tambah = mysqli_prepare($conn, '
    INSERT INTO anggota_shift (id_laporan, id_satpam, status_login)
    VALUES (?, ?, ?)
  ');
  mysqli_stmt_bind_param($tambah, 'iis', $idLaporan, $idSatpam, $statusLogin);
  mysqli_stmt_execute($tambah);

  logActivity($conn, 'Tambah data', 'anggota_shift', $idLaporan);
  $_SESSION['anggota_success'] = 'Anggota shift berhasil ditambahkan ke laporan yang sama.';
} catch (mysqli_sql_exception $exception) {
  appLog($exception);
  $_SESSION['anggota_error'] = 'Anggota shift tidak dapat ditambahkan. Pastikan satpam belum terdaftar pada laporan ini.';
}

header('Location: dashboard.php');
exit;
