<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = (int)$_GET['id'];

$query = mysqli_query($conn, "
SELECT *
FROM kategori_buku_saku
WHERE id_kategori='$id'
");

if (mysqli_num_rows($query) == 0) {
  echo "
    <script>
        alert('Data tidak ditemukan');
        window.location='index.php';
    </script>";
  exit;
}

$data = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {

  $nama = mysqli_real_escape_string(
    $conn,
    trim($_POST['nama_kategori'])
  );

  if ($nama == "") {

    echo "
        <script>
            alert('Nama kategori wajib diisi');
        </script>";
  } else {

    $cek = mysqli_query($conn, "
        SELECT *
        FROM kategori_buku_saku
        WHERE nama_kategori='$nama'
        AND id_kategori != '$id'
        ");

    if (mysqli_num_rows($cek) > 0) {

      echo "
            <script>

                alert('Nama kategori sudah digunakan');

            </script>";
    } else {

      $update = mysqli_query($conn, "
            UPDATE kategori_buku_saku
            SET
                nama_kategori='$nama'
            WHERE
                id_kategori='$id'
            ");

      if ($update) {

        echo "
                <script>

                    alert('Kategori berhasil diperbarui');

                    window.location='index.php';

                </script>";

        exit;
      } else {

        echo "
                <script>

                    alert('Gagal memperbarui kategori');

                </script>";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<?php include '../../includes/header.php'; ?>

<body>

  <?php include '../../includes/navbar.php'; ?>

  <div class="container-fluid">

    <div class="row">

      <?php include '../../includes/admin_sidebar.php'; ?>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">

          <h3>

            <i class="bi bi-pencil-square"></i>

            Edit Kategori Buku Saku

          </h3>

        </div>

        <div class="card shadow">

          <div class="card-body">

            <form method="POST">
              <?= csrf_input() ?>

              <div class="mb-3">

                <label class="form-label">

                  Nama Kategori

                </label>

                <input
                  type="text"
                  class="form-control"
                  name="nama_kategori"
                  value="<?= htmlspecialchars($data['nama_kategori']); ?>"
                  required>

              </div>

              <div class="mt-4">

                <button
                  type="submit"
                  name="update"
                  class="btn btn-success">

                  <i class="bi bi-check-circle"></i>

                  Update

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

      </main>

    </div>

  </div>

  <?php include '../../includes/footer.php'; ?>

</body>

</html>
