<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Cek apakah kategori ada
|--------------------------------------------------------------------------
*/

$cekKategori = mysqli_query($conn, "
SELECT *
FROM kategori_buku_saku
WHERE id_kategori='$id'
");

if (mysqli_num_rows($cekKategori) == 0) {

    echo "
    <script>

        alert('Kategori tidak ditemukan');

        window.location='index.php';

    </script>";

    exit;
}

/*
|--------------------------------------------------------------------------
| Cek apakah masih dipakai materi
|--------------------------------------------------------------------------
*/

$cekMateri = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM materi_buku_saku
WHERE id_kategori='$id'
");

$data = mysqli_fetch_assoc($cekMateri);

if ($data['total'] > 0) {

    echo "
    <script>

        alert('Kategori tidak dapat dihapus karena masih digunakan oleh materi buku saku.');

        window.location='index.php';

    </script>";

    exit;
}

/*
|--------------------------------------------------------------------------
| Hapus
|--------------------------------------------------------------------------
*/

$hapus = mysqli_query($conn, "
DELETE FROM kategori_buku_saku
WHERE id_kategori='$id'
");

if ($hapus) {

    echo "
    <script>

        alert('Kategori berhasil dihapus');

        window.location='index.php';

    </script>";

} else {

    echo "
    <script>

        alert('Gagal menghapus kategori');

        window.location='index.php';

    </script>";

}
?>