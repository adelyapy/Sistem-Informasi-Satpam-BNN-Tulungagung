<?php
require_once "../../config/database.php";

$title = "Daftar Materi Buku Saku";
$base_url = "../../";

if (!isset($_GET['id'])) {
    header("Location:materi.php");
    exit;
}

$id = (int)$_GET['id'];

$qKategori = mysqli_query($conn,"
SELECT *
FROM kategori_buku_saku
WHERE id_kategori='$id'
");

if(mysqli_num_rows($qKategori)==0){
    echo "
    <script>
        alert('Kategori tidak ditemukan');
        location='materi.php';
    </script>";
    exit;
}

$kategori = mysqli_fetch_assoc($qKategori);

$qMateri = mysqli_query($conn,"
SELECT *
FROM materi_buku_saku
WHERE id_kategori='$id'
ORDER BY judul ASC
");

include "../../includes/header.php";
?>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">
📖 <?= htmlspecialchars($kategori['nama_kategori']); ?>
</h3>

<p class="text-muted">
Daftar materi pada kategori ini.
</p>

</div>

<a href="materi.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i>
Kembali
</a>

</div>

<div class="row">

<?php if(mysqli_num_rows($qMateri)>0): ?>

<?php while($data=mysqli_fetch_assoc($qMateri)): ?>

<div class="col-lg-4 col-md-6 mb-4">

<div class="card shadow border-0 h-100">

<div class="card-body">

<div class="text-center mb-3">

<?php if(!empty($data['icon'])): ?>

<img
src="../../uploads/icon_buku_saku/<?= htmlspecialchars($data['icon']); ?>"
class="img-fluid"
style="height:70px;object-fit:contain;">

<?php else: ?>

<i
class="bi bi-file-earmark-text-fill text-primary"
style="font-size:60px;">
</i>

<?php endif; ?>

</div>

<h5 class="fw-bold text-center">

<?= htmlspecialchars($data['judul']); ?>

</h5>

<hr>

<div class="d-grid">

<a
href="detail.php?id=<?= $data['id_materi']; ?>"
class="btn btn-primary">

<i class="bi bi-book"></i>

Baca Materi

</a>

</div>

</div>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="col-12">

<div class="card shadow">

<div class="card-body text-center py-5">

<i
class="bi bi-folder2-open"
style="font-size:70px;color:#888;">
</i>

<h4 class="mt-3">

Belum Ada Materi

</h4>

<p class="text-muted">

Belum terdapat materi pada kategori ini.

</p>

</div>

</div>

</div>

<?php endif; ?>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>