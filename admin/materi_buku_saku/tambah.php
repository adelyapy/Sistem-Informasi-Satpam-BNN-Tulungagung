<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

if (isset($_POST['simpan'])) {

  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $kategori = (int)$_POST['kategori'];
  $isi = mysqli_real_escape_string($conn, $_POST['isi']);

  if ($judul == "" || $kategori == 0 || $isi == "") {

    echo "
        <script>

        alert('Semua data wajib diisi');

        history.back();

        </script>";

    exit;
  }

  $q = mysqli_query($conn, "
    SELECT MAX(id_materi) id
    FROM materi_buku_saku
    ");

  $d = mysqli_fetch_assoc($q);

  $id = $d['id'] + 1;

  $insert = mysqli_query($conn, "
    INSERT INTO materi_buku_saku
    (
        id_materi,
        id_kategori,
        judul,
        isi
    )
    VALUES
    (
        '$id',
        '$kategori',
        '$judul',
        '$isi'
    )
    ");

  if ($insert) {

    echo "

        <script>

        alert('Materi berhasil ditambahkan');

        location='index.php';

        </script>";
  } else {

    echo "

        <script>

        alert('Gagal menambahkan materi');

        history.back();

        </script>";
  }
}

$kategori = mysqli_query($conn, "
SELECT *
FROM kategori_buku_saku
ORDER BY nama_kategori ASC
");
?>

<!DOCTYPE html>
<html lang="id">

<?php include "../../includes/header.php"; ?>

<body>

  <?php include "../../includes/navbar.php"; ?>

  <div class="container-fluid">

    <div class="row">

      <?php include "../../includes/admin_sidebar.php"; ?>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

        <div class="d-flex justify-content-between pt-3 pb-3">

          <h3>

            <i class="bi bi-plus-circle-fill"></i>

            Tambah Materi Buku Saku

          </h3>

        </div>

        <div class="card shadow">

          <div class="card-body">

            <form method="POST">
              <?= csrf_input() ?>

              <div class="mb-3">

                <label>

                  Judul

                </label>

                <input
                  type="text"
                  name="judul"
                  class="form-control"
                  required>

              </div>

              <div class="mb-3">

                <label>

                  Kategori

                </label>

                <select
                  name="kategori"
                  class="form-select"
                  required>

                  <option value="">

                    Pilih Kategori

                  </option>

                  <?php while ($k = mysqli_fetch_assoc($kategori)): ?>

                    <option value="<?= $k['id_kategori']; ?>">

                      <?= $k['nama_kategori']; ?>

                    </option>

                  <?php endwhile; ?>

                </select>

              </div>

              <div class="mb-3">

                <label>

                  Isi Materi

                </label>

                <textarea
                  id="editor"
                  name="isi"></textarea>

              </div>

              <div class="mt-4">

                <button
                  class="btn btn-primary"
                  name="simpan">

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

  <?php include "../../includes/footer.php"; ?>

  <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

  <script>
    ClassicEditor
      .create(document.querySelector('#editor'))
      .catch(error => {
        console.error(error);
      });
  </script>

</body>

</html>
