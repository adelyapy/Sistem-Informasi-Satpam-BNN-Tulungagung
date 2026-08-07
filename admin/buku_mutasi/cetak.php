<?php
require_once "../../config/admin_auth.php";
require_once "../../config/report_signature.php";

ensureLaporanTtdKepalaColumn($conn);
ensureLaporanTtdSatpamColumn($conn);

if (!isset($_GET['id'])) {
  header("Location:index.php");
  exit;
}

$id = (int) $_GET['id'];

$query = mysqli_query($conn, "

SELECT

laporan.*,

users.nama,
users.kode_satpam,

jadwal_shift.tanggal,

shift.nama_shift,
shift.jam_mulai,
shift.jam_selesai

,validator.nama AS nama_kepala
,laporan.ttd_kepala
,laporan.ttd_satpam

FROM laporan

LEFT JOIN users
ON users.id_user = laporan.created_by

LEFT JOIN jadwal_shift
ON jadwal_shift.id_jadwal = laporan.id_jadwal

LEFT JOIN shift
ON shift.id_shift = jadwal_shift.id_shift

LEFT JOIN users validator
ON validator.id_user = laporan.validated_by

WHERE laporan.id_laporan='$id'

LIMIT 1

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
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">

  <title>
    Cetak Laporan
  </title>

  <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {

      font-size: 13px;

    }

    .header {

      text-align: center;

      margin-bottom: 20px;

    }

    .header h4 {

      margin: 0;

      font-weight: bold;

    }

    .header p {

      margin: 0;

    }

    .info {

      margin-top: 20px;

      margin-bottom: 20px;

    }

    .table {

      font-size: 13px;

    }

    .table th {

      background: #f2f2f2;

    }

    @media print {

      button {

        display: none;

      }

    }
  </style>

</head>

<body>
  <div class="container mt-4">
    <div class="text-end mb-3">
      <button onclick="window.print()" class="btn btn-success">
        Cetak
      </button>

      <button onclick="window.history.back()" class="btn btn-secondary">
        Kembali
      </button>
    </div>

    <div class="header">
      <h4>
        BUKU MUTASI SATPAM
      </h4>

      <p>
        Laporan Kegiatan Satpam
      </p>

      <hr>
    </div>

    <table class="table table-borderless info">
      <tr>
        <td width="180">
          Tanggal
        </td>

        <td>
          :
          <?= date('d F Y', strtotime($data['tanggal_laporan'])) ?>
        </td>
      </tr>

      <tr>
        <td>
          Kode Satpam
        </td>

        <td>
          :
          <?= htmlspecialchars($data['kode_satpam']) ?>
        </td>
      </tr>

      <tr>
        <td>
          Nama Satpam
        </td>

        <td>
          :
          <?= htmlspecialchars($data['nama']) ?>
        </td>
      </tr>

      <tr>
        <td>
          Shift
        </td>

        <td>
          :
          <?= htmlspecialchars($data['nama_shift']) ?>
          (
          <?= substr($data['jam_mulai'], 0, 5) ?>
          -
          <?= substr($data['jam_selesai'], 0, 5) ?>
          )
        </td>
      </tr>
    </table>

    <h5 class="mt-4 mb-3">
      Uraian Kegiatan
    </h5>

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th width="60">No</th>

            <th width="120">Jam</th>

            <th>Uraian</th>

          </tr>

        </thead>

        <tbody>

          <?php

          $no = 1;

          if (mysqli_num_rows($uraian) > 0) {

            while ($u = mysqli_fetch_assoc($uraian)) {

          ?>

              <tr>

                <td class="text-center">

                  <?= $no++ ?>
                </td>

                <td class="text-center">
                  <?= substr($u['jam'], 0, 5) ?>
                </td>

                <td>
                  <?= nl2br(htmlspecialchars($u['uraian'])) ?>
                </td>

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

    <h5 class="mt-4 mb-3">
      Inventaris
    </h5>

    <div class="table-responsive">

      <table class="table table-bordered">

        <thead>

          <tr>

            <th width="60">No</th>
            <th>Nama Barang</th>
            <th width="100">Jumlah</th>
            <th>Keterangan</th>

          </tr>

        </thead>

        <tbody>

          <?php

          $no = 1;

          if (mysqli_num_rows($inventaris) > 0) {

            while ($i = mysqli_fetch_assoc($inventaris)) {

          ?>

              <tr>

                <td class="text-center">
                  <?= $no++ ?>
                </td>

                <td>
                  <?= htmlspecialchars($i['nama_barang']) ?>
                </td>

                <td class="text-center">
                  <?= $i['jumlah'] ?>
                </td>

                <td>
                  <?= htmlspecialchars($i['keterangan']) ?>
                </td>

              </tr>

            <?php

            }
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

    <br><br>
    <div class="row">
      <div class="col-6 text-center">
        Mengetahui,

        <br>


        <?php if ($data['status'] === 'tervalidasi' && !empty($data['ttd_kepala'])): ?>

          <img src="../../uploads/ttd/<?= rawurlencode($data['ttd_kepala']) ?>" alt="Tanda tangan Kepala BNN" style="max-width:150px;max-height:75px;object-fit:contain;margin:10px 0;">

        <?php else: ?>

          <br><br><br>

        <?php endif;

        ?>

        <br>
        _________________________

        <br>

        <?= htmlspecialchars($data['nama_kepala'] ?: 'Kepala BNN') ?>

      </div>

      <div class="col-6 text-center">

        Malang,
        <?= date('d F Y') ?>

        <br>

        <?php if (!empty($data['ttd_satpam'])): ?>
          <img src="../../uploads/ttd/<?= rawurlencode($data['ttd_satpam']) ?>" alt="Tanda tangan Satpam" style="max-width:150px;max-height:75px;object-fit:contain;margin:10px 0;">
        <?php else: ?>
          <br><br><br>
        <?php endif; ?>

        <br>

        _________________________

        <br>

        Petugas Satpam

      </div>

    </div>

  </div>
  
  <script>
    window.onload = function() {

      window.print();

    };
  </script>

</body>

</html>