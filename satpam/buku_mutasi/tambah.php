<?php
require_once '../../config/satpam_auth.php';

// Laporan aktif disiapkan saat login. Jadwal tidak lagi perlu dibuat admin lebih dahulu.
$idLaporan = (int) ($_SESSION['id_laporan'] ?? 0);
if ($idLaporan > 0) {
  header('Location: detail.php?id=' . $idLaporan);
  exit;
}

header('Location: ../dashboard.php');
exit;
