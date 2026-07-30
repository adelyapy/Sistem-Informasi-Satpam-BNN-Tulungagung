<?php

require_once "../config/admin_auth.php";
require_once "../config/function.php";

$title = "Tambah Buku Saku";
$base_url = "../../";

if(isset($_POST['simpan'])){

    $judul = trim($_POST['judul']);

    if(empty($judul)){
        echo "<script>
        Swal.fire('Gagal','Judul buku tidak boleh kosong','error');
        </script>";
    }else{

        if($_FILES['file']['error']==4){

            echo "<script>
            Swal.fire('Gagal','Silakan pilih file PDF','error');
            </script>";

        }else{

            $namaFile = $_FILES['file']['name'];
            $tmpFile  = $_FILES['file']['tmp_name'];
            $ukuran   = $_FILES['file']['size'];

            $ext = strtolower(pathinfo($namaFile,PATHINFO_EXTENSION));

            if($ext!='pdf'){

                echo "<script>
                Swal.fire('Gagal','File harus PDF','error');
                </script>";

            }elseif($ukuran > 10*1024*1024){

                echo "<script>
                Swal.fire('Gagal','Ukuran maksimal 10 MB','error');
                </script>";

            }else{

                $namaBaru = time().'_'.rand(1000,9999).'.pdf';

                $folder = "../../uploads/buku_saku/";

                if(!is_dir($folder)){
                    mkdir($folder,0777,true);
                }

                if(move_uploaded_file($tmpFile,$folder.$namaBaru)){

                    $path = "uploads/buku_saku/".$namaBaru;

                    $uploaded_by = $_SESSION['id_user'];

                    $insert = mysqli_query($conn,"
                    INSERT INTO buku_saku
                    (
                        judul,
                        nama_file,
                        path_file,
                        ukuran_file,
                        uploaded_by
                    )
                    VALUES
                    (
                        '$judul',
                        '$namaBaru',
                        '$path',
                        '$ukuran',
                        '$uploaded_by'
                    )
                    ");

                    if($insert){

                        echo "
                        <script>

                        Swal.fire({
                            icon:'success',
                            title:'Berhasil',
                            text:'Buku saku berhasil ditambahkan'
                        }).then(()=>{
                            window.location='index.php';
                        });

                        </script>
                        ";

                    }else{

                        unlink($folder.$namaBaru);

                        echo "
                        <script>

                        Swal.fire(
                        'Gagal',
                        'Database gagal disimpan',
                        'error'
                        );

                        </script>
                        ";

                    }

                }else{

                    echo "
                    <script>

                    Swal.fire(
                    'Gagal',
                    'Upload file gagal',
                    'error'
                    );

                    </script>
                    ";

                }

            }

        }

    }

}

include "../includes/header.php";

?>

<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/dashboard.css">

<?php include "../includes/navbar.php"; ?>
<?php include "../includes/admin_sidebar.php"; ?>

<div class="main-content">

<div class="container-fluid">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card shadow-sm border-0">

<div class="card-header">

<h4 class="mb-0">

Tambah Buku Saku

</h4>

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
required>

</div>

<div class="mb-3">

<label class="form-label">

Upload File PDF

</label>

<input
type="file"
name="file"
class="form-control"
accept=".pdf"
required>

<small class="text-muted">

Format PDF maksimal 10 MB

</small>

</div>

<div class="d-flex gap-2">

<button
type="submit"
name="simpan"
class="btn btn-primary">

<i class="bi bi-save"></i>

Simpan

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

<?php include "../includes/footer.php"; ?>