<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Tambah Buku Saku";
$base_url = "../../";
$activeMenu = "buku_saku";

$error = '';

if (isset($_POST['simpan'])) {
  $judul = trim($_POST['judul'] ?? '');

  if ($judul === '') {
    $error = 'Judul buku tidak boleh kosong.';
  } else {
    $upload = uploadBukuSakuPdf($_FILES['file'] ?? []);
    if (!$upload['ok']) {
      $error = $upload['message'];
    } else {
      $uploadedBy = (int) $_SESSION['id_user'];
      $insert = mysqli_prepare($conn, 'INSERT INTO buku_saku (judul, nama_file, path_file, ukuran_file, uploaded_by) VALUES (?, ?, ?, ?, ?)');
      mysqli_stmt_bind_param($insert, 'sssii', $judul, $upload['nama_file'], $upload['path_file'], $upload['ukuran_file'], $uploadedBy);

      if (mysqli_stmt_execute($insert)) {
        header('Location: index.php?success=tambah');
        exit;
      }

      @unlink(dirname(__DIR__, 2) . '/' . $upload['path_file']);
      $error = 'Data buku saku gagal disimpan.';
    }
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

            <h4 class="mb-0">

              Tambah Buku Saku

            </h4>

          </div>

          <div class="card-body">

            <form method="POST" enctype="multipart/form-data">
              <?= csrf_input() ?>

              <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>

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

<?php include "../../includes/footer.php"; ?>
