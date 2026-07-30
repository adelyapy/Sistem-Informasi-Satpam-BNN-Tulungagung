<?php
require_once "../../config/database.php";

$title = "Detail Materi Buku Saku";
$base_url = "../../";

if (!isset($_GET['id'])) {
    header("Location:materi.php");
    exit;
}

$id = (int)$_GET['id'];

$query = mysqli_query($conn, "
SELECT
    materi_buku_saku.*,
    kategori_buku_saku.nama_kategori
FROM materi_buku_saku
LEFT JOIN kategori_buku_saku
ON kategori_buku_saku.id_kategori = materi_buku_saku.id_kategori
WHERE id_materi='$id'
");

if (mysqli_num_rows($query) == 0) {

    echo "
    <script>
        alert('Materi tidak ditemukan');
        location='materi.php';
    </script>";

    exit;
}

$data = mysqli_fetch_assoc($query);

include "../../includes/header.php";
?>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">

<?= htmlspecialchars($data['judul']); ?>

</h3>

<p class="text-muted">

Kategori :
<strong><?= htmlspecialchars($data['nama_kategori']); ?></strong>

</p>

</div>

<a href="javascript:history.back()" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Kembali

</a>

</div>

<div class="card shadow border-0">

<div class="card-body">

<?php if (!empty($data['icon'])) : ?>

<div class="text-center mb-4">

<img
src="../../uploads/icon_buku_saku/<?= htmlspecialchars($data['icon']); ?>"
class="img-fluid"
style="max-height:120px;">

</div>

<?php endif; ?>

<div class="materi-content">

<?= $data['isi']; ?>

</div>

</div>

</div>

</div>

</div>

<style>

.materi-content{

font-size:16px;
line-height:1.9;
text-align:justify;

}

.materi-content img{

max-width:100%;
height:auto;

}

.materi-content table{

width:100%;
margin-top:15px;
margin-bottom:15px;

}

.materi-content p{

margin-bottom:15px;

}

.materi-content h1,
.materi-content h2,
.materi-content h3,
.materi-content h4{

margin-top:20px;
margin-bottom:15px;
font-weight:bold;

}

</style>

<?php include "../../includes/footer.php"; ?>