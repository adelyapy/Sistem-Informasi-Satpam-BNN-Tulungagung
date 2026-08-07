<?php
require_once '../../config/satpam_auth.php';

$idLaporan = (int) ($_GET['id'] ?? 0);
$idSatpam = (int) ($_SESSION['id_user'] ?? 0);

$laporanStmt = mysqli_prepare($conn, '
    SELECT l.*, j.tanggal, s.nama_shift, s.jam_mulai, s.jam_selesai, pembuat.nama AS nama_pembuat
    FROM laporan l
    INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
    INNER JOIN jadwal_shift j ON j.id_jadwal = l.id_jadwal
    INNER JOIN shift s ON s.id_shift = j.id_shift
    LEFT JOIN users pembuat ON pembuat.id_user = l.created_by
    WHERE l.id_laporan = ? AND a.id_satpam = ?
    LIMIT 1
');
mysqli_stmt_bind_param($laporanStmt, 'ii', $idLaporan, $idSatpam);
mysqli_stmt_execute($laporanStmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt));

if (!$laporan) {
  header('Location: index.php');
  exit;
}

$inventarisStmt = mysqli_prepare($conn, 'SELECT nama_barang, jumlah, keterangan FROM inventaris WHERE id_laporan = ? ORDER BY urutan ASC');
mysqli_stmt_bind_param($inventarisStmt, 'i', $idLaporan);
mysqli_stmt_execute($inventarisStmt);
$inventaris = mysqli_fetch_all(mysqli_stmt_get_result($inventarisStmt), MYSQLI_ASSOC);

$uraianStmt = mysqli_prepare($conn, 'SELECT jam, uraian FROM uraian_kegiatan WHERE id_laporan = ? ORDER BY urutan ASC');
mysqli_stmt_bind_param($uraianStmt, 'i', $idLaporan);
mysqli_stmt_execute($uraianStmt);
$uraian = mysqli_fetch_all(mysqli_stmt_get_result($uraianStmt), MYSQLI_ASSOC);

$statusTervalidasi = $laporan['status'] === 'tervalidasi';
$statusMenunggu = $laporan['status'] === 'menunggu_validasi';
$title = 'Detail Laporan';
$pageTitle = 'Detail Laporan';
$base_url = '../../';
$activeMenu = 'laporan';
include '../../includes/header.php';
?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">

<?php include '../../includes/satpam_navbar.php'; ?>
<?php include '../../includes/satpam_sidebar.php'; ?>

<main class="main-content">
  <div class="inventaris-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h2 class="detail-title">Detail Laporan</h2>
        <p class="text-muted mb-0">Rincian laporan buku mutasi satpam.</p>
      </div><a class="btn btn-light btn-inventaris-outline" href="index.php"><i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Laporan</a>
    </div>
    <section class="inventaris-card mb-3">
      <div class="card-body">
        <h2 class="inventaris-heading">Informasi Laporan</h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="detail-label">Tanggal Laporan</div>
            <div class="detail-value"><?= htmlspecialchars($laporan['tanggal_laporan']) ?></div>
          </div>
          <div class="col-md-4">
            <div class="detail-label">Shift</div>
            <div class="detail-value"><?= htmlspecialchars($laporan['nama_shift']) ?> <span class="text-muted fw-normal">(<?= substr($laporan['jam_mulai'], 0, 5) ?>–<?= substr($laporan['jam_selesai'], 0, 5) ?>)</span></div>
          </div>
          <div class="col-md-4">
            <div class="detail-label">Dibuat Oleh</div>
            <div class="detail-value"><?= htmlspecialchars($laporan['nama_pembuat'] ?: 'Satpam') ?></div>
          </div>
          <?php if ($statusTervalidasi || $statusMenunggu) { ?>
            <div class="col-12"><span class="report-status <?= $statusTervalidasi ? 'report-status-valid' : 'report-status-pending' ?>"><i class="bi <?= $statusTervalidasi ? 'bi-check-circle-fill' : 'bi-clock-history' ?> me-1"></i><?= $statusTervalidasi ? 'Sudah divalidasi Kepala BNN' : 'Menunggu validasi Kepala BNN' ?></span></div>
          <?php } else { ?>
            <div class="col-12"><span class="report-status report-status-draft"><i class="bi bi-pencil-square me-1"></i>Draft - belum difinalisasi</span></div>
          <?php } ?>
        </div>
      </div>
    </section>

    <section class="inventaris-card mb-3">
      <div class="card-body">
        <h2 class="inventaris-heading">Inventaris</h2>
        <div class="inventory-table table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>No.</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody><?php if (!$inventaris) { ?><tr>
                  <td colspan="4" class="text-center text-muted py-4">Belum ada data inventaris.</td>
                </tr><?php } ?><?php foreach ($inventaris as $nomor => $row) { ?><tr>
                  <td><?= $nomor + 1 ?></td>
                  <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                  <td><?= (int) $row['jumlah'] ?></td>
                  <td><?= htmlspecialchars($row['keterangan']) ?></td>
                </tr><?php } ?></tbody>
          </table>
        </div>
      </div>
    </section>
    <section class="inventaris-card">
      <div class="card-body">
        <h2 class="inventaris-heading">Uraian Kegiatan</h2>
        <div class="inventory-table table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>No.</th>
                <th>Waktu</th>
                <th>Uraian Kegiatan</th>
              </tr>
            </thead>
            <tbody><?php if (!$uraian) { ?><tr>
                  <td colspan="3" class="text-center text-muted py-4">Belum ada uraian kegiatan.</td>
                </tr><?php } ?><?php foreach ($uraian as $nomor => $row) { ?><tr>
                  <td><?= $nomor + 1 ?></td>
                  <td><?= htmlspecialchars(substr($row['jam'], 0, 5)) ?> WIB</td>
                  <td class="activity-description"><?= nl2br(htmlspecialchars($row['uraian'])) ?></td>
                </tr><?php } ?></tbody>
          </table>
        </div>
      </div>
    </section>

    <?php if (!$statusTervalidasi && !$statusMenunggu) { ?>
      <section class="inventaris-card mt-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div>
            <h2 class="inventaris-heading mb-1">Finalisasi Laporan</h2>
            <p class="text-muted mb-0">Pastikan inventaris dan uraian kegiatan sudah lengkap. Setelah difinalisasi, laporan tidak dapat diubah dan dikirim ke Kepala BNN untuk validasi.</p>
          </div>
          <a class="btn btn-inventaris-primary" href="kirim.php?id=<?= $idLaporan ?>"><i class="bi bi-send-check me-2"></i>Finalisasi &amp; Kirim ke Kepala</a>
        </div>
      </section>
    <?php } ?>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>