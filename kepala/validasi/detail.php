<?php
require_once "../../config/kepala_auth.php";
$title = 'Detail Laporan';
$pageTitle = 'DETAIL LAPORAN';
$base_url = '../../';
$activeMenu = 'laporan';
$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT l.*,u.nama,u.kode_satpam,s.nama_shift,s.jam_mulai,s.jam_selesai FROM laporan l JOIN users u ON u.id_user=l.created_by JOIN jadwal_shift js ON js.id_jadwal=l.id_jadwal JOIN shift s ON s.id_shift=js.id_shift WHERE l.id_laporan=? AND l.status='menunggu_validasi' LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$laporan) {
  header('Location: index.php');
  exit;
}
$inventaris = mysqli_query($conn, "SELECT * FROM inventaris WHERE id_laporan=$id ORDER BY urutan");
$uraian = mysqli_query($conn, "SELECT * FROM uraian_kegiatan WHERE id_laporan=$id ORDER BY urutan");
$kepala = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ttd FROM users WHERE id_user=" . (int)$_SESSION['id_user'] . " LIMIT 1"));
$siapValidasi = !empty($kepala['ttd']);
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
      </div><span class="report-status report-status-pending"><i class="bi bi-clock-history me-1"></i>Menunggu Validasi</span>
    </div>
    <?php if (!$siapValidasi): ?><div class="alert alert-warning border-0 shadow-sm">Tanda tangan Kepala belum tersedia. <a href="../profil_ttd.php" class="alert-link">Unggah tanda tangan</a> sebelum memvalidasi laporan.</div><?php endif; ?>
    <div class="inventaris-card mb-4">
      <div class="card-body">
        <h2 class="inventaris-heading">Informasi Laporan</h2>
        <div class="row g-3">
          <div class="col-md-4">
            <div class="detail-label">Tanggal Laporan</div>
            <div class="detail-value"><?= date('d F Y', strtotime($laporan['tanggal_laporan'])) ?></div>
          </div>
          <div class="col-md-4">
            <div class="detail-label">Dibuat Oleh</div>
            <div class="detail-value"><?= htmlspecialchars($laporan['nama']) ?></div><small class="text-muted"><?= htmlspecialchars($laporan['kode_satpam']) ?></small>
          </div>
          <div class="col-md-4">
            <div class="detail-label">Shift</div>
            <div class="detail-value"><?= htmlspecialchars($laporan['nama_shift']) ?></div><small class="text-muted"><?= substr($laporan['jam_mulai'], 0, 5) ?> - <?= substr($laporan['jam_selesai'], 0, 5) ?> WIB</small>
          </div>
        </div>
      </div>
    </div>
    <div class="inventaris-card mb-4">
      <div class="card-body">
        <h2 class="inventaris-heading">Daftar Inventaris</h2>
        <div class="inventory-table table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>No.</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody><?php if (mysqli_num_rows($inventaris)): while ($item = mysqli_fetch_assoc($inventaris)): ?><tr>
                    <td><?= (int)$item['urutan'] ?></td>
                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td><?= (int)$item['jumlah'] ?></td>
                    <td><?= htmlspecialchars($item['keterangan']) ?></td>
                  </tr><?php endwhile;
                    else: ?><tr>
                  <td colspan="4" class="text-center text-muted py-4">Tidak ada data inventaris.</td>
                </tr><?php endif; ?></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="inventaris-card mb-4">
      <div class="card-body">
        <h2 class="inventaris-heading">Uraian Kegiatan</h2>
        <div class="inventory-table table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>No.</th>
                <th>Waktu</th>
                <th>Uraian Kegiatan</th>
              </tr>
            </thead>
            <tbody><?php if (mysqli_num_rows($uraian)): while ($item = mysqli_fetch_assoc($uraian)): ?><tr>
                    <td><?= (int)$item['urutan'] ?></td>
                    <td><?= substr($item['jam'], 0, 5) ?> WIB</td>
                    <td><?= nl2br(htmlspecialchars($item['uraian'])) ?></td>
                  </tr><?php endwhile;
                    else: ?><tr>
                  <td colspan="3" class="text-center text-muted py-4">Tidak ada uraian kegiatan.</td>
                </tr><?php endif; ?></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="d-flex flex-wrap justify-content-end gap-2"><a href="index.php" class="btn btn-inventaris-outline">Kembali</a><?php if ($siapValidasi): ?><form method="post" action="validasi.php" onsubmit="return confirm('Validasi laporan ini? Tanda tangan Kepala akan disimpan pada laporan dan laporan tidak dapat diubah kembali.');"><input type="hidden" name="id" value="<?= $id ?>"><button class="btn btn-success"><i class="bi bi-check2-circle me-2"></i>Validasi Laporan</button></form><?php else: ?><a href="../profil_ttd.php" class="btn btn-warning"><i class="bi bi-pen me-2"></i>Atur Tanda Tangan</a><?php endif; ?></div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>