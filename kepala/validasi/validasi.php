<?php
require_once "../../config/kepala_auth.php";
require_once "../../config/report_signature.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}
$id = (int)($_POST['id'] ?? 0);
$idUser = (int)$_SESSION['id_user'];
if ($id < 1 || !ensureLaporanTtdKepalaColumn($conn)) {
  $_SESSION['kepala_error'] = 'Sistem tidak dapat menyiapkan tanda tangan laporan.';
  header('Location: index.php');
  exit;
}
$query = mysqli_prepare($conn, "SELECT ttd FROM users WHERE id_user=? AND role='kepala' LIMIT 1");
mysqli_stmt_bind_param($query, 'i', $idUser);
mysqli_stmt_execute($query);
$kepala = mysqli_fetch_assoc(mysqli_stmt_get_result($query));
if (empty($kepala['ttd'])) {
  $_SESSION['kepala_error'] = 'Unggah tanda tangan Anda sebelum memvalidasi laporan.';
  header('Location: detail.php?id=' . $id);
  exit;
}
$ttd = $kepala['ttd'];
$update = mysqli_prepare($conn, "UPDATE laporan SET status='tervalidasi',validated_by=?,validated_at=NOW(),ttd_kepala=?,updated_at=NOW() WHERE id_laporan=? AND status='menunggu_validasi'");
mysqli_stmt_bind_param($update, 'isi', $idUser, $ttd, $id);
mysqli_stmt_execute($update);
if (mysqli_stmt_affected_rows($update) === 1) {
  logActivity($conn, 'Validasi laporan', 'laporan', $id);
  $_SESSION['kepala_success'] = 'Laporan berhasil divalidasi. Tanda tangan Kepala telah disimpan untuk cetak laporan.';
} else {
  $_SESSION['kepala_error'] = 'Laporan tidak dapat divalidasi atau sudah diproses.';
}
header('Location: index.php');
exit;
