<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

if (isset($_POST['simpan'])) {

  $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));

  if ($nama == "") {

    echo "
        <script>
            alert('Nama kategori tidak boleh kosong!');
            window.history.back();
        </script>";
    exit;
  }

  // Cek duplikat
  $cek = mysqli_query($conn, "
        SELECT *
        FROM kategori_buku_saku
        WHERE nama_kategori='$nama'
    ");

  if (mysqli_num_rows($cek) > 0) {

    echo "
        <script>
            alert('Kategori sudah ada!');
            window.history.back();
        </script>";
    exit;
  }

  // Ambil ID berikutnya
  $idBaru = 1;

  $q = mysqli_query($conn, "
        SELECT MAX(id_kategori) AS id
        FROM kategori_buku_saku
    ");

  if ($d = mysqli_fetch_assoc($q)) {

    $idBaru = $d['id'] + 1;
  }

  $insert = mysqli_query($conn, "
        INSERT INTO kategori_buku_saku
        (
            id_kategori,
            nama_kategori
        )
        VALUES
        (
            '$idBaru',
            '$nama'
        )
    ");

  if ($insert) {

    echo "
        <script>

            alert('Kategori berhasil ditambahkan');

            window.location='index.php';

        </script>";
  } else {

    echo "
        <script>

            alert('Gagal menambahkan kategori');

            window.history.back();

        </script>";
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

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

          <h3>

            <i class="bi bi-folder-plus"></i>

            Tambah Kategori Buku Saku

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
                  name="nama_kategori"
                  class="form-control"
                  placeholder="Contoh : SOP Satpam"
                  required>

              </div>

              <div class="mt-4">

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

      </main>

    </div>

  </div>

  <?php include '../../includes/footer.php'; ?>

</body>

</html>
