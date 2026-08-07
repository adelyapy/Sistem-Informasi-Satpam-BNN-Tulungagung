<?php
require_once '../../config/admin_auth.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, 'SELECT * FROM jadwal_shift WHERE id_jadwal = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$jadwal = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$jadwal) {
  header('Location: index.php');
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tanggal = $_POST['tanggal'] ?? '';
  $status = $_POST['status'] ?? 'bertugas';
  if ($tanggal && in_array($status, ['bertugas', 'libur'], true)) {
    $update = mysqli_prepare($conn, 'UPDATE jadwal_shift SET tanggal = ?, status = ? WHERE id_jadwal = ?');
    mysqli_stmt_bind_param($update, 'ssi', $tanggal, $status, $id);
    mysqli_stmt_execute($update);
    header('Location: index.php');
    exit;
  }
}
$title = 'Edit Jadwal Satpam';
$pageTitle = 'Edit Jadwal Satpam';
$base_url = '../../';
$activeMenu = 'jadwal_satpam';
include '../../includes/header.php'; ?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">
<?php include '../../includes/admin_navbar.php';
include '../../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold mb-0">Edit Jadwal Satpam</h2><a href="index.php" class="btn btn-outline-secondary">Kembali</a>
    </div>
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <form method="post" class="row g-3">
          <div class="col-md-6"><label class="form-label">Tanggal Tugas</label><input class="form-control" type="date" name="tanggal" value="<?= htmlspecialchars($jadwal['tanggal']) ?>" required></div>
          <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status">
              <option value="bertugas" <?= $jadwal['status'] === 'bertugas' ? 'selected' : '' ?>>Bertugas</option>
              <option value="libur" <?= $jadwal['status'] === 'libur' ? 'selected' : '' ?>>Libur</option>
            </select></div>
          <div class="col-12"><button class="btn btn-primary">Simpan Perubahan</button></div>
        </form>
      </div>
    </div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>