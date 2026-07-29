<?php
session_start();
if(!isset($_SESSION['login'])||$_SESSION['role']!='kepala'){header("Location:../../login.php");exit;}
require "../../config/database.php";
include "../../includes/header.php";
$id=(int)$_GET['id'];
$l=mysqli_fetch_assoc(mysqli_query($conn,"SELECT l.*,u.nama,j.tanggal,s.nama_shift FROM laporan l JOIN users u ON l.created_by=u.id_user JOIN jadwal_shift j ON l.id_jadwal=j.id_jadwal JOIN shift s ON j.id_shift=s.id_shift WHERE id_laporan='$id'"));
?>
<div class="container py-4">
<h3>Detail Laporan</h3>
<div class="card mb-3"><div class="card-body">
<p><b>Satpam:</b> <?= htmlspecialchars($l['nama']) ?></p>
<p><b>Tanggal:</b> <?= $l['tanggal'] ?></p>
<p><b>Shift:</b> <?= htmlspecialchars($l['nama_shift']) ?></p>
</div></div>

<h5>Inventaris</h5>
<table class="table table-bordered">
<tr><th>No</th><th>Barang</th><th>Jumlah</th><th>Keterangan</th></tr>
<?php $q=mysqli_query($conn,"SELECT * FROM inventaris WHERE id_laporan='$id' ORDER BY urutan");
while($r=mysqli_fetch_assoc($q)){ ?>
<tr><td><?= $r['urutan'] ?></td><td><?= htmlspecialchars($r['nama_barang']) ?></td><td><?= $r['jumlah'] ?></td><td><?= htmlspecialchars($r['keterangan']) ?></td></tr>
<?php } ?>
</table>

<h5>Uraian Kegiatan</h5>
<table class="table table-bordered">
<tr><th>No</th><th>Jam</th><th>Uraian</th></tr>
<?php $q=mysqli_query($conn,"SELECT * FROM uraian_kegiatan WHERE id_laporan='$id' ORDER BY urutan");
while($r=mysqli_fetch_assoc($q)){ ?>
<tr><td><?= $r['urutan'] ?></td><td><?= $r['jam'] ?></td><td><?= htmlspecialchars($r['uraian']) ?></td></tr>
<?php } ?>
</table>

<a href="validasi.php?id=<?= $id ?>" class="btn btn-success" onclick="return confirm('Validasi laporan ini?')">Validasi</a>
<a href="index.php" class="btn btn-secondary">Kembali</a>
</div>
<?php include "../../includes/footer.php"; ?>