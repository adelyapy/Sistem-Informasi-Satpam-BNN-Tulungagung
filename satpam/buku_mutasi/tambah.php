<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'satpam') {
  header("Location: ../../login.php");
  exit;
}

require "../../config/database.php";

$id_user = $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Cari jadwal satpam hari ini
|--------------------------------------------------------------------------
*/

$tanggal = date('Y-m-d');

$qJadwal = mysqli_query($conn, "
SELECT
    j.*,
    s.nama_shift
FROM jadwal_shift j
JOIN shift s
    ON j.id_shift = s.id_shift
WHERE j.id_satpam='$id_user'
AND j.tanggal='$tanggal'
AND j.status='bertugas'
LIMIT 1
");

if (mysqli_num_rows($qJadwal) == 0) {

  echo "
    <script>
        alert('Anda tidak memiliki jadwal bertugas hari ini.');
        window.location='index.php';
    </script>
    ";

  exit;
}

$jadwal = mysqli_fetch_assoc($qJadwal);

$id_jadwal = $jadwal['id_jadwal'];

/*
|--------------------------------------------------------------------------
| Cek apakah laporan sudah dibuat
|--------------------------------------------------------------------------
*/

$qLaporan = mysqli_query($conn, "
SELECT *
FROM laporan
WHERE id_jadwal='$id_jadwal'
LIMIT 1
");

if (mysqli_num_rows($qLaporan) > 0) {

  $laporan = mysqli_fetch_assoc($qLaporan);

  header("Location: detail.php?id=" . $laporan['id_laporan']);
  exit;
}

/*
|--------------------------------------------------------------------------
| Simpan laporan baru
|--------------------------------------------------------------------------
*/

mysqli_query($conn, "
INSERT INTO laporan
(
    id_jadwal,
    created_by,
    tanggal_laporan,
    status,
    inventaris_selesai,
    uraian_selesai,
    created_at,
    updated_at
)
VALUES
(
    '$id_jadwal',
    '$id_user',
    '$tanggal',
    'draft',
    0,
    0,
    NOW(),
    NOW()
)
");

$id_laporan = mysqli_insert_id($conn);

/*
|--------------------------------------------------------------------------
| Redirect ke detail
|--------------------------------------------------------------------------
*/

header("Location: detail.php?id=" . $id_laporan);
exit;
