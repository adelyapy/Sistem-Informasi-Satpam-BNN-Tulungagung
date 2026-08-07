<?php
require_once '../../config/satpam_auth.php';

date_default_timezone_set('Asia/Jakarta');

$idUser = (int) ($_SESSION['id_user'] ?? 0);
$namaSatpam = $_SESSION['nama'] ?? 'Satpam';
$idLaporan = (int) ($_GET['id'] ?? $_SESSION['id_laporan'] ?? 0);

if ($idLaporan < 1) {
  header('Location: index.php');
  exit;
}

$laporanStmt = mysqli_prepare($conn, '
    SELECT l.id_laporan, l.status, j.tanggal, s.nama_shift, s.jam_mulai, s.jam_selesai
    FROM laporan l
    INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
    INNER JOIN jadwal_shift j ON j.id_jadwal = l.id_jadwal
    INNER JOIN shift s ON s.id_shift = j.id_shift
    WHERE l.id_laporan = ? AND a.id_satpam = ?
    LIMIT 1
');
mysqli_stmt_bind_param($laporanStmt, 'ii', $idLaporan, $idUser);
mysqli_stmt_execute($laporanStmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt));

if (!$laporan) {
  header('Location: index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $laporan['status'] === 'draft') {
  $action = $_POST['action'] ?? '';

  if ($action === 'tambah') {
    $uraian = trim($_POST['uraian'] ?? '');
    if ($uraian !== '' && mb_strlen($uraian) <= 500) {
      $urutanResult = mysqli_query($conn, "SELECT COALESCE(MAX(urutan), 0) + 1 AS urutan FROM uraian_kegiatan WHERE id_laporan = {$idLaporan}");
      $urutan = (int) mysqli_fetch_assoc($urutanResult)['urutan'];
      $jam = date('H:i:s');
      $stmt = mysqli_prepare($conn, 'INSERT INTO uraian_kegiatan (id_laporan, created_by, urutan, jam, uraian) VALUES (?, ?, ?, ?, ?)');
      mysqli_stmt_bind_param($stmt, 'iiiss', $idLaporan, $idUser, $urutan, $jam, $uraian);
      mysqli_stmt_execute($stmt);
      mysqli_query($conn, "UPDATE laporan SET uraian_selesai = 1 WHERE id_laporan = {$idLaporan}");
    }
  }

  if ($action === 'edit') {
    $idUraian = (int) ($_POST['id_uraian'] ?? 0);
    $uraian = trim($_POST['uraian'] ?? '');
    if ($idUraian > 0 && $uraian !== '' && mb_strlen($uraian) <= 500) {
      $stmt = mysqli_prepare(
        $conn,
        'UPDATE uraian_kegiatan
     SET uraian = ?
     WHERE id_uraian = ? AND id_laporan = ?'
      );

      mysqli_stmt_bind_param(
        $stmt,
        'sii',
        $uraian,
        $idUraian,
        $idLaporan
      );

      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_param($stmt, 'sii', $uraian, $idUraian, $idLaporan);
      mysqli_stmt_execute($stmt);
    }
  }

  if ($action === 'hapus') {
    $idUraian = (int) ($_POST['id_uraian'] ?? 0);
    $stmt = mysqli_prepare($conn, 'DELETE FROM uraian_kegiatan WHERE id_uraian = ? AND id_laporan = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $idUraian, $idLaporan);
    mysqli_stmt_execute($stmt);
    $total = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM uraian_kegiatan WHERE id_laporan = {$idLaporan}"))['total'];
    if ($total === 0) {
      mysqli_query($conn, "UPDATE laporan SET uraian_selesai = 0 WHERE id_laporan = {$idLaporan}");
    }
  }

  header("Location: uraian.php?id={$idLaporan}");
  exit;
}

$dataStmt = mysqli_prepare($conn, '
    SELECT id_uraian, urutan, jam, uraian, created_at
    FROM uraian_kegiatan
    WHERE id_laporan = ?
    ORDER BY urutan ASC, id_uraian ASC
');
mysqli_stmt_bind_param($dataStmt, 'i', $idLaporan);
mysqli_stmt_execute($dataStmt);
$uraianKegiatan = mysqli_fetch_all(mysqli_stmt_get_result($dataStmt), MYSQLI_ASSOC);

$bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggal = new DateTime($laporan['tanggal']);
$tanggalTampil = $tanggal->format('d') . ' ' . $bulan[(int) $tanggal->format('n')] . ' ' . $tanggal->format('Y');

$title = 'Input Uraian Kegiatan';
$pageTitle = 'Input Uraian Kegiatan';
$base_url = '../../';
$activeMenu = 'uraian';
include '../../includes/header.php';
?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">

<?php include '../../includes/satpam_navbar.php'; ?>
<?php include '../../includes/satpam_sidebar.php'; ?>

<main class="main-content">
  <div class="inventaris-page">
    <section class="inventaris-card mb-3">
      <div class="card-body">
        <h2 class="inventaris-heading">Form Uraian Kegiatan</h2>
        <?php if ($laporan['status'] === 'draft') { ?>
          <form method="post">
            <input type="hidden" name="action" value="tambah">
            <div class="row g-3 mb-3">
              <div class="col-lg-4"><label class="form-label">Nama Satpam</label>
                <div class="form-control activity-info"><i class="bi bi-person text-primary me-2"></i><?= htmlspecialchars($namaSatpam) ?></div>
              </div>
              <div class="col-lg-4"><label class="form-label">Tanggal &amp; Waktu Input</label>
                <div class="form-control activity-info"><i class="bi bi-calendar3 text-primary me-2"></i><?= $tanggalTampil ?> <span class="ms-1 text-muted">• <?= date('H:i') ?> WIB</span></div>
              </div>
              <div class="col-lg-4"><label class="form-label">Shift</label>
                <div class="form-control activity-info"><i class="bi bi-clock text-primary me-2"></i><?= htmlspecialchars($laporan['nama_shift']) ?> <span class="ms-1 text-muted">(<?= substr($laporan['jam_mulai'], 0, 5) ?>–<?= substr($laporan['jam_selesai'], 0, 5) ?>)</span></div>
              </div>
            </div>
            <label class="form-label" for="uraian">Uraian Kegiatan</label>
            <textarea class="form-control activity-textarea" id="uraian" name="uraian" maxlength="500" placeholder="Tuliskan uraian kegiatan yang dilakukan..." required></textarea>
            <div class="d-flex justify-content-between align-items-center mt-1"><small class="text-muted">Waktu dicatat otomatis saat kegiatan ditambahkan.</small><small class="text-muted"><span id="jumlahKarakter">0</span> / 500</small></div>
            <div class="text-end mt-3"><button class="btn btn-inventaris-primary" type="submit"><i class="bi bi-plus-lg me-2"></i>Tambah Kegiatan</button></div>
          </form>
        <?php } else { ?>
          <div class="alert alert-success mb-0">Laporan telah dikirim sehingga uraian kegiatan tidak dapat diubah.</div>
        <?php } ?>
      </div>
    </section>

    <section class="inventaris-card">
      <div class="card-body">
        <h2 class="inventaris-heading">Daftar Uraian Kegiatan</h2>
        <div class="inventory-table table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>No.</th>
                <th>Tanggal &amp; Waktu</th>
                <th>Shift</th>
                <th>Uraian Kegiatan</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$uraianKegiatan) { ?><tr>
                  <td colspan="5" class="text-center text-muted py-5">Belum ada uraian kegiatan yang ditambahkan.</td>
                </tr><?php } ?>
              <?php foreach ($uraianKegiatan as $index => $row) { ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td>
                    <div><?= $tanggalTampil ?></div><small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars(substr($row['jam'], 0, 5)) ?> WIB</small>
                  </td>
                  <td><?= htmlspecialchars($laporan['nama_shift']) ?></td>
                  <td class="activity-description"><?= nl2br(htmlspecialchars($row['uraian'])) ?></td>
                  <td class="text-center inventory-actions">
                    <?php if ($laporan['status'] === 'draft') { ?>
                      <button class="btn btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#edit<?= (int) $row['id_uraian'] ?>" aria-label="Edit uraian"><i class="bi bi-pencil-square"></i></button>
                      <form method="post" class="d-inline" onsubmit="return confirm('Hapus uraian kegiatan ini?')"><input type="hidden" name="action" value="hapus"><input type="hidden" name="id_uraian" value="<?= (int) $row['id_uraian'] ?>"><button class="btn btn-delete" type="submit" aria-label="Hapus uraian"><i class="bi bi-trash3"></i></button></form>
                    <?php } else { ?><span class="text-muted">-</span><?php } ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a class="btn btn-light btn-inventaris-outline" href="../dashboard.php"><i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard</a>
      <a class="btn btn-inventaris-primary" href="index.php"><i class="bi bi-floppy me-2"></i>Simpan</a>
      <a class="btn btn-inventaris-primary" href="detail.php?id=<?= $idLaporan ?>"><i class="bi bi-file-earmark-text me-2"></i>Lihat Laporan Uraian Kegiatan</a>
    </div>
  </div>
</main>

<?php if ($laporan['status'] === 'draft') {
  foreach ($uraianKegiatan as $row) { ?>
    <div class="modal fade" id="edit<?= (int) $row['id_uraian'] ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title">Edit Uraian Kegiatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="id_uraian" value="<?= (int) $row['id_uraian'] ?>"><label class="form-label">Uraian Kegiatan</label><textarea class="form-control activity-textarea" name="uraian" maxlength="500" required><?= htmlspecialchars($row['uraian']) ?></textarea></div>
            <div class="modal-footer"><button class="btn btn-inventaris-primary" type="submit">Simpan Perubahan</button></div>
          </form>
        </div>
      </div>
    </div>
<?php }
} ?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const uraian = document.getElementById('uraian');
    const jumlahKarakter = document.getElementById('jumlahKarakter');
    if (uraian && jumlahKarakter) {
      const updateJumlah = () => jumlahKarakter.textContent = uraian.value.length;
      uraian.addEventListener('input', updateJumlah);
      updateJumlah();
    }
  });
</script>
<?php include '../../includes/footer.php'; ?>