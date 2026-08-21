<?php

require_once '../config/kepala_auth.php';
require_once '../config/function.php';

$title = 'Tanda Tangan Kepala';
$base_url = '../';
$activeMenu = 'dashboard';
$error = '';
$idUser = (int) $_SESSION['id_user'];
$kepala = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama, ttd FROM users WHERE id_user = {$idUser} LIMIT 1"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['ttd']) || $_FILES['ttd']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Pilih berkas gambar tanda tangan terlebih dahulu.';
    } else {
        $newName = uploadTTD($_FILES['ttd']);
        if (!$newName) {
            $error = 'Gunakan gambar PNG/JPG maksimal 2 MB.';
        } else {
            $folder = '../uploads/ttd/';
            $stmt = mysqli_prepare($conn, "UPDATE users SET ttd = ? WHERE id_user = ? AND role = 'kepala'");
            mysqli_stmt_bind_param($stmt, 'si', $newName, $idUser);

            if (mysqli_stmt_execute($stmt)) {
                if (!empty($kepala['ttd']) && is_file($folder . $kepala['ttd'])) {
                    @unlink($folder . $kepala['ttd']);
                }

                logActivity($conn, 'Edit data', 'profil_kepala', $idUser);
                header('Location: dashboard.php?ttd=success');
                exit;
            }

            @unlink($folder . $newName);
            $error = 'Tanda tangan gagal disimpan.';
        }
    }
}

include '../includes/header.php';
include '../includes/kepala_navbar.php';
include '../includes/kepala_sidebar.php';
?>

<main class="main-content">
  <div class="inventaris-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="dashboard-title">TANDA TANGAN KEPALA</h1>
        <p class="dashboard-sub">Tanda tangan ini akan disalin otomatis ke setiap laporan yang Anda validasi.</p>
      </div>
      <a href="dashboard.php" class="btn btn-inventaris-outline">Kembali</a>
    </div>

    <div class="inventaris-card">
      <div class="card-body">
        <h2 class="inventaris-heading">Unggah Tanda Tangan</h2>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="row g-4 align-items-center">
          <div class="col-md-4 text-center">
            <?php if (!empty($kepala['ttd'])): ?>
              <img class="img-fluid border rounded p-2 bg-white" style="max-height:150px" src="<?= $base_url ?>uploads/ttd/<?= rawurlencode($kepala['ttd']) ?>" alt="Tanda tangan <?= htmlspecialchars($kepala['nama'], ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
              <div class="p-5 border rounded bg-light text-muted"><i class="bi bi-pen fs-1 d-block mb-2"></i>Belum ada tanda tangan</div>
            <?php endif; ?>
          </div>

          <div class="col-md-8">
            <form method="post" enctype="multipart/form-data">
              <?= csrf_input() ?>
              <label class="form-label">Berkas tanda tangan</label>
              <input class="form-control mb-3" type="file" name="ttd" accept="image/png,image/jpeg" required>
              <div class="form-text mb-3">Gunakan PNG atau JPG dengan latar belakang transparan/putih, maksimal 2 MB.</div>
              <button class="btn btn-inventaris-primary"><i class="bi bi-upload me-2"></i>Simpan Tanda Tangan</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
