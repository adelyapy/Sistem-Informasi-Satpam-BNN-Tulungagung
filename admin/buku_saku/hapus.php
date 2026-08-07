<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
  header("Location: index.php");
  exit;
}

// Ambil data buku
$query = mysqli_query($conn, "
    SELECT *
    FROM buku_saku
    WHERE id_buku='$id'
");

if (mysqli_num_rows($query) == 0) {
  header("Location: index.php");
  exit;
}

$data = mysqli_fetch_assoc($query);

// Hapus file PDF jika ada
if (!empty($data['path_file'])) {

  $file = "../../" . $data['path_file'];

  if (file_exists($file)) {
    unlink($file);
  }
}

// Hapus data database
$hapus = mysqli_query($conn, "
    DELETE FROM buku_saku
    WHERE id_buku='$id'
");

if ($hapus) {

?>

  <script src="../../assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>

  <script>
    Swal.fire({

      icon: 'success',

      title: 'Berhasil',

      text: 'Buku saku berhasil dihapus'

    }).then(() => {

      window.location = 'index.php';

    });
  </script>

<?php

} else {

?>

  <script src="../../assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>

  <script>
    Swal.fire({

      icon: 'error',

      title: 'Gagal',

      text: 'Data gagal dihapus'

    }).then(() => {

      window.location = 'index.php';

    });
  </script>

<?php

}
