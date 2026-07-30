<?php

require_once "../../config/database.php";

requireRole('satpam');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = mysqli_query($conn,"
SELECT
    buku_saku.*,
    users.nama
FROM buku_saku
LEFT JOIN users
ON users.id_user = buku_saku.uploaded_by
WHERE buku_saku.id_buku='$id'
LIMIT 1
");

if(mysqli_num_rows($query)==0){
    header("Location: index.php");
    exit;
}

$data = mysqli_fetch_assoc($query);

$title = "Baca Buku Saku";
$base_url = "../../";

include "../../includes/header.php";
?>

<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/dashboard.css">

<?php include "../../includes/navbar.php"; ?>
<?php include "../../includes/satpam_sidebar.php"; ?>

<div class="main-content">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">
<?= htmlspecialchars($data['judul']); ?>
</h3>

<p class="text-muted mb-0">
Buku Saku Satpam
</p>

</div>

<div>

<a href="index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i>
    Kembali
</a>

<a
href="../../<?= htmlspecialchars($data['path_file']); ?>"
target="_blank"
download
class="btn btn-success">

<i class="bi bi-download"></i>

Download

</a>

</div>

</div>


<div class="row">

<div class="col-lg-3">

<div class="card shadow-sm border-0">

<div class="card-body">

<div class="text-center mb-3">

<i class="bi bi-file-earmark-pdf-fill text-danger"
style="font-size:80px;"></i>

</div>

<table class="table table-borderless">

<tr>

<th>Judul</th>

</tr>

<tr>

<td><?= htmlspecialchars($data['judul']); ?></td>

</tr>

<tr>

<th>Uploader</th>

</tr>

<tr>

<td><?= htmlspecialchars($data['nama']); ?></td>

</tr>

<tr>

<th>Ukuran</th>

</tr>

<tr>

<td>

<?= number_format($data['ukuran_file']/1024,2); ?>

KB

</td>

</tr>

<tr>

<th>Tanggal Upload</th>

</tr>

<tr>

<td>

<?= date('d F Y H:i',strtotime($data['created_at'])); ?>

</td>

</tr>

</table>

</div>

</div>

</div>


<div class="col-lg-9">

<div class="card shadow-sm border-0">

<div class="card-header">

<strong>Preview Buku Saku</strong>

</div>

<div class="card-body p-0">

<iframe

src="../../<?= htmlspecialchars($data['path_file']); ?>"

width="100%"

height="850"

style="border:none;">

</iframe>

</div>

</div>

</div>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>