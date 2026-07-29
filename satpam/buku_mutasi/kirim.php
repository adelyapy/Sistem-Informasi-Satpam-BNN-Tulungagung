<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'satpam') {
    header("Location: ../../login.php");
    exit;
}

require "../../config/database.php";

$id_user     = $_SESSION['id_user'];
$id_laporan  = $_SESSION['id_laporan'];

if (empty($id_laporan)) {

    header("Location:index.php");
    exit;

}

$q = mysqli_query($conn,"
SELECT
    l.*
FROM laporan l

JOIN anggota_shift a
ON a.id_laporan=l.id_laporan

WHERE

l.id_laporan='$id_laporan'
AND a.id_satpam='$id_user'

LIMIT 1
");

if(mysqli_num_rows($q)==0){
    header("Location:index.php");
    exit;
}

$laporan = mysqli_fetch_assoc($q);

if ($laporan['status'] != 'draft') {
    echo "<script>
        alert('Laporan sudah dikirim.');
        window.location='detail.php';
    </script>";
    exit;
}

$inventaris = mysqli_num_rows(mysqli_query($conn,"
SELECT id_inventaris 
FROM inventaris
WHERE id_laporan='$id_laporan'
"));

$uraian = mysqli_num_rows(mysqli_query($conn,"
SELECT id_uraian
FROM uraian_kegiatan
WHERE id_laporan='$id_laporan'
"));

$errors = [];

if($inventaris == 0){
    $errors[] = "Inventaris belum diisi.";
}

if($uraian == 0){
    $errors[] = "Uraian kegiatan belum diisi.";
}

if(isset($_POST['kirim'])){

    if(count($errors) > 0){
        echo "<script>alert('Lengkapi data inventaris dan uraian terlebih dahulu.');history.back();</script>";
        exit;
    }

    mysqli_query($conn,"
    UPDATE laporan

    SET

    status='menunggu_validasi',
    updated_at=NOW()

    WHERE

    id_laporan='$id_laporan'
    ");

    echo "<script>
    alert('Laporan berhasil dikirim untuk validasi.');
    window.location='detail.php';
    </script>";
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kirim Laporan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
<div class="row justify-content-center">
<div class="col-lg-7">

<div class="card shadow">

<div class="card-header">
<h4 class="mb-0">Konfirmasi Pengiriman Laporan</h4>
</div>

<div class="card-body">

<p>Pastikan seluruh data laporan sudah lengkap.</p>

<ul class="list-group mb-4">

<li class="list-group-item d-flex justify-content-between">
<span>Inventaris</span>
<span class="badge <?= $inventaris>0?'bg-success':'bg-danger' ?>">
<?= $inventaris ?> Data
</span>
</li>

<li class="list-group-item d-flex justify-content-between">
<span>Uraian Kegiatan</span>
<span class="badge <?= $uraian>0?'bg-success':'bg-danger' ?>">
<?= $uraian ?> Data
</span>
</li>

</ul>

<?php if(count($errors)>0){ ?>

<div class="alert alert-danger">

<strong>Laporan belum dapat dikirim.</strong>

<ul class="mb-0 mt-2">
<?php foreach($errors as $e){ ?>
<li><?= htmlspecialchars($e) ?></li>
<?php } ?>
</ul>

</div>

<?php }else{ ?>

<div class="alert alert-success">
Semua data telah lengkap dan siap dikirim ke Kepala untuk divalidasi.
</div>

<?php } ?>

<form method="post" class="d-flex justify-content-end gap-2">

<a href="detail.php" class="btn btn-secondary">
Kembali
</a>

<button
type="submit"
name="kirim"
class="btn btn-primary"
<?= count($errors)>0?'disabled':''; ?>>
Kirim Laporan
</button>

</form>

</div>

</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
