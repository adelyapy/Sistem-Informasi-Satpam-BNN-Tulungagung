<?php
require_once '../config/admin_auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT id_user, kode_satpam, nama, status FROM users WHERE id_user = ? AND role = 'satpam' LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id); mysqli_stmt_execute($stmt);
$satpam = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$satpam) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = trim($_POST['kode_satpam'] ?? ''); $nama = trim($_POST['nama'] ?? ''); $status = $_POST['status'] ?? 'aktif';
    if ($kode === '' || $nama === '' || !in_array($status, ['aktif', 'nonaktif'], true)) $error = 'Lengkapi data satpam dengan benar.';
    else {
        $update = mysqli_prepare($conn, "UPDATE users SET kode_satpam = ?, nama = ?, status = ? WHERE id_user = ? AND role = 'satpam'");
        mysqli_stmt_bind_param($update, 'sssi', $kode, $nama, $status, $id);
        if (mysqli_stmt_execute($update)) { header('Location: index.php'); exit; }
        $error = 'Data tidak dapat diperbarui. Kode Satpam mungkin sudah digunakan.';
    }
}
$title='Edit Satpam'; $pageTitle='Edit Satpam'; $base_url='../'; $activeMenu='data_satpam'; include '../includes/header.php'; ?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css"><link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">
<?php include '../includes/admin_navbar.php'; include '../includes/admin_sidebar.php'; ?>
<main class="main-content"><div class="container-fluid"><div class="d-flex justify-content-between align-items-center mb-4"><div><h2 class="fw-bold mb-1">Edit Satpam</h2><p class="text-muted mb-0">Perbarui data anggota satpam.</p></div><a href="index.php" class="btn btn-outline-secondary">Kembali</a></div><div class="card shadow-sm border-0"><div class="card-body p-4"><form method="post" class="row g-3"><?php if($error): ?><div class="col-12"><div class="alert alert-danger mb-0"><?= htmlspecialchars($error) ?></div></div><?php endif; ?><div class="col-md-6"><label class="form-label">Kode Satpam</label><input class="form-control" name="kode_satpam" value="<?= htmlspecialchars($satpam['kode_satpam']) ?>" required></div><div class="col-md-6"><label class="form-label">Nama Satpam</label><input class="form-control" name="nama" value="<?= htmlspecialchars($satpam['nama']) ?>" required></div><div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="aktif" <?= $satpam['status']==='aktif'?'selected':'' ?>>Aktif</option><option value="nonaktif" <?= $satpam['status']==='nonaktif'?'selected':'' ?>>Nonaktif</option></select></div><div class="col-12"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div></div></main><?php include '../includes/footer.php'; ?>
