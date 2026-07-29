<?php
session_start();
if(!isset($_SESSION['login'])||$_SESSION['role']!='kepala'){header("Location:../../login.php");exit;}
require "../../config/database.php";
include "../../includes/header.php";
?>
<div class="container py-4">
<h3>Validasi Laporan</h3>
<table class="table table-bordered">
<thead><tr><th>No</th><th>Tanggal</th><th>Satpam</th><th>Shift</th><th>Aksi</th></tr></thead>
<tbody>
<?php
$no=1;
$q=mysqli_query($conn,"SELECT l.id_laporan,j.tanggal,u.nama,s.nama_shift
FROM laporan l
JOIN jadwal_shift j ON l.id_jadwal=j.id_jadwal
JOIN users u ON l.created_by=u.id_user
JOIN shift s ON j.id_shift=s.id_shift
WHERE l.status='menunggu_validasi'
ORDER BY j.tanggal DESC");
while($r=mysqli_fetch_assoc($q)){ ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $r['tanggal'] ?></td>
<td><?= htmlspecialchars($r['nama']) ?></td>
<td><?= htmlspecialchars($r['nama_shift']) ?></td>
<td><a class="btn btn-primary btn-sm" href="detail.php?id=<?= $r['id_laporan'] ?>">Detail</a></td>
</tr>
<?php } ?>
</tbody></table></div><?php include "../../includes/footer.php"; ?>