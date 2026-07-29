<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Detail Nomor Penting";
$base_url = "../../";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

$query = mysqli_query($conn, "
    SELECT *
    FROM nomor_penting
    WHERE id_nomor='$id'
");

if (mysqli_num_rows($query) == 0) {
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc($query);

include "../../includes/header.php";

?>

<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/dashboard.css">

<?php include "../../includes/navbar.php"; ?>
<?php include "../../includes/admin_sidebar.php"; ?>

<div class="main-content">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">
Detail Nomor Penting
</h3>

<p class="text-muted mb-0">
Informasi lengkap nomor penting.
</p>

</div>

<a href="index.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Kembali

</a>

</div>

<div class="card shadow-sm border-0">

<div class="card-body">

<div class="row justify-content-center">

<div class="col-lg-8">

<table class="table table-borderless">

<tr>

<th width="220">
Instansi
</th>

<td>

: <?= htmlspecialchars($data['instansi']); ?>

</td>

</tr>

<tr>

<th>
Nomor Telepon
</th>

<td>

: <span class="fw-bold text-primary">

<?= htmlspecialchars($data['nomor_telepon']); ?>

</span>

</td>

</tr>

<tr>

<th>
Keterangan
</th>

<td>

: <?= htmlspecialchars($data['keterangan']); ?>

</td>

</tr>

<tr>

<th>
Urutan
</th>

<td>

: <?= $data['urutan']; ?>

</td>

</tr>

<tr>

<th>
Dibuat
</th>

<td>

: <?= formatTanggal(date('Y-m-d', strtotime($data['created_at']))); ?>

</td>

</tr>

<?php if(!empty($data['updated_at'])): ?>

<tr>

<th>
Terakhir Diubah
</th>

<td>

: <?= formatTanggal(date('Y-m-d', strtotime($data['updated_at']))); ?>

</td>

</tr>

<?php endif; ?>

</table>

<hr>

<a
href="edit.php?id=<?= $data['id_nomor']; ?>"
class="btn btn-warning">

<i class="bi bi-pencil-square me-2"></i>

Edit

</a>

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

</div>

</div>

</div>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>