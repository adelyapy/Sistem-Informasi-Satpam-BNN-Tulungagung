<?php
require_once '../../config/admin_auth.php';
require_once '../../config/report_attachment.php';

$title = 'Detail Laporan';
$pageTitle = 'Detail Laporan';
$base_url = '../../';
$activeMenu = 'monitoring_laporan';

if (!ensureLampiranFotoTable($conn)) {
  exit('Tabel lampiran foto tidak dapat disiapkan.');
}

$idLaporan = (int) ($_GET['id'] ?? 0);
if ($idLaporan < 1) {
  header('Location: index.php');
  exit;
}

$laporanStmt = mysqli_prepare($conn, '
  SELECT l.*, u.nama, u.kode_satpam, j.tanggal, s.nama_shift, s.jam_mulai, s.jam_selesai
  FROM laporan l
  LEFT JOIN users u ON u.id_user = l.created_by
  LEFT JOIN jadwal_shift j ON j.id_jadwal = l.id_jadwal
  LEFT JOIN shift s ON s.id_shift = j.id_shift
  WHERE l.id_laporan = ?
  LIMIT 1
');
mysqli_stmt_bind_param($laporanStmt, 'i', $idLaporan);
mysqli_stmt_execute($laporanStmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt));
if (!$data) {
  header('Location: index.php');
  exit;
}

$anggotaStmt = mysqli_prepare($conn, '
  SELECT u.nama, u.kode_satpam
  FROM anggota_shift a
  INNER JOIN users u ON u.id_user = a.id_satpam
  WHERE a.id_laporan = ?
  ORDER BY u.nama ASC
');
mysqli_stmt_bind_param($anggotaStmt, 'i', $idLaporan);
mysqli_stmt_execute($anggotaStmt);
$anggota = mysqli_fetch_all(mysqli_stmt_get_result($anggotaStmt), MYSQLI_ASSOC);

$inventarisStmt = mysqli_prepare($conn, '
  SELECT i.*, u.nama AS nama_input
  FROM inventaris i
  LEFT JOIN users u ON u.id_user = i.created_by
  WHERE i.id_laporan = ?
  ORDER BY i.urutan ASC, i.id_inventaris ASC
');
mysqli_stmt_bind_param($inventarisStmt, 'i', $idLaporan);
mysqli_stmt_execute($inventarisStmt);
$inventaris = mysqli_fetch_all(mysqli_stmt_get_result($inventarisStmt), MYSQLI_ASSOC);

$uraianStmt = mysqli_prepare($conn, '
  SELECT uk.id_uraian, uk.urutan, uk.jam, uk.uraian, uk.created_at, pengguna.nama AS nama_input
  FROM uraian_kegiatan uk
  LEFT JOIN users pengguna ON pengguna.id_user = uk.created_by
  WHERE uk.id_laporan = ?
  ORDER BY uk.urutan ASC, uk.id_uraian ASC
');
mysqli_stmt_bind_param($uraianStmt, 'i', $idLaporan);
mysqli_stmt_execute($uraianStmt);
$uraian = mysqli_fetch_all(mysqli_stmt_get_result($uraianStmt), MYSQLI_ASSOC);

$fotoStmt = mysqli_prepare($conn, '
  SELECT f.id_lampiran, f.id_uraian, f.id_inventaris, f.nama_file, f.path_file
  FROM lampiran_foto f
  WHERE f.id_laporan = ?
  ORDER BY f.created_at ASC, f.id_lampiran ASC
');
mysqli_stmt_bind_param($fotoStmt, 'i', $idLaporan);
mysqli_stmt_execute($fotoStmt);
$lampiran = mysqli_fetch_all(mysqli_stmt_get_result($fotoStmt), MYSQLI_ASSOC);
$lampiranPerUraian = [];
$lampiranPerInventaris = [];
foreach ($lampiran as $foto) {
  if (!empty($foto['id_uraian'])) {
    $lampiranPerUraian[(int) $foto['id_uraian']][] = $foto;
  }
  if (!empty($foto['id_inventaris'])) {
    $lampiranPerInventaris[(int) $foto['id_inventaris']][] = $foto;
  }
}

$labelStatus = [
  'draft' => ['Draft', 'secondary'],
  'menunggu_validasi' => ['Menunggu Validasi', 'warning text-dark'],
  'tervalidasi' => ['Tervalidasi', 'success'],
];
[$statusLabel, $statusClass] = $labelStatus[$data['status']] ?? ['Tidak diketahui', 'danger'];

include '../../includes/header.php';
?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">
<style>
  .report-detail-table-card {
    border: 1px solid #dbe7fb !important;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 28px rgba(18, 81, 161, .10) !important;
  }

  .report-detail-table-card .card-header {
    padding: 15px 22px;
    background: linear-gradient(115deg, #0b58d0, #1677f2) !important;
    border: 0;
    font-size: 1.08rem;
    letter-spacing: .1px;
  }

  .report-info-card {
    border: 1px solid #dbe7fb !important;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 28px rgba(18, 81, 161, .10) !important;
  }

  .report-info-card .card-header {
    padding: 15px 22px;
    background: linear-gradient(115deg, #0b58d0, #1677f2) !important;
    border: 0;
    font-size: 1.08rem;
    letter-spacing: .1px;
  }

  .report-info-card .card-body {
    padding: 25px 28px;
    background: linear-gradient(135deg, #ffffff 0%, #f7faff 100%);
  }

  .report-info-card .small.text-muted {
    margin-bottom: 5px;
    color: #64748b !important;
    font-size: .82rem;
    font-weight: 700;
    letter-spacing: .035em;
    text-transform: uppercase;
  }

  .report-info-card .fw-semibold {
    color: #17345f;
    font-size: 1.08rem;
  }

  .report-info-card .row > div {
    padding-top: 7px;
    padding-bottom: 7px;
  }

  .report-info-card .badge {
    padding: 8px 13px;
    border-radius: 999px;
    font-size: .84rem;
  }

  .report-detail-table {
    min-width: 980px;
    border: 0;
  }

  .report-detail-table thead th {
    padding: 15px 16px;
    color: #17345f;
    background: #f3f7ff;
    border-color: #dbe7fb;
    font-size: .86rem;
    font-weight: 800;
    letter-spacing: .02em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .report-detail-table tbody td {
    padding: 15px 16px;
    color: #243957;
    border-color: #e3ebf8;
    vertical-align: middle;
  }

  .report-detail-table tbody tr:nth-child(even) td {
    background: #fbfdff;
  }

  .report-detail-table tbody tr:hover td {
    background: #eef5ff;
  }

  .report-detail-table .report-number {
    width: 68px;
    color: #0b58d0;
    font-weight: 800;
    text-align: center;
  }

  .report-detail-table .report-uploader {
    color: #0f766e;
    font-weight: 700;
    white-space: nowrap;
  }

  .report-detail-table .report-photo {
    width: 60px;
    height: 60px;
    padding: 3px;
    object-fit: cover;
    background: #fff;
    border: 1px solid #cbdcf5 !important;
    box-shadow: 0 3px 8px rgba(38, 87, 154, .15);
    transition: transform .18s ease;
  }

  .report-detail-table .report-photo:hover {
    transform: scale(1.08);
  }

  .main-content.admin-report-detail-content {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 28px 44px 40px;
  }

  .admin-report-detail-content .container-fluid {
    max-width: none;
  }

  @media (max-width: 576px) {
    .main-content.admin-report-detail-content {
      padding: 22px 16px 32px;
    }
  }
</style>
<?php include '../../includes/admin_navbar.php'; ?>
<?php include '../../includes/admin_sidebar.php'; ?>

<main class="main-content admin-report-detail-content">
  <div class="container-fluid px-0 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Detail Laporan</h2>
        <p class="text-secondary mb-0">Data yang ditampilkan sesuai dengan input e-SATPAM — Elektronik Sistem Administrasi Satpam.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        <?php if ($data['status'] === 'tervalidasi'): ?><a href="cetak.php?id=<?= (int) $data['id_laporan'] ?>" class="btn btn-success"><i class="bi bi-printer me-1"></i>Cetak</a><?php endif; ?>
      </div>
    </div>

    <section class="card shadow-sm border-0 mb-4 report-info-card">
      <div class="card-header bg-primary text-white fw-semibold">Informasi Laporan</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4"><div class="small text-muted">Tanggal Laporan</div><div class="fw-semibold"><?= date('d F Y', strtotime($data['tanggal_laporan'])) ?></div></div>
          <div class="col-md-4"><div class="small text-muted">Shift</div><div class="fw-semibold"><?= htmlspecialchars($data['nama_shift'] ?: '-') ?></div><small><?= htmlspecialchars(substr((string) $data['jam_mulai'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $data['jam_selesai'], 0, 5)) ?> WIB</small></div>
          <div class="col-md-4"><div class="small text-muted">Status</div><span class="badge bg-<?= $statusClass ?>"><?= $statusLabel ?></span></div>
          <div class="col-md-4"><div class="small text-muted">Pembuat Laporan</div><div class="fw-semibold"><?= htmlspecialchars($data['nama'] ?: '-') ?></div><small><?= htmlspecialchars($data['kode_satpam'] ?: '-') ?></small></div>
          <div class="col-md-8"><div class="small text-muted">Anggota Shift yang Login</div><div class="fw-semibold"><?php if ($anggota): ?><?php foreach ($anggota as $index => $petugas): ?><?= $index ? ', ' : '' ?><?= htmlspecialchars($petugas['nama']) ?> <small class="text-muted">(<?= htmlspecialchars($petugas['kode_satpam']) ?>)</small><?php endforeach; ?><?php else: ?>-<?php endif; ?></div></div>
        </div>
      </div>
    </section>

    <section class="card shadow-sm border-0 mb-4 report-detail-table-card">
      <div class="card-header bg-primary text-white fw-semibold">Uraian Kegiatan</div>
      <div class="table-responsive mobile-scroll-table activity-list-table admin-detail-table" tabindex="0" aria-label="Tabel uraian kegiatan, geser ke samping untuk melihat semua kolom">
        <table class="table table-bordered align-middle mb-0 report-detail-table">
          <thead><tr><th>No.</th><th>Tanggal &amp; Waktu</th><th>Shift</th><th>Uraian Kegiatan</th><th>Pengunggah</th><th>Lampiran Foto</th></tr></thead>
          <tbody>
            <?php if (!$uraian): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada uraian kegiatan.</td></tr><?php endif; ?>
            <?php foreach ($uraian as $index => $item): $fotoUraian = $lampiranPerUraian[(int) $item['id_uraian']] ?? []; ?>
              <tr>
                <td class="report-number"><?= $index + 1 ?></td>
                <td><?= date('d F Y', strtotime($data['tanggal_laporan'])) ?><br><small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars(substr($item['jam'], 0, 5)) ?> WIB</small></td>
                <td><?= htmlspecialchars($data['nama_shift'] ?: '-') ?></td>
                <td><?= nl2br(htmlspecialchars($item['uraian'])) ?></td>
                <td class="report-uploader"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($item['nama_input'] ?: '-') ?></td>
                <td><?php if ($fotoUraian): ?><div class="d-flex flex-wrap gap-2"><?php foreach ($fotoUraian as $foto): ?><a href="../../<?= htmlspecialchars($foto['path_file']) ?>" target="_blank"><img src="../../<?= htmlspecialchars($foto['path_file']) ?>" alt="Lampiran kegiatan" class="rounded report-photo"></a><?php endforeach; ?></div><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <input class="mobile-table-scrollbar" type="range" min="0" value="0" aria-label="Geser tabel uraian kegiatan ke samping">
      <p class="mobile-scroll-hint mb-0 px-3"><i class="bi bi-arrow-left-right me-1"></i>Geser tabel ke samping untuk melihat semua kolom.</p>
    </section>

    <section class="card shadow-sm border-0 mb-4 report-detail-table-card">
      <div class="card-header bg-primary text-white fw-semibold">Inventaris</div>
      <div class="table-responsive mobile-scroll-table admin-detail-table" tabindex="0" aria-label="Tabel inventaris, geser ke samping untuk melihat semua kolom">
        <table class="table table-bordered align-middle mb-0 report-detail-table">
          <thead><tr><th>No.</th><th>Waktu Input</th><th>Nama Barang</th><th>Jumlah</th><th>Keterangan</th><th>Pengunggah</th><th>Lampiran Foto</th></tr></thead>
          <tbody>
            <?php if (!$inventaris): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada data inventaris.</td></tr><?php endif; ?>
            <?php foreach ($inventaris as $index => $item): $fotoInventaris = $lampiranPerInventaris[(int) $item['id_inventaris']] ?? []; ?><tr><td class="report-number"><?= $index + 1 ?></td><td><?= date('d-m-Y', strtotime($item['created_at'])) ?><br><small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($item['created_at'])) ?> WIB</small></td><td><?= htmlspecialchars($item['nama_barang']) ?></td><td><?= (int) $item['jumlah'] ?></td><td><?= htmlspecialchars($item['keterangan']) ?></td><td class="report-uploader"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($item['nama_input'] ?: '-') ?></td><td><?php if ($fotoInventaris): ?><div class="d-flex flex-wrap gap-2"><?php foreach ($fotoInventaris as $foto): ?><a href="../../<?= htmlspecialchars($foto['path_file']) ?>" target="_blank"><img src="../../<?= htmlspecialchars($foto['path_file']) ?>" alt="Lampiran <?= htmlspecialchars($item['nama_barang']) ?>" class="rounded report-photo"></a><?php endforeach; ?></div><?php else: ?><span class="text-muted">-</span><?php endif; ?></td></tr><?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <input class="mobile-table-scrollbar" type="range" min="0" value="0" aria-label="Geser tabel inventaris ke samping">
      <p class="mobile-scroll-hint mb-0 px-3"><i class="bi bi-arrow-left-right me-1"></i>Geser tabel ke samping untuk melihat semua kolom.</p>
    </section>
  </div>
</main>

<?php include '../../includes/footer.php'; ?>
