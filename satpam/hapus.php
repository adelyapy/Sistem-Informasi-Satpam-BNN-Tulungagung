<?php

require_once "../config/admin_auth.php";
require_once "../config/function.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$data = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id_user='$id'
    AND role='satpam'
");

if (mysqli_num_rows($data) == 0) {
    header("Location: index.php");
    exit;
}

$satpam = mysqli_fetch_assoc($data);

/*
|--------------------------------------------------------------------------
| Hapus Foto
|--------------------------------------------------------------------------
*/

if (!empty($satpam['foto'])) {

    $path = "../../uploads/foto/" . $satpam['foto'];

    if (file_exists($path)) {
        unlink($path);
    }

}

/*
|--------------------------------------------------------------------------
| Hapus TTD
|--------------------------------------------------------------------------
*/

if (!empty($satpam['ttd'])) {

    $path = "../../uploads/ttd/" . $satpam['ttd'];

    if (file_exists($path)) {
        unlink($path);
    }

}

/*
|--------------------------------------------------------------------------
| Hapus Database
|--------------------------------------------------------------------------
*/

$hapus = mysqli_query($conn, "
    DELETE FROM users
    WHERE id_user='$id'
");

if ($hapus) {

    echo "

    <script>

    Swal.fire({

        icon:'success',
        title:'Berhasil',

        text:'Data Satpam berhasil dihapus'

    }).then(function(){

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

    }).then(function(){

        window.location='index.php';

    });

    </script>

    ";

}
