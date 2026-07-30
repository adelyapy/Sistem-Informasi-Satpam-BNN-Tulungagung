<?php

require_once "../../config/database.php";


$title = "Buku Saku";
$base_url = "../../";

$keyword = isset($_GET['keyword'])
    ? mysqli_real_escape_string($conn,$_GET['keyword'])
    : '';

$sql = "
SELECT
    buku_saku.*,
    users.nama
FROM buku_saku
LEFT JOIN users
ON users.id_user = buku_saku.uploaded_by
";

if($keyword!=""){
    $sql .= " WHERE judul LIKE '%$keyword%'";
}

$sql .= " ORDER BY created_at DESC";

$query = mysqli_query($conn,$sql);

include "../../includes/header.php";
?>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">

📚 Buku Saku Satpam

</h3>

<p class="text-muted">

Panduan, SOP, dan dokumen yang dapat dipelajari oleh anggota satpam.

</p>

</div>

</div>


<form method="GET" class="mb-4">

<div class="row">

<div class="col-md-5">

<input
type="text"
class="form-control"
name="keyword"
placeholder="Cari Buku..."
value="<?= htmlspecialchars($keyword); ?>">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">

<i class="bi bi-search"></i>

Cari

</button>

</div>

</div>

</form>


<div class="row">

<?php

if(mysqli_num_rows($query)>0):

while($data=mysqli_fetch_assoc($query)):

?>

<div class="col-md-4 mb-4">

<div class="card shadow-sm border-0 h-100">

<div class="card-body text-center">

<div class="mb-3">

<i class="bi bi-file-earmark-pdf-fill text-danger"
style="font-size:70px;"></i>

</div>

<h5 class="fw-bold">

<?= htmlspecialchars($data['judul']); ?>

</h5>

<p class="text-muted mb-1">

PDF Document

</p>

<small class="text-muted">

Ukuran :

<?= round($data['ukuran_file']/1024,2); ?>

KB

</small>

<hr>

<p class="mb-3">

<i class="bi bi-calendar3"></i>

<?= date('d M Y',strtotime($data['created_at'])); ?>

</p>

<div class="d-grid gap-2">

<a
href="baca.php?id=<?= $data['id_buku']; ?>"
class="btn btn-primary">

<i class="bi bi-eye"></i>

Baca Buku

</a>

<a
href="../../<?= $data['path_file']; ?>"
target="_blank"
download
class="btn btn-success">

<i class="bi bi-download"></i>

Download

</a>

</div>

</div>

</div>

</div>

<?php

endwhile;

else:

?>

<div class="col-12">

<div class="card shadow-sm">

<div class="card-body text-center py-5">

<img
src="../../assets/img/empty-data.png"
width="140">

<h5 class="mt-3">

Belum Ada Buku Saku

</h5>

<p class="text-muted">

Admin belum mengunggah Buku Saku.

</p>

</div>

</div>

</div>

<?php endif; ?>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>