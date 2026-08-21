<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Edit Buku Saku";
$base_url = "../../";
$activeMenu = "buku_saku";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = mysqli_query($conn, "
SELECT *
FROM buku_saku
WHERE id_buku='$id'
");

if (mysqli_num_rows($query) == 0) {

  echo "<script>
    alert('Data tidak ditemukan');
    window.location='index.php';
    </script>";

  exit;
}

$data = mysqli_fetch_assoc($query);

$error = '';

if (isset($_POST['update'])) {
  $judul = trim($_POST['judul'] ?? '');
  $namaFile = $data['nama_file'];
  $pathFile = $data['path_file'];
  $ukuran = (int) $data['ukuran_file'];
  $fileBaru = null;

  if ($judul === '') {
    $error = 'Judul buku tidak boleh kosong.';
  } elseif (($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $upload = uploadBukuSakuPdf($_FILES['file']);
    if (!$upload['ok']) {
      $error = $upload['message'];
    } else {
      $fileBaru = $upload;
      $namaFile = $upload['nama_file'];
      $pathFile = $upload['path_file'];
      $ukuran = $upload['ukuran_file'];
    }
  }

  if ($error === '') {
    $update = mysqli_prepare($conn, 'UPDATE buku_saku SET judul = ?, nama_file = ?, path_file = ?, ukuran_file = ? WHERE id_buku = ?');
    mysqli_stmt_bind_param($update, 'sssii', $judul, $namaFile, $pathFile, $ukuran, $id);

    if (mysqli_stmt_execute($update)) {
      if ($fileBaru && !empty($data['path_file'])) {
        $fileLama = dirname(__DIR__, 2) . '/' . $data['path_file'];
        if (is_file($fileLama)) {
          @unlink($fileLama);
        }
      }
      header('Location: index.php?success=edit');
      exit;
    }

    if ($fileBaru) {
      @unlink(dirname(__DIR__, 2) . '/' . $fileBaru['path_file']);
    }
    $error = 'Data buku saku gagal diperbarui.';
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

            <h4>Edit Buku Saku</h4>

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
                  required
                  value="<?= htmlspecialchars($data['judul']); ?>">

              </div>

              <div class="mb-3">

                <label class="form-label">

                  File Saat Ini

                </label>

                <input
                  type="text"
                  class="form-control"
                  value="<?= htmlspecialchars($data['nama_file']); ?>"
                  readonly>

              </div>

              <div class="mb-3">

                <label class="form-label">

                  Ganti File PDF (Opsional)

                </label>

                <input
                  type="file"
                  name="file"
                  class="form-control"
                  accept=".pdf">

                <small class="text-muted">

                  Kosongkan jika tidak ingin mengganti file.

                </small>

              </div>

              <div class="d-flex gap-2">

                <button
                  type="submit"
                  name="update"
                  class="btn btn-primary">

                  <i class="bi bi-save me-2"></i>

                  Simpan Perubahan

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
