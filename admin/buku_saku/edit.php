<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Edit Buku Saku";
$base_url = "../../";
$activeMenu = "buku_saku";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = mysqli_query($conn,"
SELECT *
FROM buku_saku
WHERE id_buku='$id'
");

if(mysqli_num_rows($query)==0){

    echo "<script>
    alert('Data tidak ditemukan');
    window.location='index.php';
    </script>";

    exit;

}

$data = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){

    $judul = trim($_POST['judul']);

    $nama_file = $data['nama_file'];
    $path_file = $data['path_file'];
    $ukuran = $data['ukuran_file'];

    if($_FILES['file']['error'] != 4){

        $ext = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));

        if($ext != "pdf"){

            echo "<script>
            Swal.fire('Gagal','File harus PDF','error');
            </script>";

        }elseif($_FILES['file']['size'] > 10*1024*1024){

            echo "<script>
            Swal.fire('Gagal','Ukuran maksimal 10 MB','error');
            </script>";

        }else{

            $folder="../../uploads/buku_saku/";

            $namaBaru=time()."_".rand(1000,9999).".pdf";

            if(move_uploaded_file($_FILES['file']['tmp_name'],$folder.$namaBaru)){

                if(file_exists("../../".$path_file)){
                    unlink("../../".$path_file);
                }

                $nama_file=$namaBaru;
                $path_file="uploads/buku_saku/".$namaBaru;
                $ukuran=$_FILES['file']['size'];

            }else{

                echo "<script>
                Swal.fire('Gagal','Upload gagal','error');
                </script>";

            }

        }

    }

    $update=mysqli_query($conn,"
    UPDATE buku_saku SET

    judul='$judul',

    nama_file='$nama_file',

    path_file='$path_file',

    ukuran_file='$ukuran'

    WHERE id_buku='$id'
    ");

    if($update){

        echo "
        <script>

        Swal.fire({
            icon:'success',
            title:'Berhasil',
            text:'Data berhasil diperbarui'
        }).then(()=>{
            window.location='index.php';
        });

        </script>";

    }

}

include "../../includes/header.php";

?>

<?php include "../../includes/navbar.php"; ?>
<?php include "../../includes/admin_sidebar.php"; ?>

<div class="main-content">

<div class="container-fluid">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card shadow-sm border-0">

<div class="card-header">

<h4>Edit Buku Saku</h4>

</div>

<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">

<label class="form-label">

Judul Buku

</label>

<input
type="text"
name="judul"
class="form-control"
required
value="<?= htmlspecialchars($data['judul']); ?>">

</div>

<div class="mb-3">

<label class="form-label">

File Saat Ini

</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($data['nama_file']); ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Ganti File PDF (Opsional)

</label>

<input
type="file"
name="file"
class="form-control"
accept=".pdf">

<small class="text-muted">

Kosongkan jika tidak ingin mengganti file.

</small>

</div>

<div class="d-flex gap-2">

<button
type="submit"
name="update"
class="btn btn-primary">

<i class="bi bi-save me-2"></i>

Simpan Perubahan

</button>

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>
