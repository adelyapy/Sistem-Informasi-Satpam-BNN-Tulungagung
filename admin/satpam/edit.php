<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Edit Satpam";
$base_url = "../../";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

$query = mysqli_query($conn,"
    SELECT *
    FROM users
    WHERE id_user='$id'
    AND role='satpam'
");

if(mysqli_num_rows($query)==0){
    header("Location:index.php");
    exit;
}

$satpam = mysqli_fetch_assoc($query);

if(isset($_POST['simpan'])){

    $nama = e($_POST['nama']);

    $foto = $satpam['foto'];
    $ttd  = $satpam['ttd'];

    if(!empty($_FILES['foto']['name'])){

        $upload = uploadFoto($_FILES['foto']);

        if($upload){

            if(
                !empty($foto) &&
                file_exists("../../uploads/foto/".$foto)
            ){
                unlink("../../uploads/foto/".$foto);
            }

            $foto = $upload;

        }

    }

    if(!empty($_FILES['ttd']['name'])){

        $upload = uploadTTD($_FILES['ttd']);

        if($upload){

            if(
                !empty($ttd) &&
                file_exists("../../uploads/ttd/".$ttd)
            ){
                unlink("../../uploads/ttd/".$ttd);
            }

            $ttd = $upload;

        }

    }

    $update=mysqli_query($conn,"
        UPDATE users
        SET
            nama='$nama',
            foto='$foto',
            ttd='$ttd',
            updated_at=NOW()
        WHERE id_user='$id'
    ");

    if($update){

        echo "

        <script>

        Swal.fire({

            icon:'success',

            title:'Berhasil',

            text:'Data Satpam berhasil diperbarui'

        }).then(()=>{

            window.location='index.php';

        });

        </script>

        ";

    }else{

        echo "

        <script>

        Swal.fire({

            icon:'error',

            title:'Gagal',

            text:'Data gagal diperbarui'

        });

        </script>

        ";

    }

}

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

Edit Satpam

</h3>

<p class="text-muted mb-0">

Perbarui data satpam.

</p>

</div>

<a
href="index.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Kembali

</a>

</div>

<div class="card shadow-sm border-0">

<div class="card-body">

<form
method="POST"
enctype="multipart/form-data">

<div class="row">

<div class="col-lg-6 mb-3">

<label class="form-label">

Kode Satpam

</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($satpam['kode_satpam']); ?>"
readonly>

</div>

<div class="col-lg-6 mb-3">

<label class="form-label">

Nama Satpam

</label>

<input
type="text"
name="nama"
class="form-control"
value="<?= htmlspecialchars($satpam['nama']); ?>"
required>

</div>

<div class="col-lg-6 mb-3">

<label class="form-label">

Foto Baru

</label>

<input
type="file"
name="foto"
class="form-control"
accept=".jpg,.jpeg,.png">

<?php if(!empty($satpam['foto'])): ?>

<div class="mt-3">

<img
src="../../uploads/foto/<?= $satpam['foto']; ?>"
class="img-thumbnail"
style="width:170px;height:170px;object-fit:cover;">

</div>

<?php endif; ?>

</div>

<div class="col-lg-6 mb-3">

<label class="form-label">

Tanda Tangan Baru

</label>

<input
type="file"
name="ttd"
class="form-control"
accept=".jpg,.jpeg,.png">

<?php if(!empty($satpam['ttd'])): ?>

<div class="mt-3">

<img
src="../../uploads/ttd/<?= $satpam['ttd']; ?>"
class="img-thumbnail p-2"
style="height:120px;background:#fff;">

</div>

<?php endif; ?>

</div>

<div class="col-12">

<button
type="submit"
name="simpan"
class="btn btn-primary">

<i class="bi bi-check-circle me-2"></i>

Simpan Perubahan

</button>

<a
href="index.php"
class="btn btn-secondary">

Batal

</a>

</div>

</div>

</form>

</div>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>