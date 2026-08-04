<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Buku Saku";
$base_url = "../../";
$activeMenu = "buku_saku";

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn,$_GET['keyword']) : '';

$sql = "
SELECT
    buku_saku.*,
    users.nama
FROM buku_saku
LEFT JOIN users
ON users.id_user = buku_saku.uploaded_by
";

if($keyword!=''){
    $sql .= " WHERE buku_saku.judul LIKE '%$keyword%'";
}

$sql .= " ORDER BY created_at DESC";

$query = mysqli_query($conn,$sql);

include "../../includes/header.php";
?>

<?php include "../../includes/navbar.php"; ?>
<?php include "../../includes/admin_sidebar.php"; ?>

<div class="main-content">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold mb-1">
Buku Saku Satpam
</h3>

<p class="text-muted mb-0">
Kelola seluruh dokumen buku saku satpam.
</p>

</div>

<a href="tambah.php" class="btn btn-primary">

<i class="bi bi-plus-circle me-2"></i>

Tambah Buku

</a>

</div>


<div class="card shadow-sm border-0">

<div class="card-body">

<form method="GET" class="mb-3">

<div class="row">

<div class="col-md-4">

<input
type="text"
name="keyword"
class="form-control"
placeholder="Cari Judul Buku..."
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

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>

<th width="60">
No
</th>

<th>
Judul Buku
</th>

<th width="180">
Nama File
</th>

<th width="120">
Ukuran
</th>

<th width="180">
Uploader
</th>

<th width="170">
Tanggal Upload
</th>

<th width="180" class="text-center">
Aksi
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query)>0):

$no=1;

while($data=mysqli_fetch_assoc($query)):

?>

<tr>

<td><?= $no++; ?></td>

<td>

<strong>

<?= htmlspecialchars($data['judul']); ?>

</strong>

</td>

<td>

<?= htmlspecialchars($data['nama_file']); ?>

</td>

<td>

<?= round($data['ukuran_file']/1024,2); ?> KB

</td>

<td>

<?= htmlspecialchars($data['nama']); ?>

</td>

<td>

<?= date('d-m-Y H:i',strtotime($data['created_at'])); ?>

</td>

<td class="text-center">

<a
href="detail.php?id=<?= $data['id_buku']; ?>"
class="btn btn-info btn-sm">

<i class="bi bi-eye"></i>

</a>

<a
href="edit.php?id=<?= $data['id_buku']; ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil-square"></i>

</a>

<button
class="btn btn-danger btn-sm"
onclick="hapusBuku(<?= $data['id_buku']; ?>)">

<i class="bi bi-trash"></i>

</button>

</td>

</tr>

<?php

endwhile;

else:

?>

<tr>

<td colspan="7" class="text-center py-5">

<img
src="../../assets/img/empty-data.png"
width="120"
class="mb-3">

<h6 class="fw-semibold">

Belum ada Buku Saku

</h6>

<p class="text-muted mb-0">

Silakan tambahkan Buku Saku terlebih dahulu.

</p>

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<script>

function hapusBuku(id){

Swal.fire({

title:'Hapus Buku?',

text:'Data buku saku akan dihapus.',

icon:'warning',

showCancelButton:true,

confirmButtonColor:'#dc3545',

cancelButtonColor:'#6c757d',

confirmButtonText:'Ya, Hapus',

cancelButtonText:'Batal'

}).then((result)=>{

if(result.isConfirmed){

window.location='hapus.php?id='+id;

}

});

}

</script>

<?php include "../../includes/footer.php"; ?>
