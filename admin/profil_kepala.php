<?php
require_once '../config/admin_auth.php';
require_once '../config/function.php';

$kepalaQuery = mysqli_query($conn, "SELECT id_user, nama, foto, ttd FROM users WHERE role = 'kepala' LIMIT 1");
$kepala = mysqli_fetch_assoc($kepalaQuery);

if (!$kepala) {
  $_SESSION['admin_error'] = 'Akun Kepala BNN belum tersedia.';
  header('Location: dashboard/dashboard.php');
  exit;
}

$error = '';
$berhasilDisimpan = !empty($_SESSION['admin_success']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fotoBaru = uploadFoto($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
  $ttdBaru = uploadTTD($_FILES['ttd'] ?? ['error' => UPLOAD_ERR_NO_FILE]);

  $foto = $fotoBaru ?: $kepala['foto'];
  $ttd = $ttdBaru ?: $kepala['ttd'];

  $update = mysqli_prepare($conn, 'UPDATE users SET foto = ?, ttd = ? WHERE id_user = ? AND role = \'kepala\'');
  mysqli_stmt_bind_param($update, 'ssi', $foto, $ttd, $kepala['id_user']);

  if (mysqli_stmt_execute($update)) {
    if ($fotoBaru && !empty($kepala['foto'])) {
      @unlink('../uploads/foto/' . $kepala['foto']);
    }
    if ($ttdBaru && !empty($kepala['ttd'])) {
      @unlink('../uploads/ttd/' . $kepala['ttd']);
    }
    $_SESSION['admin_success'] = 'Foto dan tanda tangan Kepala BNN berhasil diperbarui.';
    header('Location: profil_kepala.php');
    exit;
  }

  $error = 'Data Kepala BNN tidak dapat diperbarui.';
}

$title = 'Profil Kepala BNN';
$pageTitle = 'PROFIL KEPALA BNN';
$base_url = '../';
$activeMenu = 'profil_kepala';
include '../includes/header.php';
include '../includes/admin_navbar.php';
include '../includes/admin_sidebar.php';
?>

<main class="main-content">
  <div class="admin-form-container">
    <div class="mb-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
          <h2 class="fw-bold mb-1">Profil Kepala BNN</h2>
          <p class="text-muted mb-0">Kelola foto profil dan tanda tangan Kepala BNN untuk proses validasi laporan.</p>
        </div>
        <a href="dashboard/dashboard.php" class="btn btn-inventaris-outline"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
      </div>
    </div>

    <div class="card shadow-sm border-0 admin-form-card">
      <div class="card-body p-4 p-lg-5">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['admin_success'])): ?>
          <div class="alert alert-success"><?= htmlspecialchars($_SESSION['admin_success']);
                                            unset($_SESSION['admin_success']); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="row g-4">
          <?= csrf_input() ?>
          <div class="col-12">
            <label class="form-label">Nama Kepala BNN</label>
            <input class="form-control" value="<?= htmlspecialchars($kepala['nama']) ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Foto Profil Kepala</label>
            <input class="form-control" type="file" name="foto" accept="image/png,image/jpeg">
            <div class="form-text">PNG/JPG, maksimal 2 MB. Kosongkan bila tidak ingin mengganti.</div>
            <?php if (!empty($kepala['foto'])): ?>
              <img class="profile-preview mt-3" src="../uploads/foto/<?= rawurlencode($kepala['foto']) ?>" alt="Foto Kepala BNN">
            <?php endif; ?>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tanda Tangan Kepala</label>
            <input class="form-control" type="file" name="ttd" accept="image/png,image/jpeg">
            <div class="form-text">PNG/JPG, maksimal 2 MB. Tanda tangan disalin otomatis saat laporan divalidasi.</div>
            <?php if (!empty($kepala['ttd'])): ?>
              <img class="signature-preview mt-3" src="../uploads/ttd/<?= rawurlencode($kepala['ttd']) ?>" alt="Tanda tangan Kepala BNN">
            <?php endif; ?>
          </div>
          <?php if (!$berhasilDisimpan): ?>
            <div class="col-12"><button class="btn btn-primary px-4">Simpan Perubahan</button></div>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
