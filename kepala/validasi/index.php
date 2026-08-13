<?php
require_once '../../config/kepala_auth.php';

$title = 'Daftar Laporan';
$pageTitle = 'DAFTAR LAPORAN';
$base_url = '../../';
$activeMenu = 'laporan';
$tanggal = $_GET['tanggal'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$statusFilter = $_GET['status'] ?? 'semua';

$statusValid = ['semua', 'menunggu_validasi', 'tervalidasi', 'hari_ini'];
if (!in_array($statusFilter, $statusValid, true)) {
  $statusFilter = 'semua';
}

$where = ["l.status IN ('menunggu_validasi', 'tervalidasi')"];
if ($statusFilter === 'menunggu_validasi' || $statusFilter === 'tervalidasi') {
  $where[] = "l.status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
}
if ($statusFilter === 'hari_ini') {
  $where[] = 'l.tanggal_laporan = CURDATE()';
}
if ($tanggal !== '') {
  $where[] = "l.tanggal_laporan = '" . mysqli_real_escape_string($conn, $tanggal) . "'";
}
if ($cari !== '') {
  $safe = mysqli_real_escape_string($conn, $cari);
  $where[] = "(u.nama LIKE '%$safe%' OR u.kode_satpam LIKE '%$safe%')";
}

$filter = implode(' AND ', $where);
$laporan = mysqli_query(
  $conn,
  "SELECT l.id_laporan, l.tanggal_laporan, l.created_at, l.status, u.nama, u.kode_satpam,
          s.nama_shift, s.jam_mulai, s.jam_selesai
   FROM laporan l
   JOIN users u ON u.id_user = l.created_by
   JOIN jadwal_shift js ON js.id_jadwal = l.id_jadwal
   JOIN shift s ON s.id_shift = js.id_shift
   WHERE $filter
   ORDER BY l.tanggal_laporan DESC, l.created_at DESC"
);

$judulDaftar = [
  'semua' => 'Semua Laporan',
  'menunggu_validasi' => 'Menunggu Validasi',
  'tervalidasi' => 'Sudah Divalidasi',
  'hari_ini' => 'Laporan Hari Ini',
][$statusFilter];

include '../../includes/header.php';
include '../../includes/kepala_navbar.php';
include '../../includes/kepala_sidebar.php';
?>

<main class="main-content">
  <div class="satpam-dashboard">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="dashboard-title">DAFTAR LAPORAN</h1>
        <p class="dashboard-sub">Tinjau seluruh laporan final Satpam, baik yang menunggu maupun sudah divalidasi.</p>
      </div>
      <a href="../dashboard.php" class="btn btn-inventaris-outline"><i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard</a>
    </div>

    <?php if (!empty($_SESSION['kepala_success'])): ?>
      <div class="alert alert-success border-0 shadow-sm"><?= htmlspecialchars($_SESSION['kepala_success']); unset($_SESSION['kepala_success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['kepala_error'])): ?>
      <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars($_SESSION['kepala_error']); unset($_SESSION['kepala_error']); ?></div>
    <?php endif; ?>

    <div class="inventaris-card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
          <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" class="form-control" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>"></div>
          <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="semua" <?= $statusFilter === 'semua' ? 'selected' : '' ?>>Semua laporan</option><option value="menunggu_validasi" <?= $statusFilter === 'menunggu_validasi' ? 'selected' : '' ?>>Menunggu validasi</option><option value="tervalidasi" <?= $statusFilter === 'tervalidasi' ? 'selected' : '' ?>>Sudah divalidasi</option><option value="hari_ini" <?= $statusFilter === 'hari_ini' ? 'selected' : '' ?>>Laporan hari ini</option></select></div>
          <div class="col-md-3"><label class="form-label">Cari Satpam</label><input type="search" class="form-control" name="cari" placeholder="Nama atau kode satpam" value="<?= htmlspecialchars($cari) ?>"></div>
          <div class="col-md-3 d-flex gap-2"><button class="btn btn-inventaris-primary"><i class="bi bi-search me-1"></i>Filter</button><a href="index.php" class="btn btn-inventaris-outline">Reset</a></div>
        </form>
      </div>
    </div>

    <div class="inventaris-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="inventaris-heading mb-0"><?= $judulDaftar ?></h2><span class="badge-status badge-warning"><?= mysqli_num_rows($laporan) ?> Laporan</span></div>
        <div class="table-responsive">
          <table class="table report-table align-middle mb-0">
            <thead class="table-light"><tr><th>No.</th><th>Tanggal &amp; Waktu</th><th>Dibuat Oleh</th><th>Shift</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
              <?php if (mysqli_num_rows($laporan)): $no = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($laporan)): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><div class="fw-semibold"><?= date('d/m/Y', strtotime($row['tanggal_laporan'])) ?></div><small class="text-muted"><?= date('H:i', strtotime($row['created_at'])) ?> WIB</small></td>
                    <td><div class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></div><small class="text-muted"><?= htmlspecialchars($row['kode_satpam']) ?></small></td>
                    <td><div class="fw-semibold"><?= htmlspecialchars($row['nama_shift']) ?></div><small><?= substr($row['jam_mulai'], 0, 5) ?>–<?= substr($row['jam_selesai'], 0, 5) ?></small></td>
                    <td><?php if ($row['status'] === 'tervalidasi'): ?><span class="report-status report-status-validated"><i class="bi bi-check2-circle me-1"></i>Tervalidasi</span><?php else: ?><span class="report-status report-status-pending"><i class="bi bi-clock-history me-1"></i>Menunggu</span><?php endif; ?></td>
                    <td class="text-center"><a href="detail.php?id=<?= (int) $row['id_laporan'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-eye me-1"></i>Lihat</a></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada laporan yang sesuai.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../../includes/footer.php'; ?>
