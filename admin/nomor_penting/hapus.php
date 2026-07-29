<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

$cek = mysqli_query($conn, "
    SELECT *
    FROM nomor_penting
    WHERE id_nomor='$id'
");

if (mysqli_num_rows($cek) == 0) {
    header("Location: index.php");
    exit;
}

$hapus = mysqli_query($conn, "
    DELETE FROM nomor_penting
    WHERE id_nomor='$id'
");

if ($hapus) {

    echo "

    <script>

    Swal.fire({

        icon:'success',

        title:'Berhasil',

        text:'Nomor penting berhasil dihapus'

    }).then(()=>{

        window.location='index.php';

    });

    </script>

    ";

} else {

    echo "

    <script>

    Swal.fire({

        icon:'error',

        title:'Gagal',

        text:'Data gagal dihapus'

    }).then(()=>{

        window.location='index.php';

    });

    </script>

    ";

}