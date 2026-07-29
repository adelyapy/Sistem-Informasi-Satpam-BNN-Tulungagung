<?php
session_start();

include "../../config/database.php";
include "../../config/session.php";

$id_user     = $_SESSION['id_user'];
$nama        = $_SESSION['nama'];
$id_laporan  = $_SESSION['id_laporan'];

$query = mysqli_query($conn,"
SELECT
l.*,
j.tanggal,
s.nama_shift,
s.jam_mulai,
s.jam_selesai

FROM laporan l

JOIN jadwal_shift j
ON l.id_jadwal=j.id_jadwal

JOIN shift s
ON j.id_shift=s.id_shift

WHERE l.id_laporan='$id_laporan'

LIMIT 1
");

$laporan=mysqli_fetch_assoc($query);


$anggota=mysqli_query($conn,"

SELECT

u.nama

FROM anggota_shift a

JOIN users u

ON a.id_satpam=u.id_user

WHERE a.id_laporan='$id_laporan'

ORDER BY u.nama

");

$jumlahAnggota=mysqli_num_rows($anggota);
?>

<!doctype html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Dashboard Satpam</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<body>
  <nav class="navbar navbar-custom">

<div class="container-fluid">

<div class="row w-100 align-items-center">

<div class="col-1">

<i class="bi bi-list menu-btn"></i>

</div>

<div class="col-7 d-flex align-items-center">

<img
src="../../assets/img/logo-bnn.png"
class="logo-bnn me-3">

<div>

<div class="title-app">

BUKU MUTASI SATPAM

</div>

<div class="subtitle-app">

BNN TULUNGAGUNG

</div>

</div>

</div>

<div class="col-4 text-end">

<i class="bi bi-person-circle fs-1"></i>

<span class="profile">

<?= $nama ?>

</span>

</div>

</div>

</div>

</nav>

<div class="container mt-5">

<div class="row">

<div class="col-md-8">

<div class="dashboard-title">

DASHBOARD

</div>

<div class="dashboard-sub">

Selamat Datang,

<b><?= $nama ?></b>

</div>

</div>

<div class="col-md-4 text-end">

<button

class="btn btn-tambah"

data-bs-toggle="modal"

data-bs-target="#modalTambahSatpam">

<i class="bi bi-plus-lg"></i>

Tambah Nama Satpam

</button>

</div>

</div>

<div class="row mt-5">

<div class="col-md-6">

<div

class="card card-menu"

onclick="location.href='inventaris.php'">

<div class="card-body">

<div class="row align-items-center">

<div class="col-3">

<i class="bi bi-box-seam icon-menu"></i>

</div>

<div class="col-9">

<div class="menu-title">

INPUT INVENTARIS

</div>

<div class="menu-desc">

Catat inventaris yang digunakan

</div>

</div>

</div>

</div>

</div>

</div>

<div class="col-md-6">

<div

class="card card-menu"

onclick="location.href='uraian.php'">

<div class="card-body">

<div class="row align-items-center">

<div class="col-3">

<i class="bi bi-clipboard2-check icon-menu"></i>

</div>

<div class="col-9">

<div class="menu-title">

INPUT URAIAN KEGIATAN

</div>

<div class="menu-desc">

Catat kegiatan dan lampiran

</div>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>