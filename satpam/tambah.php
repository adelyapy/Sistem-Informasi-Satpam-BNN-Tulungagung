<?php

require_once "../config/admin_auth.php";
require_once "../config/function.php";

$title = "Tambah Satpam";
$base_url = "../";
$activeMenu = "data_satpam";

if (isset($_POST['simpan'])) {

  $kode_satpam = trim((string) ($_POST['kode_satpam'] ?? ''));
  $nama = trim((string) ($_POST['nama'] ?? ''));
  $error = '';

  if ($kode_satpam === '' || $nama === '') {
    $error = 'Kode Satpam dan nama Satpam wajib diisi.';
  } else {
    $cekKode = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE kode_satpam = ? LIMIT 1');
    mysqli_stmt_bind_param($cekKode, 's', $kode_satpam);
    mysqli_stmt_execute($cekKode);

    if (mysqli_fetch_assoc(mysqli_stmt_get_result($cekKode))) {
      $error = 'Kode Satpam sudah digunakan. Gunakan kode Satpam lain.';
    }
  }

  if ($error === '') {
    $foto = uploadFoto($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
    $ttd = uploadTTD($_FILES['ttd'] ?? ['error' => UPLOAD_ERR_NO_FILE]);

    try {
      $query = mysqli_prepare($conn, "
        INSERT INTO users (kode_satpam, nama, username, password, foto, ttd, role)
        VALUES (?, ?, NULL, NULL, ?, ?, 'satpam')
      ");
      mysqli_stmt_bind_param($query, 'ssss', $kode_satpam, $nama, $foto, $ttd);
      mysqli_stmt_execute($query);

      logActivity($conn, 'Tambah data', 'satpam', (int) mysqli_insert_id($conn));
      header('Location: index.php?success=tambah');
      exit;
    } catch (Throwable $exception) {
      appLog($exception);
      $error = 'Data Satpam tidak dapat disimpan. Silakan periksa kembali kode Satpam.';
    }
  }
}

include "../includes/header.php";

?>

<?php include "../includes/navbar.php"; ?>
<?php include "../includes/admin_sidebar.php"; ?>

<div class="main-content">

  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

      <div>

        <h3 class="fw-bold">
          Tambah Satpam
        </h3>

        <p class="text-muted mb-0">
          Tambahkan anggota satpam baru.
        </p>

      </div>

      <a href="index.php" class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>

        Kembali

      </a>

    </div>

    <div class="card shadow-sm border-0">

      <div class="card-body">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <?= csrf_input() ?>

          <div class="row">

            <div class="col-lg-6 mb-3">

              <label class="form-label">

                Kode Satpam

              </label>

              <input
                type="text"
                name="kode_satpam"
                class="form-control"
                value="<?= htmlspecialchars($kode_satpam ?? '') ?>"
                placeholder="Contoh: STP006"
                required>

            </div>

            <div class="col-lg-6 mb-3">

              <label class="form-label">

                Nama Satpam

              </label>

              <input
                type="text"
                name="nama"
                class="form-control"
                required>

            </div>

            <div class="col-lg-6 mb-3">

              <label class="form-label">

                Foto

              </label>

              <input
                type="file"
                name="foto"
                class="form-control"
                accept=".jpg,.jpeg,.png">

              <div class="form-text">

                Format: JPG / JPEG / PNG

              </div>

            </div>

            <div class="col-lg-6 mb-3">

              <label class="form-label">

                Tanda Tangan

              </label>

              <input
                type="file"
                name="ttd"
                class="form-control"
                accept=".jpg,.jpeg,.png">

              <div class="form-text">

                Format: JPG / JPEG / PNG

              </div>

            </div>

            <div class="col-12">

              <button
                type="submit"
                name="simpan"
                class="btn btn-primary">

                <i class="bi bi-check-circle me-2"></i>

                Simpan

              </button>

              <a
                href="index.php"
                class="btn btn-secondary">

                Batal

              </a>

            </div>

          </div>

        </form>

      </div>

    </div>

  </div>

</div>

<?php include "../includes/footer.php"; ?>
