<?php
require_once "../../config/database.php";

requireRole('satpam');

$title = "Cari Materi Buku Saku";
$base_url = "../../";

$keyword = isset($_GET['keyword'])
    ? mysqli_real_escape_string($conn,$_GET['keyword'])
    : '';

$query = mysqli_query($conn,"
SELECT
    materi_buku_saku.*,
    kategori_buku_saku.nama_kategori
FROM materi_buku_saku
LEFT JOIN kategori_buku_saku
ON kategori_buku_saku.id_kategori=materi_buku_saku.id_kategori
WHERE judul LIKE '%$keyword%'
ORDER BY judul ASC
");

include "../../includes/header.php";
?>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">

🔍 Pencarian Materi Buku Saku

</h3>

<p class="text-muted">

Cari materi berdasarkan judul.

</p>

</div>

<a href="materi.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Kembali

</a>

</div>

<form method="GET" class="mb-4">

<div class="row">

<div class="col-md-8">

<input
type="text"
name="keyword"
class="form-control"
placeholder="Masukkan judul materi..."
value="<?= htmlspecialchars($keyword); ?>"
required>

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">

<i class="bi bi-search"></i>

Cari

</button>

</div>

<div class="col-md-2">

<a
href="cari.php"
class="btn btn-outline-secondary w-100">

Reset

</a>

</div>

</div>

</form>

<div class="row">

<?php if(mysqli_num_rows($query)>0): ?>

<?php while($data=mysqli_fetch_assoc($query)): ?>

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

<p class="text-center text-muted">

<?= htmlspecialchars($data['nama_kategori']); ?>

</p>

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
class="bi bi-search"
style="font-size:70px;color:#999;">
</i>

<h4 class="mt-3">

Materi Tidak Ditemukan

</h4>

<p class="text-muted">

Tidak ada materi yang sesuai dengan kata kunci
<strong><?= htmlspecialchars($keyword); ?></strong>

</p>

</div>

</div>

</div>

<?php endif; ?>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>