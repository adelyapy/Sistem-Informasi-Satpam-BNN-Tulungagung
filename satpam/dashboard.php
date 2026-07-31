<?php
require_once "../config/satpam_auth.php";
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

<?php
$title = "Dashboard Satpam";
$base_url = "../";

include "../includes/header.php"; ?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">
<?php
include "../includes/navbar.php";
include "../includes/satpam_sidebar.php";
?>

<div class="main-content">

<div class="row align-items-center mb-5">

    <div class="col-lg-8">

        <h1 class="dashboard-title">
            DASHBOARD
        </h1>

        <p class="dashboard-sub">

            Selamat Datang,

            <strong><?= $nama ?></strong>

        </p>

    </div>

    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

        <button class="btn btn-tambah"
                data-bs-toggle="modal"
                data-bs-target="#modalTambahSatpam">

            <i class="bi bi-plus-lg"></i>

            Tambah Nama Satpam

        </button>

    </div>

</div>



<div class="container">
  


</div>

<div class="row">

<div class="col-lg-6 mb-4">

<div

class="card card-menu"

  onclick="location.href='buku_mutasi/inventaris.php'">

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

  onclick="location.href='buku_mutasi/uraian.php'">

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
<div class="row mt-4">

    <div class="col-12">

        <div class="card">

            <div class="card-header">

                <i class="bi bi-journal-text"></i>
                Laporan Hari Ini

            </div>

            <div class="card-body">

                <p><strong>Tanggal :</strong> <?= $laporan['tanggal']; ?></p>

                <p><strong>Shift :</strong> <?= $laporan['nama_shift']; ?></p>

                <p><strong>Jam :</strong> <?= $laporan['jam_mulai']; ?> - <?= $laporan['jam_selesai']; ?></p>

                <hr>

                <h6>Anggota Shift</h6>

                <ul class="mb-0">

                <?php while($row=mysqli_fetch_assoc($anggota)){ ?>

                    <li>
                        <i class="bi bi-person-check-fill text-primary"></i>
                        <?= $row['nama']; ?>
                    </li>

                <?php } ?>

                </ul>

            </div>

        </div>

    </div>

</div>

</div>


<div class="modal fade" id="modalTambahSatpam" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="tambah_anggota.php" method="POST">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus"></i>
                        Tambah Nama Satpam
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden"
                           name="id_laporan"
                           value="<?= $id_laporan ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama</label>

                        <select class="form-select"
                                name="id_satpam"
                                required>

                            <option value="">Pilih Nama</option>

                            <?php
                            $q = mysqli_query($conn,"
                                SELECT id_user,nama
                                FROM users
                                WHERE role='satpam'
                                ORDER BY nama
                            ");

                            while($row = mysqli_fetch_assoc($q)){
                            ?>

                                <option value="<?= $row['id_user'] ?>">
                                    <?= $row['nama'] ?>
                                </option>

                            <?php } ?>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shift</label>

                        <select class="form-select"
                                name="id_shift"
                                required>

                            <option value="">Pilih Shift</option>

                            <?php
                            $q = mysqli_query($conn,"
                                SELECT id_shift,nama_shift
                                FROM shift
                                ORDER BY id_shift
                            ");

                            while($row = mysqli_fetch_assoc($q)){
                            ?>

                                <option value="<?= $row['id_shift'] ?>">
                                    <?= $row['nama_shift'] ?>
                                </option>

                            <?php } ?>

                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary w-100">
                        Tambah
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

