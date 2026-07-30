<?php
require_once "../../config/admin_auth.php";

include "../../includes/header.php";
include "../../includes/admin_sidebar.php";

function total($conn,$table,$where=''){
    $q=mysqli_query($conn,"SELECT COUNT(*) jml FROM $table $where");
    return mysqli_fetch_assoc($q)['jml'];
}

$totalLaporan = total($conn,'laporan');
$draft = total($conn,'laporan',"WHERE status='draft'");
$validasi = total($conn,'laporan',"WHERE status='menunggu_validasi'");
$selesai = total($conn,'laporan',"WHERE status='tervalidasi'");
?>

<div class="main">
<div class="container-fluid py-4">

<h3 class="mb-4">Dashboard Admin</h3>

<div class="row g-3">

<div class="col-md-3">
<div class="card text-center"><div class="card-body">
<h5>Total Laporan</h5>
<h2><?= $totalLaporan; ?></h2>
</div></div>
</div>

<div class="col-md-3">
<div class="card text-center"><div class="card-body">
<h5>Draft</h5>
<h2><?= $draft; ?></h2>
</div></div>
</div>

<div class="col-md-3">
<div class="card text-center"><div class="card-body">
<h5>Menunggu Validasi</h5>
<h2><?= $validasi; ?></h2>
</div></div>
</div>

<div class="col-md-3">
<div class="card text-center"><div class="card-body">
<h5>Tervalidasi</h5>
<h2><?= $selesai; ?></h2>
</div></div>
</div>

</div>

</div>
</div>

<?php include "../../includes/footer.php"; ?>
