<?php

require_once "../config/satpam_auth.php";

$id_laporan = $_POST['id_laporan'];
$id_satpam  = $_POST['id_satpam'];

// Cek apakah satpam sudah ditambahkan ke laporan
$cek = mysqli_query($conn, "
SELECT *
FROM anggota_shift
WHERE id_laporan='$id_laporan'
AND id_satpam='$id_satpam'
");

if (mysqli_num_rows($cek) == 0) {

  mysqli_query($conn, "
    INSERT INTO anggota_shift
    (
        id_laporan,
        id_satpam
    )
    VALUES
    (
        '$id_laporan',
        '$id_satpam'
    )
    ");
}

header("Location: dashboard.php");
exit;
