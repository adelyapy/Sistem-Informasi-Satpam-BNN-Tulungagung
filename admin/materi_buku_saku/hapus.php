<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";

if (!isset($_GET['id'])) {
  header("Location:index.php");
  exit;
}

$id = (int) $_GET['id'];

// Ambil data materi
$query = mysqli_query($conn, "
    SELECT *
    FROM materi_buku_saku
    WHERE id_materi = '$id'
");

if (mysqli_num_rows($query) == 0) {
  echo "
    <script>
        alert('Data tidak ditemukan');
        location='index.php';
    </script>";
  exit;
}

$data = mysqli_fetch_assoc($query);

// Hapus icon jika ada
if (!empty($data['icon'])) {

  $file = "../../uploads/icon_buku_saku/" . $data['icon'];

  if (file_exists($file)) {
    unlink($file);
  }
}

// Hapus data dari database
$hapus = mysqli_query($conn, "
    DELETE FROM materi_buku_saku
    WHERE id_materi = '$id'
");

if ($hapus) {

  echo "
    <script>
        alert('Materi berhasil dihapus');
        location='index.php';
    </script>";
} else {

  echo "
    <script>
        alert('Materi gagal dihapus');
        location='index.php';
    </script>";
}
