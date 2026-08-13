<?php
require_once "../config/kepala_auth.php";
$title = 'Dashboard Kepala BNN';
$base_url = '../';
$activeMenu = 'dashboard';

$menunggu = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM laporan WHERE status='menunggu_validasi'"))['total'] ?? 0);
$tervalidasi = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM laporan WHERE status='tervalidasi'"))['total'] ?? 0);
$hariIni = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM laporan WHERE status IN ('menunggu_validasi', 'tervalidasi') AND tanggal_laporan=CURDATE()"))['total'] ?? 0);
$ttd = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ttd FROM users WHERE id_user=" . (int) $_SESSION['id_user'] . " LIMIT 1"));
$ttdTersedia = !empty($ttd['ttd']);
$laporanTerbaru = mysqli_query($conn, "SELECT l.id_laporan,l.tanggal_laporan,l.created_at,u.nama,s.nama_shift
    FROM laporan l JOIN users u ON u.id_user=l.created_by JOIN jadwal_shift js ON js.id_jadwal=l.id_jadwal JOIN shift s ON s.id_shift=js.id_shift
    WHERE l.status='menunggu_validasi' ORDER BY l.created_at DESC LIMIT 5");

include '../includes/header.php';
include '../includes/kepala_navbar.php';
include '../includes/kepala_sidebar.php';
?>
<main class="main-content">
  <div class="satpam-dashboard">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="dashboard-title">DASHBOARD</h1>
        <p class="dashboard-sub">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Kepala BNN') ?></strong></p>
      </div>
      <a class="btn btn-inventaris-outline" href="profil_ttd.php"><i class="bi bi-pen me-2"></i><?= $ttdTersedia ? 'Perbarui Tanda Tangan' : 'Atur Tanda Tangan' ?></a>
    </div>
    <?php if (!$ttdTersedia): ?><div class="alert alert-warning border-0 shadow-sm d-flex gap-3 align-items-center"><i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div><strong>Tanda tangan belum tersedia.</strong><br><small>Unggah tanda tangan terlebih dahulu agar laporan yang divalidasi dapat langsung dicetak oleh Admin.</small></div>
      </div><?php endif; ?>
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <a class="card-menu text-decoration-none d-block" href="validasi/index.php?status=menunggu_validasi" aria-label="Lihat laporan menunggu validasi">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="menu-icon-wrap"><i class="bi bi-hourglass-split"></i></div>
            <div>
              <div class="menu-desc">Menunggu Validasi</div>
              <div class="fs-2 fw-bold text-dark"><?= $menunggu ?></div>
              <div class="small text-muted">Laporan final</div>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a class="card-menu text-decoration-none d-block" href="validasi/index.php?status=tervalidasi" aria-label="Lihat laporan yang sudah divalidasi">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="menu-icon-wrap"><i class="bi bi-check2-circle"></i></div>
            <div>
              <div class="menu-desc">Sudah Divalidasi</div>
              <div class="fs-2 fw-bold text-dark"><?= $tervalidasi ?></div>
              <div class="small text-muted">Seluruh laporan</div>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a class="card-menu text-decoration-none d-block" href="validasi/index.php?status=hari_ini" aria-label="Lihat laporan hari ini">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="menu-icon-wrap"><i class="bi bi-calendar-day"></i></div>
            <div>
              <div class="menu-desc">Masuk Hari Ini</div>
              <div class="fs-2 fw-bold text-dark"><?= $hariIni ?></div>
              <div class="small text-muted">Siap ditinjau</div>
            </div>
          </div>
        </a>
      </div>
    </div>
    <div class="card progress-card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
          <div>
            <div class="section-title">Laporan Menunggu Validasi</div>
            <div class="small text-muted">Laporan yang telah diselesaikan dan dikirim oleh Satpam.</div>
          </div><a href="validasi/index.php" class="btn btn-inventaris-primary">Lihat Semua</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Dibuat Oleh</th>
                <th>Shift</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($laporanTerbaru)): while ($laporan = mysqli_fetch_assoc($laporanTerbaru)): ?><tr>
                    <td class="fw-semibold"><?= date('d/m/Y', strtotime($laporan['tanggal_laporan'])) ?></td>
                    <td><?= htmlspecialchars($laporan['nama']) ?></td>
                    <td><?= htmlspecialchars($laporan['nama_shift']) ?></td>
                    <td class="text-center"><a class="btn btn-primary btn-sm" href="validasi/detail.php?id=<?= (int)$laporan['id_laporan'] ?>"><i class="bi bi-eye me-1"></i>Detail</a></td>
                  </tr><?php endwhile;
                    else: ?><tr>
                  <td colspan="4" class="text-center text-muted py-5"><i class="bi bi-clipboard-check fs-2 d-block mb-2"></i>Tidak ada laporan yang menunggu validasi.</td>
                </tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
