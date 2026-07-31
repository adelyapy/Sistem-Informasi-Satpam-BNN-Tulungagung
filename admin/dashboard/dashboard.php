<?php
require_once "../../config/admin_auth.php";

include "../../includes/header.php";
?>

<link rel="stylesheet" href="../../assets/css/dashboard-admin.css">

<?php

include "../../includes/admin_sidebar.php";
function total($conn,$table,$where=''){
    $q=mysqli_query($conn,"SELECT COUNT(*) jml FROM $table $where");
    return mysqli_fetch_assoc($q)['jml'];
}

$totalLaporan = total($conn,'laporan');
$draft        = total($conn,'laporan',"WHERE status='draft'");
$validasi     = total($conn,'laporan',"WHERE status='tervalidasi'");
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

$qShiftHariIni = mysqli_query($conn,"
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
$chartData  = [];

$sqlChart = mysqli_query($conn,"
SELECT
DATE(tanggal_laporan) AS tgl,
COUNT(*) AS total
FROM laporan
WHERE tanggal_laporan >= DATE_SUB(CURDATE(),INTERVAL 6 DAY)
GROUP BY DATE(tanggal_laporan)
ORDER BY DATE(tanggal_laporan)
");

$dataChart = [];

while($r=mysqli_fetch_assoc($sqlChart)){
    $dataChart[$r['tgl']] = $r['total'];
}

for($i=6;$i>=0;$i--){

    $tgl = date("Y-m-d",strtotime("-".$i." day"));

    $chartLabel[] = date("d M",strtotime($tgl));

    $chartData[] = $dataChart[$tgl] ?? 0;

}

/* ==========================
   AKTIVITAS TERBARU
========================== */

$aktivitas = mysqli_query($conn,"
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

<div class="main bg-light">

<div class="container-fluid py-4">

<div class="dashboard-header mb-4">

    <h2 class="fw-bold mb-1">
        DASHBOARD
    </h2>

    <h5 class="text-secondary">
        Selamat Datang,
        <b><?= $namaAdmin ?></b>
    </h5>

</div>

<div class="row g-4">

    <!-- CARD 1 -->

    <div class="col-lg-3 col-md-6">

        <div class="dashboard-card card-blue">

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

        <div class="dashboard-card card-green">

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

        <div class="dashboard-card card-red">

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

        <div class="dashboard-card card-orange">

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

<div class="row g-4">

<div class="col-lg-3 col-md-6">

<a href="../buku_mutasi/index.php" class="quick-menu">

<i class="bi bi-clipboard2-check"></i>

<h5>Monitoring Laporan</h5>

<p>Lihat laporan satpam serta cetak laporan.</p>

</a>

</div>

<div class="col-lg-3 col-md-6">

<a href="../../satpam/index.php" class="quick-menu">

<i class="bi bi-calendar-week"></i>

<h5>Jadwal Shift</h5>

<p>Kelola satpam serta jadwal kerja.</p>

</a>

</div>

<div class="col-lg-3 col-md-6">

<a href="../buku_saku/index.php" class="quick-menu">

<i class="bi bi-book"></i>

<h5>Buku Saku</h5>

<p>Tambah dan perbarui buku saku.</p>

</a>

</div>

<div class="col-lg-3 col-md-6">

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

<?php while($s=mysqli_fetch_assoc($qShiftHariIni)){ ?>

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

          <div class="chart-container">

              <canvas id="laporanChart"></canvas>

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

          <ul class="activity-list">

            <?php while($a=mysqli_fetch_assoc($aktivitas)){ ?>

            <li>

              <i class="bi bi-dot"></i>

              <b><?= htmlspecialchars($a['nama'] ?? 'Satpam'); ?></b>

              <br>

              <small>

                Mengirim laporan

                <?= date('d M Y',strtotime($a['tanggal_laporan'])) ?>

              </small>

            </li>

            <?php } ?>

          </ul>

<ul class="activity-list">

<?php while($a=mysqli_fetch_assoc($aktivitas)){ ?>

<li>

<i class="bi bi-dot"></i>

<b><?= htmlspecialchars($a['nama'] ?? 'Satpam'); ?></b>

<br>

<small>

Mengirim laporan

<?= date('d M Y',strtotime($a['tanggal_laporan'])) ?>

</small>

</li>

<?php } ?>

</ul>

</div>

</div>

</div>

</div>

</div>

<script>

const ctx=document.getElementById('laporanChart');

new Chart(ctx,{

type:'line',

data:{

labels:<?= json_encode($chartLabel) ?>,

datasets:[{

label:'Jumlah Laporan',

data:<?= json_encode($chartData) ?>,

fill:true,

borderWidth:3,

borderColor:'#2F54EB',

backgroundColor:'rgba(47,84,235,.12)',

tension:.4,

pointRadius:5

}]

},

options:{
    responsive:true,
    maintainAspectRatio:false,

    plugins:{
        legend:{
            display:false
        }
    },

    scales:{
        y:{
            beginAtZero:true,
            ticks:{
                stepSize:1
            }
        }
    }
}

});

</script>

<div class="dashboard-footer text-center mt-5">

<small>

© 2026 BNN Tulungagung — Sistem Informasi Buku Mutasi Satpam

</small>

</div>

<?php include "../../includes/footer.php"; ?>
