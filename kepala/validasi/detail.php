<?php
require_once '../../config/kepala_auth.php';
require_once '../../config/report_attachment.php';

$title = 'Detail Laporan';
$pageTitle = 'DETAIL LAPORAN';
$base_url = '../../';
$activeMenu = 'laporan';
$id = (int) ($_GET['id'] ?? 0);

if (!ensureLampiranFotoTable($conn)) {
    exit('Tabel lampiran foto tidak dapat disiapkan.');
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT
        l.*,
        u.nama AS nama_pembuat,
        u.kode_satpam,
        s.nama_shift,
        s.jam_mulai,
        s.jam_selesai
     FROM laporan l
     JOIN users u ON u.id_user = l.created_by
     JOIN jadwal_shift js ON js.id_jadwal = l.id_jadwal
     JOIN shift s ON s.id_shift = js.id_shift
     WHERE l.id_laporan = ? AND l.status IN (\'menunggu_validasi\', \'tervalidasi\')
     LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$laporan) {
    header('Location: index.php');
    exit;
}

$anggotaStmt = mysqli_prepare(
    $conn,
    'SELECT u.nama, u.kode_satpam
     FROM anggota_shift anggota
     JOIN users u ON u.id_user = anggota.id_satpam
     WHERE anggota.id_laporan = ?
     ORDER BY u.nama ASC'
);
mysqli_stmt_bind_param($anggotaStmt, 'i', $id);
mysqli_stmt_execute($anggotaStmt);
$anggotaShift = mysqli_fetch_all(mysqli_stmt_get_result($anggotaStmt), MYSQLI_ASSOC);

$inventarisStmt = mysqli_prepare(
    $conn,
    'SELECT i.*, u.nama AS nama_pengunggah
     FROM inventaris i
     LEFT JOIN users u ON u.id_user = i.created_by
     WHERE i.id_laporan = ?
     ORDER BY i.urutan ASC, i.id_inventaris ASC'
);
mysqli_stmt_bind_param($inventarisStmt, 'i', $id);
mysqli_stmt_execute($inventarisStmt);
$inventaris = mysqli_fetch_all(mysqli_stmt_get_result($inventarisStmt), MYSQLI_ASSOC);

$uraianStmt = mysqli_prepare(
    $conn,
    'SELECT uk.*, u.nama AS nama_pengunggah
     FROM uraian_kegiatan uk
     LEFT JOIN users u ON u.id_user = uk.created_by
     WHERE uk.id_laporan = ?
     ORDER BY uk.urutan ASC, uk.id_uraian ASC'
);
mysqli_stmt_bind_param($uraianStmt, 'i', $id);
mysqli_stmt_execute($uraianStmt);
$uraian = mysqli_fetch_all(mysqli_stmt_get_result($uraianStmt), MYSQLI_ASSOC);

$lampiranStmt = mysqli_prepare(
    $conn,
    'SELECT id_inventaris, id_uraian, path_file, nama_file
     FROM lampiran_foto
     WHERE id_laporan = ?
     ORDER BY id_lampiran ASC'
);
mysqli_stmt_bind_param($lampiranStmt, 'i', $id);
mysqli_stmt_execute($lampiranStmt);
$lampiranRows = mysqli_fetch_all(mysqli_stmt_get_result($lampiranStmt), MYSQLI_ASSOC);
$lampiranInventaris = [];
$lampiranUraian = [];

foreach ($lampiranRows as $lampiran) {
    if (!empty($lampiran['id_inventaris'])) {
        $lampiranInventaris[(int) $lampiran['id_inventaris']][] = $lampiran;
    }

    if (!empty($lampiran['id_uraian'])) {
        $lampiranUraian[(int) $lampiran['id_uraian']][] = $lampiran;
    }
}

$kepalaStmt = mysqli_prepare($conn, 'SELECT ttd FROM users WHERE id_user = ? LIMIT 1');
$kepalaId = (int) $_SESSION['id_user'];
mysqli_stmt_bind_param($kepalaStmt, 'i', $kepalaId);
mysqli_stmt_execute($kepalaStmt);
$kepala = mysqli_fetch_assoc(mysqli_stmt_get_result($kepalaStmt));
$siapValidasi = !empty($kepala['ttd']);
$menungguValidasi = $laporan['status'] === 'menunggu_validasi';

include '../../includes/header.php';
include '../../includes/kepala_navbar.php';
include '../../includes/kepala_sidebar.php';
?>

<main class="main-content">
  <div class="inventaris-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="detail-title">Detail Laporan</h1>
        <p class="text-muted mb-0">Periksa seluruh isi laporan sebelum melakukan validasi.</p>
      </div>
      <?php if ($menungguValidasi): ?>
        <span class="report-status report-status-pending"><i class="bi bi-clock-history me-1"></i>Menunggu Validasi</span>
      <?php else: ?>
        <span class="report-status report-status-validated"><i class="bi bi-check2-circle me-1"></i>Tervalidasi</span>
      <?php endif; ?>
    </div>

    <?php if ($menungguValidasi && !$siapValidasi): ?>
      <div class="alert alert-warning border-0 shadow-sm">
        Tanda tangan Kepala belum tersedia. <a href="../profil_ttd.php" class="alert-link">Unggah tanda tangan</a> sebelum memvalidasi laporan.
      </div>
    <?php endif; ?>

    <div class="inventaris-card mb-4">
      <div class="card-body">
        <h2 class="inventaris-heading">Informasi Laporan</h2>
        <div class="row g-3">
          <div class="col-md-3">
            <div class="detail-label">Tanggal Laporan</div>
            <div class="detail-value"><?= date('d F Y', strtotime($laporan['tanggal_laporan'])) ?></div>
          </div>
          <div class="col-md-3">
            <div class="detail-label">Dibuat Oleh</div>
            <div class="detail-value"><?= htmlspecialchars($laporan['nama_pembuat']) ?></div>
            <small class="text-muted"><?= htmlspecialchars($laporan['kode_satpam']) ?></small>
          </div>
          <div class="col-md-3">
            <div class="detail-label">Shift</div>
            <div class="detail-value"><?= htmlspecialchars($laporan['nama_shift']) ?></div>
            <small class="text-muted"><?= substr($laporan['jam_mulai'], 0, 5) ?> - <?= substr($laporan['jam_selesai'], 0, 5) ?> WIB</small>
          </div>
          <div class="col-md-3">
            <div class="detail-label">Petugas Shift</div>
            <div class="detail-value">
              <?php if ($anggotaShift): ?>
                <?php foreach ($anggotaShift as $anggota): ?>
                  <div><?= htmlspecialchars($anggota['nama']) ?> <small class="text-muted">(<?= htmlspecialchars($anggota['kode_satpam']) ?>)</small></div>
                <?php endforeach; ?>
              <?php else: ?>
                <div><?= htmlspecialchars($laporan['nama_pembuat']) ?> <small class="text-muted">(<?= htmlspecialchars($laporan['kode_satpam']) ?>)</small></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="inventaris-card mb-4">
      <div class="card-body">
        <h2 class="inventaris-heading">Daftar Inventaris</h2>
        <div class="inventory-table table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>No.</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Diinput Oleh</th>
                <th>Lampiran Foto</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($inventaris): ?>
                <?php foreach ($inventaris as $index => $item): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td><?= (int) $item['jumlah'] ?></td>
                    <td><?= htmlspecialchars($item['keterangan']) ?></td>
                    <td><?= htmlspecialchars($item['nama_pengunggah'] ?: $laporan['nama_pembuat']) ?></td>
                    <td>
                      <?php $fotoItems = $lampiranInventaris[(int) $item['id_inventaris']] ?? []; ?>
                      <?php if ($fotoItems): ?>
                        <div class="d-flex flex-wrap gap-2">
                          <?php foreach ($fotoItems as $foto): ?>
                            <a href="<?= $base_url . htmlspecialchars($foto['path_file']) ?>" target="_blank" title="<?= htmlspecialchars($foto['nama_file']) ?>">
                              <img src="<?= $base_url . htmlspecialchars($foto['path_file']) ?>" alt="Lampiran inventaris" class="img-thumbnail" style="width: 56px; height: 56px; object-fit: cover;">
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">Tidak ada data inventaris.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="inventaris-card mb-4">
      <div class="card-body">
        <h2 class="inventaris-heading">Uraian Kegiatan</h2>
        <div class="inventory-table table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>No.</th>
                <th>Waktu</th>
                <th>Uraian Kegiatan</th>
                <th>Diinput Oleh</th>
                <th>Lampiran Foto</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($uraian): ?>
                <?php foreach ($uraian as $index => $item): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= substr($item['jam'], 0, 5) ?> WIB</td>
                    <td><?= nl2br(htmlspecialchars($item['uraian'])) ?></td>
                    <td><?= htmlspecialchars($item['nama_pengunggah'] ?: $laporan['nama_pembuat']) ?></td>
                    <td>
                      <?php $fotoItems = $lampiranUraian[(int) $item['id_uraian']] ?? []; ?>
                      <?php if ($fotoItems): ?>
                        <div class="d-flex flex-wrap gap-2">
                          <?php foreach ($fotoItems as $foto): ?>
                            <a href="<?= $base_url . htmlspecialchars($foto['path_file']) ?>" target="_blank" title="<?= htmlspecialchars($foto['nama_file']) ?>">
                              <img src="<?= $base_url . htmlspecialchars($foto['path_file']) ?>" alt="Lampiran uraian kegiatan" class="img-thumbnail" style="width: 56px; height: 56px; object-fit: cover;">
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">Tidak ada uraian kegiatan.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="d-flex flex-wrap justify-content-end gap-2">
      <a href="index.php" class="btn btn-inventaris-outline">Kembali</a>
      <?php if ($menungguValidasi && $siapValidasi): ?>
        <form method="post" action="validasi.php" onsubmit="return confirm('Validasi laporan ini? Tanda tangan Kepala akan disimpan pada laporan dan laporan tidak dapat diubah kembali.');">
          <?= csrf_input() ?>
          <input type="hidden" name="id" value="<?= $id ?>">
          <input type="hidden" name="action" value="validasi">
          <button class="btn btn-success"><i class="bi bi-check2-circle me-2"></i>Validasi Laporan</button>
        </form>
      <?php elseif ($menungguValidasi): ?>
        <a href="../profil_ttd.php" class="btn btn-warning"><i class="bi bi-pen me-2"></i>Atur Tanda Tangan</a>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include '../../includes/footer.php'; ?>
