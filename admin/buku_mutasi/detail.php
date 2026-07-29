<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require "../../config/database.php";

$id = (int) $_GET['id'];

include "../../includes/header.php";
include "../../includes/admin_sidebar.php";

$laporan = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT l.*,u.nama,j.tanggal,s.nama_shift
FROM laporan l
JOIN users u ON l.created_by=u.id_user
JOIN jadwal_shift j ON l.id_jadwal=j.id_jadwal
JOIN shift s ON j.id_shift=s.id_shift
WHERE l.id_laporan='$id'
"));
?>

<div class="main">
<div class="container-fluid py-4">

<h3 class="mb-4">Detail Laporan</h3>

<div class="card mb-4">
<div class="card-body">
<p><strong>Satpam :</strong> <?= htmlspecialchars($laporan['nama']); ?></p>
<p><strong>Tanggal :</strong> <?= $laporan['tanggal']; ?></p>
<p><strong>Shift :</strong> <?= htmlspecialchars($laporan['nama_shift']); ?></p>
<p><strong>Status :</strong> <?= ucfirst(str_replace('_',' ',$laporan['status'])); ?></p>
</div>
</div>

<h5>Inventaris</h5>
<table class="table table-bordered mb-4">
<tr><th>No</th><th>Barang</th><th>Jumlah</th><th>Keterangan</th></tr>
<?php
$q=mysqli_query($conn,"SELECT * FROM inventaris WHERE id_laporan='$id' ORDER BY urutan");
while($r=mysqli_fetch_assoc($q)){
?>
<tr>
<td><?= $r['urutan']; ?></td>
<td><?= htmlspecialchars($r['nama_barang']); ?></td>
<td><?= $r['jumlah']; ?></td>
<td><?= htmlspecialchars($r['keterangan']); ?></td>
</tr>
<?php } ?>
</table>

<h5>Uraian Kegiatan</h5>
<table class="table table-bordered">
<tr><th>No</th><th>Jam</th><th>Uraian</th></tr>
<?php
$q=mysqli_query($conn,"SELECT * FROM uraian_kegiatan WHERE id_laporan='$id' ORDER BY urutan");
while($r=mysqli_fetch_assoc($q)){
?>
<tr>
<td><?= $r['urutan']; ?></td>
<td><?= $r['jam']; ?></td>
<td><?= htmlspecialchars($r['uraian']); ?></td>
</tr>
<?php } ?>
</table>

<a href="index.php" class="btn btn-secondary">Kembali</a>

</div>
</div>

<?php include "../../includes/footer.php"; ?>
