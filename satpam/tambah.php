<?php

require_once "../config/admin_auth.php";
require_once "../config/function.php";

$title = "Tambah Satpam";
$base_url = "../";
$activeMenu = "data_satpam";

if(isset($_POST['simpan'])){

    $kode_satpam = generateKodeSatpam($conn);
    $nama = e($_POST['nama']);

    $foto = uploadFoto($_FILES['foto']);
    $ttd  = uploadTTD($_FILES['ttd']);

    $query = mysqli_query($conn,"
        INSERT INTO users
        (
            kode_satpam,
            nama,
            username,
            password,
            foto,
            ttd,
            role
        )
        VALUES
        (
            '$kode_satpam',
            '$nama',
            NULL,
            NULL,
            '$foto',
            '$ttd',
            'satpam'
        )
    ");

    if($query){

        echo "
        <script>

        Swal.fire({

            icon:'success',
            title:'Berhasil',

            text:'Data Satpam berhasil ditambahkan'

        }).then(()=>{

            window.location='index.php';

        });

        </script>";

    }else{

        echo "
        <script>

        Swal.fire({

            icon:'error',
            title:'Gagal',

            text:'Data gagal disimpan'

        });

        </script>";

    }

}

include "../includes/header.php";

?>

<?php include "../includes/navbar.php"; ?>
<?php include "../includes/admin_sidebar.php"; ?>

<div class="main-content">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="fw-bold">
Tambah Satpam
</h3>

<p class="text-muted mb-0">
Tambahkan anggota satpam baru.
</p>

</div>

<a href="index.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Kembali

</a>

</div>

<div class="card shadow-sm border-0">

<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<div class="row">

<div class="col-lg-6 mb-3">

<label class="form-label">

Kode Satpam

</label>

<input
type="text"
class="form-control"
value="<?= generateKodeSatpam($conn); ?>"
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
required>

</div>

<div class="col-lg-6 mb-3">

<label class="form-label">

Foto

</label>

<input
type="file"
name="foto"
class="form-control"
accept=".jpg,.jpeg,.png">

<div class="form-text">

Format: JPG / JPEG / PNG

</div>

</div>

<div class="col-lg-6 mb-3">

<label class="form-label">

Tanda Tangan

</label>

<input
type="file"
name="ttd"
class="form-control"
accept=".jpg,.jpeg,.png">

<div class="form-text">

Format: JPG / JPEG / PNG

</div>

</div>

<div class="col-12">

<button
type="submit"
name="simpan"
class="btn btn-primary">

<i class="bi bi-check-circle me-2"></i>

Simpan

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

<?php include "../includes/footer.php"; ?>
