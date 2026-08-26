<?php
require_once "../../config/admin_auth.php";

$title = "Dashboard Admin";
$pageTitle = "";
$base_url = "../../";
$activeMenu = "dashboard";

include "../../includes/header.php";
?>

<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard-admin.css">

<?php
include "../../includes/admin_navbar.php";
include "../../includes/admin_sidebar.php";
?>

<?php
function total($conn, $table, $where = '')
{
  $q = mysqli_query($conn, "SELECT COUNT(*) jml FROM $table $where");
  return mysqli_fetch_assoc($q)['jml'];
}

$totalLaporan = total($conn, 'laporan');
$draft        = total($conn, 'laporan', "WHERE status='draft'");
$validasi     = total($conn, 'laporan', "WHERE status='tervalidasi'");
$belum        = $totalLaporan - $validasi;

$totalSatpam  = total(
  $conn,
  "users",
  "WHERE role='satpam' AND status='aktif'"
);

/* =======================================
   JADWAL HARI INI
======================================= */

$today = date('Y-m-d');

$qShiftHariIni = mysqli_query($conn, "
SELECT
    shift.nama_shift,
    users.nama
FROM jadwal_shift
JOIN users
ON users.id_user=jadwal_shift.id_satpam

JOIN shift
ON shift.id_shift=jadwal_shift.id_shift

WHERE jadwal_shift.tanggal='$today'

ORDER BY shift.jam_mulai
");

/* =======================================
   RINGKASAN HARI INI
======================================= */

$laporanHariIni = total(
  $conn,
  "laporan",
  "WHERE tanggal_laporan='$today'"
);

$draftHariIni = total(
  $conn,
  "laporan",
  "WHERE tanggal_laporan='$today'
    AND status='draft'"
);

$validHariIni = total(
  $conn,
  "laporan",
  "WHERE tanggal_laporan='$today'
    AND status='tervalidasi'"
);

/* ==========================
   DATA GRAFIK 7 HARI TERAKHIR
========================== */

$chartLabel = [];
$chartData = [];
$dataChart = [];

$sqlChart = mysqli_query($conn, "
    SELECT DATE(tanggal_laporan) AS tgl, COUNT(*) AS total
    FROM laporan
    WHERE tanggal_laporan >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(tanggal_laporan)
");

while ($r = mysqli_fetch_assoc($sqlChart)) {
  $dataChart[$r['tgl']] = (int) $r['total'];
}

for ($day = 6; $day >= 0; $day--) {
  $tanggal = date('Y-m-d', strtotime("-$day day"));
  $chartLabel[] = date('d M', strtotime($tanggal));
  $chartData[] = $dataChart[$tanggal] ?? 0;
}

$chartMaximum = max(1, ...$chartData);

/* ==========================
   AKTIVITAS TERBARU
========================== */

$aktivitas = mysqli_query($conn, "
SELECT
laporan.tanggal_laporan,
laporan.status,
users.nama
FROM laporan
LEFT JOIN users
ON users.id_user=laporan.created_by
ORDER BY laporan.id_laporan DESC
LIMIT 5
");

$namaAdmin = $_SESSION['nama'] ?? 'Administrator';
?>

<main class="main-content">

  <div class="admin-dashboard">

    <div class="dashboard-header">

      <h1 class="dashboard-title">

        DASHBOARD

      </h1>

      <p class="dashboard-subtitle">

        Selamat Datang,

        <strong><?= $namaAdmin ?></strong>

        👋

      </p>

    </div>

    <div class="row g-4">

      <!-- CARD 1 -->

      <div class="col-lg-3 col-md-6">

        <div class="dashboard-card card-blue h-100">

          <div class="icon blue">

            <i class="bi bi-file-earmark-text"></i>

          </div>

          <div>

            <small>Total Laporan</small>

            <h2><?= $totalLaporan ?></h2>

            <span>Laporan</span>

          </div>

        </div>

      </div>

      <!-- CARD 2 -->

      <div class="col-lg-3 col-md-6">

        <div class="dashboard-card card-green h-100">

          <div class="icon green">

            <i class="bi bi-check-circle-fill"></i>

          </div>

          <div>

            <small>Sudah Validasi</small>

            <h2><?= $validasi ?></h2>

            <span>Laporan</span>

          </div>

        </div>

      </div>

      <!-- CARD 3 -->

      <div class="col-lg-3 col-md-6">

        <div class="dashboard-card card-red h-100">

          <div class="icon red">

            <i class="bi bi-x-circle-fill"></i>

          </div>

          <div>

            <small>Belum Validasi</small>

            <h2><?= $belum ?></h2>

            <span>Laporan</span>

          </div>

        </div>

      </div>

      <!-- CARD 4 -->

      <div class="col-lg-3 col-md-6">

        <div class="dashboard-card card-orange h-100">

          <div class="icon orange">

            <i class="bi bi-people-fill"></i>

          </div>

          <div>

            <small>Total Satpam</small>

            <h2><?= $totalSatpam ?></h2>

            <span>Personel</span>

          </div>

        </div>

      </div>

    </div>


    <!-- QUICK MENU -->

    <div class="mt-5">

      <div class="section-title">

        <h4 class="fw-bold m-0">

          Menu Cepat Administrator

        </h4>

      </div>

      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-3">

        <div class="col">

          <a href="../buku_mutasi/index.php" class="quick-menu">

            <i class="bi bi-clipboard2-check"></i>

            <h5>Monitoring Laporan</h5>

            <p>Lihat laporan satpam serta cetak laporan.</p>

          </a>

        </div>

        <div class="col">

          <a href="../shift/index.php" class="quick-menu">

            <i class="bi bi-calendar-week-fill"></i>

            <h5>Jadwal Satpam</h5>

            <p>Atur jadwal tugas dan shift satpam.</p>

          </a>

        </div>

        <div class="col">

          <a href="../../satpam/index.php" class="quick-menu">

            <i class="bi bi-people-fill"></i>

            <h5>Data Satpam</h5>

            <p>Kelola data seluruh anggota satpam.</p>

          </a>

        </div>

        <div class="col">

          <a href="../buku_saku/index.php" class="quick-menu">

            <i class="bi bi-book"></i>

            <h5>Buku Saku</h5>

            <p>Tambah dan perbarui buku saku.</p>

          </a>

        </div>

        <div class="col">

          <a href="../nomor_penting/index.php" class="quick-menu">

            <i class="bi bi-telephone-fill"></i>

            <h5>Nomor Penting</h5>

            <p>Kelola daftar nomor penting.</p>

          </a>

        </div>

      </div>

    </div>

    <!-- CHART + ACTIVITY -->
    <hr class="my-5">

    <div class="row g-4 mt-4">

      <div class="col-lg-8">

        <div class="dashboard-box">

          <div class="section-title">

            <h4 class="fw-bold m-0">

              <i class="bi bi-calendar-check"></i>

              Jadwal Shift Hari Ini

            </h4>

          </div>

          <table class="table table-hover">

            <thead>

              <tr>

                <th>Shift</th>

                <th>Satpam</th>

              </tr>

            </thead>

            <tbody>

              <?php while ($s = mysqli_fetch_assoc($qShiftHariIni)) { ?>

                <tr>

                  <td>

                    <?= htmlspecialchars($s['nama_shift']) ?>

                  </td>

                  <td>

                    <?= htmlspecialchars($s['nama']) ?>

                  </td>

                </tr>

              <?php } ?>

            </tbody>

          </table>

        </div>

      </div>

      <div class="col-lg-4">

        <div class="dashboard-box">

          <div class="section-title">

            <h4 class="fw-bold m-0">

              Ringkasan Hari Ini

            </h4>

          </div>

          <div class="summary-item">

            <span>Laporan Masuk</span>

            <b><?= $laporanHariIni ?></b>

          </div>

          <div class="summary-item">

            <span>Draft</span>

            <b><?= $draftHariIni ?></b>

          </div>

          <div class="summary-item">

            <span>Tervalidasi</span>

            <b><?= $validHariIni ?></b>

          </div>

        </div>

      </div>

    </div>


    <div class="row g-4 mt-4">

      <div class="col-lg-8">

        <div class="dashboard-box">

          <div class="section-title">

            <h4 class="fw-bold m-0">

              Grafik Aktivitas

            </h4>

          </div>

          <div class="activity-chart" role="img" aria-label="Grafik jumlah laporan tujuh hari terakhir">
            <?php foreach ($chartData as $index => $total): ?>
              <?php $height = max(10, (int) round(($total / $chartMaximum) * 170)); ?>
              <div class="activity-chart-column">
                <span class="activity-chart-value"><?= $total ?></span>
                <span class="activity-chart-bar" style="height: <?= $height ?>px"></span>
                <span class="activity-chart-label"><?= $chartLabel[$index] ?></span>
              </div>
            <?php endforeach; ?>
          </div>

        </div>

      </div>

    </div> <!-- row Jadwal -->

    <div class="row g-4 mt-4">

      <div class="col-lg-8">

        <div class="dashboard-box">

          <div class="section-title">

            <h4 class="fw-bold m-0">

              Aktivitas Terbaru

            </h4>

          </div>

          <div class="activity-wrapper">

            <?php while ($a = mysqli_fetch_assoc($aktivitas)) { ?>

              <div class="activity-item">

                <div class="activity-icon">
                  <i class="bi bi-person-fill"></i>
                </div>

                <div class="activity-content">
                  <h6><?= htmlspecialchars($a['nama'] ?? 'Satpam') ?></h6>

                  <small>
                    Mengirim laporan
                    <?= date('d M Y', strtotime($a['tanggal_laporan'])) ?>
                  </small>
                </div>

                <span class="badge bg-primary">
                  <?= ucfirst($a['status']) ?>
                </span>

              </div>

            <?php } ?>

          </div>

        </div>

      </div>

    </div>

  </div>

</main>

<footer class="dashboard-footer">
  © 2026 BNN Tulungagung
  <span>|</span>
  e-SATPAM — Elektronik Sistem Administrasi Satpam
</footer>

<?php include "../../includes/footer.php"; ?>
