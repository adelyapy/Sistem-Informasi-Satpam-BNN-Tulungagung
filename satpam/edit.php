<?php
require_once '../config/admin_auth.php';
require_once '../config/function.php';

$id = (int) ($_GET['id'] ?? 0);
$query = mysqli_prepare($conn, 'SELECT id_user, kode_satpam, nama, status, foto, ttd FROM users WHERE id_user = ? AND role = \'satpam\' LIMIT 1');
mysqli_stmt_bind_param($query, 'i', $id);
mysqli_stmt_execute($query);
$satpam = mysqli_fetch_assoc(mysqli_stmt_get_result($query));

if (!$satpam) {
  header('Location: index.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $kode = trim($_POST['kode_satpam'] ?? '');
  $nama = trim($_POST['nama'] ?? '');
  $status = $_POST['status'] ?? 'aktif';
  $fotoBaru = uploadFoto($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
  $ttdBaru = uploadTTD($_FILES['ttd'] ?? ['error' => UPLOAD_ERR_NO_FILE]);

  if ($kode === '' || $nama === '' || !in_array($status, ['aktif', 'nonaktif'], true)) {
    $error = 'Lengkapi data Satpam dengan benar.';
  } else {
    $foto = $fotoBaru ?: $satpam['foto'];
    $ttd = $ttdBaru ?: $satpam['ttd'];
    $update = mysqli_prepare($conn, 'UPDATE users SET kode_satpam = ?, nama = ?, status = ?, foto = ?, ttd = ? WHERE id_user = ? AND role = \'satpam\'');
    mysqli_stmt_bind_param($update, 'sssssi', $kode, $nama, $status, $foto, $ttd, $id);

    if (mysqli_stmt_execute($update)) {
      logActivity($conn, 'Edit data', 'satpam', $id);
      if ($fotoBaru && !empty($satpam['foto'])) {
        @unlink('../uploads/foto/' . $satpam['foto']);
      }
      if ($ttdBaru && !empty($satpam['ttd'])) {
        @unlink('../uploads/ttd/' . $satpam['ttd']);
      }
      header('Location: detail.php?id=' . $id);
      exit;
    }

    $error = 'Data tidak dapat diperbarui. Kode Satpam mungkin sudah digunakan.';
  }
}

$title = 'Edit Satpam';
$pageTitle = 'EDIT SATPAM';
$base_url = '../';
$activeMenu = 'data_satpam';
include '../includes/header.php';
include '../includes/admin_navbar.php';
include '../includes/admin_sidebar.php';
?>

<main class="main-content">
  <div class="admin-form-container">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Edit Satpam</h2>
        <p class="text-muted mb-0">Perbarui data anggota Satpam, foto, dan tanda tangan.</p>
      </div>
      <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm border-0 admin-form-card">
      <div class="card-body p-4 p-lg-5">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="row g-4">
          <?= csrf_input() ?>
          <div class="col-md-6">
            <label class="form-label">Kode Satpam</label>
            <input class="form-control" name="kode_satpam" value="<?= htmlspecialchars($satpam['kode_satpam']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nama Satpam</label>
            <input class="form-control" name="nama" value="<?= htmlspecialchars($satpam['nama']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="aktif" <?= $satpam['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
              <option value="nonaktif" <?= $satpam['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <label class="form-label">Foto Satpam</label>
            <input class="form-control" type="file" name="foto" accept="image/png,image/jpeg">
            <div class="form-text">PNG/JPG, maksimal 2 MB. Kosongkan bila tidak ingin mengganti.</div>
            <?php if (!empty($satpam['foto'])): ?><img class="profile-preview mt-3" src="../uploads/foto/<?= rawurlencode($satpam['foto']) ?>" alt="Foto Satpam"><?php endif; ?>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tanda Tangan Satpam</label>
            <input class="form-control" type="file" name="ttd" accept="image/png,image/jpeg">
            <div class="form-text">PNG/JPG, maksimal 2 MB. Tanda tangan akan disalin saat laporan difinalisasi.</div>
            <?php if (!empty($satpam['ttd'])): ?><img class="signature-preview mt-3" src="../uploads/ttd/<?= rawurlencode($satpam['ttd']) ?>" alt="Tanda tangan Satpam"><?php endif; ?>
          </div>
          <div class="col-12"><button class="btn btn-primary px-4">Simpan Perubahan</button></div>
        </form>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
