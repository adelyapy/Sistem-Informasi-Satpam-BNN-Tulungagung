<?php
require_once "../../config/admin_auth.php";

$title = 'Detail Laporan';
$pageTitle = 'Detail Laporan';
$base_url = '../../';
$activeMenu = 'monitoring_laporan';

if (!isset($_GET['id'])) {
  header("Location:index.php");
  exit;
}

$id = (int)$_GET['id'];

$query = mysqli_query($conn, "
SELECT

laporan.*,

users.nama,

users.kode_satpam,

jadwal_shift.tanggal,

shift.nama_shift,

shift.jam_mulai,

shift.jam_selesai

FROM laporan

LEFT JOIN users
ON users.id_user = laporan.created_by

LEFT JOIN jadwal_shift
ON jadwal_shift.id_jadwal = laporan.id_jadwal

LEFT JOIN shift
ON shift.id_shift = jadwal_shift.id_shift

WHERE laporan.id_laporan = '$id'
");

if (mysqli_num_rows($query) == 0) {

  header("Location:index.php");
  exit;
}

$data = mysqli_fetch_assoc($query);

$inventaris = mysqli_query($conn, "
SELECT *
FROM inventaris
WHERE id_laporan='$id'
ORDER BY urutan ASC
");

$uraian = mysqli_query($conn, "
SELECT *
FROM uraian_kegiatan
WHERE id_laporan='$id'
ORDER BY urutan ASC
");

include "../../includes/header.php";
?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">
<?php
include "../../includes/admin_navbar.php";
include "../../includes/admin_sidebar.php";
?>

<div class="main bg-light">

  <div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

      <div>

        <h2 class="fw-bold">

          Detail Laporan

        </h2>

        <p class="text-secondary mb-0">

          Buku Mutasi Satpam

        </p>

      </div>

      <div>

        <a
          href="index.php"
          class="btn btn-secondary">

          <i class="bi bi-arrow-left"></i>

          Kembali

        </a>

        <?php if ($data['status'] == "tervalidasi") { ?>

          <a href="cetak.php?id=<?= $data['id_laporan'] ?>"
            class="btn btn-success">

            <i class="bi bi-printer"></i>

            Cetak

          </a>

        <?php } ?>

      </div>

    </div>

    <div class="card shadow-sm border-0 mb-4">

      <div class="card-header bg-primary text-white">

        Informasi Laporan

      </div>

      <div class="card-body">

        <div class="row">

          <div class="col-md-6 mb-3">

            <label class="fw-bold">

              Tanggal

            </label>

            <div>

              <?= date('d F Y', strtotime($data['tanggal_laporan'])) ?>

            </div>

          </div>

          <div class="col-md-6 mb-3">

            <label class="fw-bold">

              Kode Satpam

            </label>

            <div>

              <?= htmlspecialchars($data['kode_satpam']) ?>

            </div>

          </div>

          <div class="col-md-6 mb-3">

            <label class="fw-bold">

              Nama Satpam

            </label>

            <div>

              <?= htmlspecialchars($data['nama']) ?>

            </div>

          </div>

          <div class="col-md-6 mb-3">

            <label class="fw-bold">

              Shift

            </label>

            <div>

              <?= htmlspecialchars($data['nama_shift']) ?>

              (
              <?= substr($data['jam_mulai'], 0, 5) ?>

              -

              <?= substr($data['jam_selesai'], 0, 5) ?>

              )

            </div>

          </div>

          <div class="col-md-6">

            <label class="fw-bold">

              Status

            </label>

            <div>

              <?php

              switch ($data['status']) {

                case 'draft':

                  echo "<span class='badge bg-secondary'>Draft</span>";

                  break;

                case 'menunggu_validasi':

                  echo "<span class='badge bg-warning text-dark'>Menunggu Validasi</span>";

                  break;

                case 'tervalidasi':

                  echo "<span class='badge bg-success'>Tervalidasi</span>";

                  break;
              }

              ?>

            </div>

          </div>

        </div>

      </div>

    </div>

    <div class="card shadow-sm border-0 mb-4">

      <div class="card-header bg-primary text-white">

        Uraian Kegiatan

      </div>

      <div class="table-responsive">

        <table class="table table-bordered mb-0">

          <thead>

            <tr>

              <th width="60">No</th>
              <th width="100">Jam</th>
              <th>Uraian</th>

            </tr>

          </thead>

          <tbody>

            <?php

            if (mysqli_num_rows($uraian) > 0) {

              $no = 1;

              while ($u = mysqli_fetch_assoc($uraian)) {

            ?>

                <tr>

                  <td><?= $no++ ?></td>

                  <td><?= substr($u['jam'], 0, 5) ?></td>

                  <td><?= nl2br(htmlspecialchars($u['uraian'])) ?></td>

                </tr>

              <?php

              }
            } else {

              ?>

              <tr>

                <td colspan="3" class="text-center">

                  Belum ada uraian kegiatan.

                </td>

              </tr>

            <?php } ?>

          </tbody>

        </table>

      </div>

    </div>

    <div class="card shadow-sm border-0">

      <div class="card-header bg-primary text-white">

        Inventaris

      </div>

      <div class="table-responsive">

        <table class="table table-bordered mb-0">

          <thead>

            <tr>

              <th width="60">

                No

              </th>

              <th>

                Nama Barang

              </th>

              <th width="100">

                Jumlah

              </th>

              <th>

                Keterangan

              </th>

            </tr>

          </thead>

          <tbody>

            <?php

            $no = 1;

            if (mysqli_num_rows($inventaris) > 0) {

              while ($i = mysqli_fetch_assoc($inventaris)) {

            ?>

                <tr>

                  <td><?= $no++ ?></td>

                  <td><?= htmlspecialchars($i['nama_barang']) ?></td>

                  <td><?= $i['jumlah'] ?></td>

                  <td><?= htmlspecialchars($i['keterangan']) ?></td>

                </tr>

              <?php }
            } else {

              ?>

              <tr>

                <td colspan="4" class="text-center">

                  Belum ada data inventaris.

                </td>

              </tr>

            <?php } ?>

          </tbody>

        </table>

      </div>

    </div>

  </div>

</div>

<?php include "../../includes/footer.php"; ?>