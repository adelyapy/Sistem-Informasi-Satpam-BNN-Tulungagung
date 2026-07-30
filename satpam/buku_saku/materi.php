<?php
require_once "../../config/database.php";

$title = "Materi Buku Saku";
$base_url = "../../";

$query = mysqli_query($conn,"
SELECT *
FROM kategori_buku_saku
ORDER BY nama_kategori ASC
");

include "../../includes/header.php";
?>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">
📖 Materi Buku Saku Satpam
</h3>

<p class="text-muted">
Pilih kategori materi yang ingin dipelajari.
</p>

</div>

</div>

<div class="row">

<?php if(mysqli_num_rows($query)>0): ?>

<?php while($kategori=mysqli_fetch_assoc($query)): ?>

<?php

$total=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) AS jumlah
FROM materi_buku_saku
WHERE id_kategori='".$kategori['id_kategori']."'
"));

?>

<div class="col-lg-4 col-md-6 mb-4">

<div class="card border-0 shadow h-100">

<div class="card-body text-center">

<div class="mb-3">

<i class="bi bi-journal-bookmark-fill text-primary"
style="font-size:60px;"></i>

</div>

<h5 class="fw-bold">

<?= htmlspecialchars($kategori['nama_kategori']); ?>

</h5>

<p class="text-muted">

<?= $total['jumlah']; ?> Materi

</p>

<a
href="list.php?id=<?= $kategori['id_kategori']; ?>"
class="btn btn-primary">

<i class="bi bi-arrow-right-circle"></i>

Lihat Materi

</a>

</div>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="col-12">

<div class="card shadow">

<div class="card-body text-center py-5">

<i
class="bi bi-folder-x"
style="font-size:70px;color:#999;">
</i>

<h4 class="mt-3">

Belum Ada Kategori

</h4>

<p class="text-muted">

Admin belum menambahkan kategori materi.

</p>

</div>

</div>

</div>

<?php endif; ?>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>