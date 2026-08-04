<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Detail Buku Saku";
$base_url = "../../";
$activeMenu = "buku_saku";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = mysqli_query($conn,"
SELECT
    buku_saku.*,
    users.nama
FROM buku_saku
LEFT JOIN users
ON users.id_user = buku_saku.uploaded_by
WHERE id_buku='$id'
");

if(mysqli_num_rows($query)==0){

    echo "
    <script>
    alert('Data tidak ditemukan');
    window.location='index.php';
    </script>
    ";

    exit;

}

$data = mysqli_fetch_assoc($query);

include "../../includes/header.php";

?>

<?php include "../../includes/navbar.php"; ?>
<?php include "../../includes/admin_sidebar.php"; ?>

<div class="main-content">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold mb-1">

Detail Buku Saku

</h3>

<p class="text-muted">

Informasi lengkap buku saku.

</p>

</div>

<a
href="index.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Kembali

</a>

</div>

<div class="row">

<div class="col-lg-4">

<div class="card shadow-sm border-0">

<div class="card-body">

<table class="table">

<tr>

<th width="130">

Judul

</th>

<td>

<?= htmlspecialchars($data['judul']); ?>

</td>

</tr>

<tr>

<th>

Nama File

</th>

<td>

<?= htmlspecialchars($data['nama_file']); ?>

</td>

</tr>

<tr>

<th>

Ukuran

</th>

<td>

<?= round($data['ukuran_file']/1024,2); ?>

KB

</td>

</tr>

<tr>

<th>

Uploader

</th>

<td>

<?= htmlspecialchars($data['nama']); ?>

</td>

</tr>

<tr>

<th>

Tanggal Upload

</th>

<td>

<?= date('d-m-Y H:i',strtotime($data['created_at'])); ?>

</td>

</tr>

</table>

<hr>

<a
href="../../<?= $data['path_file']; ?>"
target="_blank"
class="btn btn-success w-100">

<i class="bi bi-download me-2"></i>

Download PDF

</a>

</div>

</div>

</div>

<div class="col-lg-8">

<div class="card shadow-sm border-0">

<div class="card-header">

Preview PDF

</div>

<div class="card-body p-0">

<iframe

src="../../<?= $data['path_file']; ?>"

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
