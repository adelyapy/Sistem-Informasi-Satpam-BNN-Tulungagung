<?php
require_once "../../config/admin_auth.php";
require_once "../../config/shift_config.php";

ensureShiftDobel($conn);
$title = "Tambah Jadwal Satpam";
$base_url = "../../";
$activeMenu = "jadwal_satpam";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idSatpam = (int) ($_POST['id_satpam'] ?? 0);
  $idShift = (int) ($_POST['id_shift'] ?? 0);
  $tanggal = $_POST['tanggal'] ?? '';
  $status = $_POST['status'] ?? 'bertugas';
  if ($idSatpam <= 0 || $idShift <= 0 || !$tanggal || !in_array($status, ['bertugas', 'libur'], true)) {
    $error = 'Lengkapi data jadwal dengan benar.';
  } else {
    $check = mysqli_prepare($conn, 'SELECT id_jadwal FROM jadwal_shift WHERE id_satpam=? AND id_shift=? AND tanggal=?');
    mysqli_stmt_bind_param($check, 'iis', $idSatpam, $idShift, $tanggal);
    mysqli_stmt_execute($check);
    if (mysqli_num_rows(mysqli_stmt_get_result($check)) > 0) {
      $error = 'Jadwal untuk satpam, shift, dan tanggal tersebut sudah ada.';
    } else {
      $stmt = mysqli_prepare($conn, 'INSERT INTO jadwal_shift (id_satpam, id_shift, tanggal, status) VALUES (?, ?, ?, ?)');
      mysqli_stmt_bind_param($stmt, 'iiss', $idSatpam, $idShift, $tanggal, $status);
      if (mysqli_stmt_execute($stmt)) {
        header('Location: index.php?success=1');
        exit;
      }
      $error = 'Jadwal tidak dapat disimpan. Silakan coba lagi.';
    }
  }
}
$satpam = mysqli_query($conn, "SELECT id_user, nama, kode_satpam FROM users WHERE role='satpam' AND status='aktif' ORDER BY nama");
$shift = mysqli_query($conn, 'SELECT * FROM shift ORDER BY jam_mulai');
include "../../includes/header.php";
include "../../includes/admin_navbar.php";
include "../../includes/admin_sidebar.php";
?>
<main class="main-content">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1">Tambah Jadwal Satpam</h2>
        <p class="text-muted mb-0">Tetapkan petugas dan shift untuk satu hari kerja.</p>
      </div><a href="index.php" class="btn btn-outline-secondary">Kembali</a>
    </div>
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <form method="post" class="row g-3">
          <?php if ($error): ?><div class="col-12">
              <div class="alert alert-danger mb-0"><?= htmlspecialchars($error) ?></div>
            </div><?php endif; ?>
          <div class="col-md-6"><label class="form-label">Nama Satpam</label><select name="id_satpam" class="form-select" required>
              <option value="">Pilih satpam</option><?php while ($u = mysqli_fetch_assoc($satpam)): ?><option value="<?= $u['id_user'] ?>" <?= ((int)($_POST['id_satpam'] ?? 0) === (int)$u['id_user']) ? 'selected' : '' ?>><?= htmlspecialchars($u['nama'] . ' — ' . $u['kode_satpam']) ?></option><?php endwhile; ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Shift</label><select name="id_shift" class="form-select" required>
              <option value="">Pilih shift</option><?php while ($s = mysqli_fetch_assoc($shift)): ?><option value="<?= $s['id_shift'] ?>" <?= ((int)($_POST['id_shift'] ?? 0) === (int)$s['id_shift']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nama_shift']) ?> (<?= substr($s['jam_mulai'], 0, 5) ?> - <?= substr($s['jam_selesai'], 0, 5) ?>)</option><?php endwhile; ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Tanggal Tugas</label><input type="date" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')) ?>" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select">
              <option value="bertugas">Bertugas</option>
              <option value="libur">Libur</option>
            </select></div>
          <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Jadwal</button></div>
        </form>
      </div>
    </div>
  </div>
</main>
<?php include "../../includes/footer.php"; ?>
