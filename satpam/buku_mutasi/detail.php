<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../../login.php");
    exit;
}

if ($_SESSION['role'] != 'satpam') {
    header("Location: ../../login.php");
    exit;
}

require "../../config/database.php";

$title = "Detail Buku Mutasi";
$base_url = "../../";
include "../../includes/header.php";

$id_user     = $_SESSION['id_user'];
$id_laporan  = $_SESSION['id_laporan'];


$id_laporan = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Ambil Data Laporan
|--------------------------------------------------------------------------
*/

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

JOIN anggota_shift a
ON a.id_laporan=l.id_laporan

WHERE

l.id_laporan='$id_laporan'
AND a.id_satpam='$id_user'

LIMIT 1
");

if (mysqli_num_rows($query) == 0) {

    echo "
    <script>

    alert('Laporan tidak ditemukan');

    window.location='index.php';

    </script>
    ";

    exit;
}

$laporan = mysqli_fetch_assoc($query);
$qAnggota = mysqli_query($conn,"
SELECT
    u.nama
FROM anggota_shift a
JOIN users u
ON a.id_satpam=u.id_user
WHERE a.id_laporan='$id_laporan'
ORDER BY u.nama
");

/*
|--------------------------------------------------------------------------
| Hitung Inventaris
|--------------------------------------------------------------------------
*/

$qInventaris = mysqli_query($conn, "
SELECT COUNT(*) jumlah
FROM inventaris
WHERE id_laporan='$id_laporan'
");

$inventaris = mysqli_fetch_assoc($qInventaris);

/*
|--------------------------------------------------------------------------
| Hitung Uraian
|--------------------------------------------------------------------------
*/

$qUraian = mysqli_query($conn, "
SELECT COUNT(*) jumlah
FROM uraian_kegiatan
WHERE id_laporan='$id_laporan'
");

$uraian = mysqli_fetch_assoc($qUraian);

/*
|--------------------------------------------------------------------------
| Badge Status
|--------------------------------------------------------------------------
*/

$statusBadge = "secondary";
$statusText = "Draft";

switch ($laporan['status']) {

    case "draft":

        $statusBadge = "secondary";
        $statusText = "Draft";

        break;

    case "menunggu_validasi":

        $statusBadge = "warning";
        $statusText = "Menunggu Validasi";

        break;

    case "tervalidasi":

        $statusBadge = "success";
        $statusText = "Tervalidasi";

        break;
}

?>

<div class="wrapper">

    <?php include "../../includes/satpam_sidebar.php"; ?>

    <div class="main">

        <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h3 class="fw-bold mb-1">
            Detail Buku Mutasi
        </h3>

        <small class="text-muted">
            Detail laporan buku mutasi satpam
        </small>

    </div>

    <a href="index.php" class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>

        Kembali

    </a>

</div>


<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="bi bi-file-earmark-text"></i>

            Informasi Laporan

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="fw-semibold text-muted">
                    Tanggal Laporan
                </label>

                <div class="fs-6">

                    <?= date('d F Y', strtotime($laporan['tanggal_laporan'])); ?>

                </div>

            </div>

            <div class="col-md-6 mb-3">

                <label class="fw-semibold text-muted">
                    Status
                </label>

                <div>

                    <span class="badge bg-<?= $statusBadge; ?> fs-6">

                        <?= $statusText; ?>

                    </span>

                </div>

            </div>

            <div class="col-md-6 mb-3">

                <label class="fw-semibold text-muted">
                    Shift
                </label>

                <div>

                    <?= htmlspecialchars($laporan['nama_shift']); ?>

                </div>

            </div>

            <div class="col-md-12 mb-3">

              <label class="fw-semibold text-muted">
                  Anggota Shift
              </label>

              <div>

                  <?php

                  $nama=[];

                  while($a=mysqli_fetch_assoc($qAnggota)){

                      $nama[]=$a['nama'];

                  }

                  echo implode(", ",$nama);

                  ?>

              </div>

            </div>

            <div class="col-md-6 mb-3">

                <label class="fw-semibold text-muted">
                    Jam Shift
                </label>

                <div>

                    <?= substr($laporan['jam_mulai'],0,5); ?>

                    -

                    <?= substr($laporan['jam_selesai'],0,5); ?>

                </div>

            </div>

            <div class="col-md-6">

                <label class="fw-semibold text-muted">
                    Inventaris
                </label>

                <div>

                    <?= $inventaris['jumlah']; ?>

                    Barang

                </div>

            </div>

            <div class="col-md-6">

                <label class="fw-semibold text-muted">
                    Uraian Kegiatan
                </label>

                <div>

                    <?= $uraian['jumlah']; ?>

                    Kegiatan

                </div>

            </div>

        </div>

    </div>

</div>

<div class="row">

    <!-- CARD INVENTARIS -->

    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center">

                <div class="mb-3">

                    <i class="bi bi-box-seam text-primary"
                        style="font-size:60px;"></i>

                </div>

                <h4 class="fw-bold">

                    Inventaris

                </h4>

                <p class="text-muted mb-3">

                    Jumlah Inventaris

                </p>

                <h2 class="fw-bold text-primary">

                    <?= $inventaris['jumlah']; ?>

                </h2>

                <p class="text-muted">

                    Barang

                </p>

                <hr>

                <?php if ($laporan['status'] == 'draft') { ?>

                    <a href="inventaris.php"
                        class="btn btn-primary">

                        <i class="bi bi-pencil-square"></i>

                        Kelola Inventaris

                    </a>

                <?php } else { ?>

                    <a href="inventaris.php?id=<?= $id_laporan; ?>"
                        class="btn btn-outline-primary">

                        <i class="bi bi-eye"></i>

                        Lihat Inventaris

                    </a>

                <?php } ?>

            </div>

        </div>

    </div>


    <!-- CARD URAIAN -->

    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center">

                <div class="mb-3">

                    <i class="bi bi-journal-text text-success"
                        style="font-size:60px;"></i>

                </div>

                <h4 class="fw-bold">

                    Uraian Kegiatan

                </h4>

                <p class="text-muted mb-3">

                    Jumlah Kegiatan

                </p>

                <h2 class="fw-bold text-success">

                    <?= $uraian['jumlah']; ?>

                </h2>

                <p class="text-muted">

                    Kegiatan

                </p>

                <hr>

                <?php if ($laporan['status'] == 'draft') { ?>

                    <a href="uraian.php"
                        class="btn btn-success">

                        <i class="bi bi-pencil-square"></i>

                        Kelola Uraian

                    </a>

                <?php } else { ?>

                    <a href="uraian.php?id=<?= $id_laporan; ?>"
                        class="btn btn-outline-success">

                        <i class="bi bi-eye"></i>

                        Lihat Uraian

                    </a>

                <?php } ?>

            </div>

        </div>

    </div>

</div>

<?php if ($laporan['status'] == 'draft') { ?>

    <div class="card border-0 shadow-sm">

        <div class="card-body text-center">

            <h5 class="fw-bold mb-3">

                Laporan Siap Dikirim

            </h5>

            <p class="text-muted mb-4">

                Pastikan seluruh data inventaris dan uraian kegiatan telah diisi
                sebelum mengirim laporan kepada Kepala BNN.

            </p>

            <a href="kirim.php>"
                class="btn btn-warning btn-lg">

                <i class="bi bi-send-fill"></i>

                Kirim Laporan

            </a>

        </div>

    </div>

<?php } elseif ($laporan['status'] == 'menunggu_validasi') { ?>

    <div class="alert alert-warning shadow-sm">

        <div class="d-flex align-items-center">

            <i class="bi bi-hourglass-split fs-3 me-3"></i>

            <div>

                <strong>

                    Menunggu Validasi

                </strong>

                <br>

                Laporan sudah dikirim dan sedang menunggu validasi Kepala BNN.

            </div>

        </div>

    </div>

<?php } elseif ($laporan['status'] == 'tervalidasi') { ?>

    <div class="alert alert-success shadow-sm">

        <div class="d-flex align-items-center">

            <i class="bi bi-patch-check-fill fs-3 me-3"></i>

            <div>

                <strong>

                    Laporan Tervalidasi

                </strong>

                <br>

                Laporan telah divalidasi oleh Kepala BNN.

            </div>

        </div>

    </div>

<?php } ?>


<div class="text-end mt-4">

    <a href="index.php"
        class="btn btn-secondary">

        <i class="bi bi-arrow-left-circle"></i>

        Kembali

    </a>

</div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>