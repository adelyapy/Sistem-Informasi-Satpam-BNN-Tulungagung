<?php
require_once '../../config/satpam_auth.php';
require_once '../../config/report_attachment.php';

date_default_timezone_set('Asia/Jakarta');

if (!ensureLampiranFotoTable($conn) || !ensureUraianDraftColumn($conn) || !ensureStatusRekapColumns($conn)) {
  exit('Tabel lampiran foto tidak dapat disiapkan.');
}

$idUser = (int) ($_SESSION['id_user'] ?? 0);
$namaSatpam = $_SESSION['nama'] ?? 'Satpam';
$idLaporan = (int) ($_GET['id'] ?? $_SESSION['id_laporan'] ?? 0);

if ($idLaporan < 1) {
  header('Location: index.php');
  exit;
}

$laporanStmt = mysqli_prepare($conn, '
    SELECT l.id_laporan, l.status, l.uraian_draft_disimpan, j.tanggal, s.nama_shift, s.jam_mulai, s.jam_selesai
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

$error = '';
$uraianTersimpan = (int) $laporan['uraian_draft_disimpan'] === 1;
$bolehUbahUraian = $laporan['status'] === 'draft';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'simpan_draft') {
    $total = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM uraian_kegiatan WHERE id_laporan = {$idLaporan} AND sudah_direkap = 0"))['total'];
    if ($laporan['status'] !== 'draft') {
      $error = 'Laporan yang telah dikirim tidak dapat diubah.';
    } elseif ($total < 1) {
      $error = 'Tambahkan minimal satu uraian kegiatan sebelum menyimpan draft.';
    } else {
      $simpanDraft = mysqli_prepare($conn, 'UPDATE uraian_kegiatan SET sudah_direkap = 1 WHERE id_laporan = ? AND sudah_direkap = 0');
      mysqli_stmt_bind_param($simpanDraft, 'i', $idLaporan);
      if (mysqli_stmt_execute($simpanDraft)) {
        mysqli_query($conn, "UPDATE laporan SET uraian_selesai = 1, uraian_draft_disimpan = 1, updated_at = NOW() WHERE id_laporan = {$idLaporan} AND status = 'draft'");
        logActivity($conn, 'Edit data', 'uraian_kegiatan', $idLaporan);
        header("Location: detail.php?id={$idLaporan}");
        exit;
      }
      $error = 'Draft uraian kegiatan tidak dapat disimpan.';
    }
  } elseif (!$bolehUbahUraian) {
    $error = 'Laporan telah dikirim sehingga uraian kegiatan tidak dapat diubah.';
  } elseif ($action === 'tambah') {
    $uraian = trim($_POST['uraian'] ?? '');
    $files = $_FILES['lampiran_foto'] ?? null;
    $jumlahFoto = is_array($files['name'] ?? null) ? count(array_filter($files['name'], static fn($nama) => $nama !== '')) : 0;

    if ($uraian === '' || mb_strlen($uraian) > 500) {
      $error = 'Uraian kegiatan wajib diisi dan maksimal 500 karakter.';
    } elseif ($jumlahFoto > 5) {
      $error = 'Maksimal 5 foto lampiran untuk setiap uraian kegiatan.';
    } else {
      $fileFoto = [];
      if ($jumlahFoto > 0) {
        foreach ($files['name'] as $index => $namaFile) {
          if ($namaFile === '') {
            continue;
          }
          $fileFoto[] = [
            'name' => $namaFile,
            'tmp_name' => $files['tmp_name'][$index],
            'error' => $files['error'][$index],
            'size' => $files['size'][$index],
          ];
        }
      }

      $pathTerunggah = [];
      try {
        mysqli_begin_transaction($conn);
        $urutanResult = mysqli_query($conn, "SELECT COALESCE(MAX(urutan), 0) + 1 AS urutan FROM uraian_kegiatan WHERE id_laporan = {$idLaporan}");
        $urutan = (int) mysqli_fetch_assoc($urutanResult)['urutan'];
        $jam = date('H:i:s');
        $stmt = mysqli_prepare($conn, 'INSERT INTO uraian_kegiatan (id_laporan, created_by, urutan, jam, uraian) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'iiiss', $idLaporan, $idUser, $urutan, $jam, $uraian);
        if (!mysqli_stmt_execute($stmt)) {
          throw new RuntimeException('Uraian kegiatan gagal disimpan.');
        }
        $idUraian = (int) mysqli_insert_id($conn);

        foreach ($fileFoto as $file) {
          $upload = uploadLampiranFoto($file);
          if (!$upload['ok']) {
            throw new RuntimeException($upload['message']);
          }
          $pathTerunggah[] = $upload['path_file'];
          $simpanFoto = mysqli_prepare($conn, 'INSERT INTO lampiran_foto (id_laporan, id_uraian, nama_file, path_file, ukuran_file, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)');
          mysqli_stmt_bind_param($simpanFoto, 'iissii', $idLaporan, $idUraian, $upload['nama_file'], $upload['path_file'], $upload['ukuran_file'], $idUser);
          if (!mysqli_stmt_execute($simpanFoto)) {
            throw new RuntimeException('Data lampiran foto gagal disimpan.');
          }
        }

        mysqli_commit($conn);
      } catch (Throwable $exception) {
        mysqli_rollback($conn);
        foreach ($pathTerunggah as $path) {
          @unlink(dirname(__DIR__, 2) . '/' . $path);
        }
        $error = $exception->getMessage();
      }
    }
  }

  if ($bolehUbahUraian && $action === 'edit') {
    $idUraian = (int) ($_POST['id_uraian'] ?? 0);
    $uraian = trim($_POST['uraian'] ?? '');
    $hapusFotoInput = $_POST['hapus_foto'] ?? [];
    if (!is_array($hapusFotoInput)) {
      $hapusFotoInput = [];
    }
    $hapusFoto = array_values(array_unique(array_filter(array_map('intval', $hapusFotoInput), static fn($id) => $id > 0)));
    $files = $_FILES['lampiran_foto'] ?? null;
    $jumlahFotoBaru = is_array($files['name'] ?? null) ? count(array_filter($files['name'], static fn($nama) => $nama !== '')) : 0;

    if ($idUraian < 1 || $uraian === '' || mb_strlen($uraian) > 500) {
      $error = 'Data perubahan uraian kegiatan tidak valid.';
    } else {
      $fotoLamaStmt = mysqli_prepare($conn, 'SELECT id_lampiran, path_file FROM lampiran_foto WHERE id_laporan = ? AND id_uraian = ?');
      mysqli_stmt_bind_param($fotoLamaStmt, 'ii', $idLaporan, $idUraian);
      mysqli_stmt_execute($fotoLamaStmt);
      $fotoLama = mysqli_fetch_all(mysqli_stmt_get_result($fotoLamaStmt), MYSQLI_ASSOC);
      $fotoYangDihapus = array_values(array_filter($fotoLama, static fn($foto) => in_array((int) $foto['id_lampiran'], $hapusFoto, true)));

      if (count($fotoLama) - count($fotoYangDihapus) + $jumlahFotoBaru > 5) {
        $error = 'Maksimal 5 foto untuk setiap uraian kegiatan setelah perubahan.';
      } else {
        $fileFoto = [];
        if ($jumlahFotoBaru > 0) {
          foreach ($files['name'] as $index => $namaFile) {
            if ($namaFile === '') {
              continue;
            }
            $fileFoto[] = [
              'name' => $namaFile,
              'tmp_name' => $files['tmp_name'][$index],
              'error' => $files['error'][$index],
              'size' => $files['size'][$index],
            ];
          }
        }

        $pathFotoBaru = [];
        try {
          mysqli_begin_transaction($conn);
          $ubah = mysqli_prepare($conn, 'UPDATE uraian_kegiatan SET uraian = ? WHERE id_uraian = ? AND id_laporan = ?');
          mysqli_stmt_bind_param($ubah, 'sii', $uraian, $idUraian, $idLaporan);
          if (!mysqli_stmt_execute($ubah)) {
            throw new RuntimeException('Uraian kegiatan tidak dapat diperbarui.');
          }

          foreach ($fotoYangDihapus as $foto) {
            $hapusFotoStmt = mysqli_prepare($conn, 'DELETE FROM lampiran_foto WHERE id_lampiran = ? AND id_laporan = ? AND id_uraian = ?');
            $idLampiran = (int) $foto['id_lampiran'];
            mysqli_stmt_bind_param($hapusFotoStmt, 'iii', $idLampiran, $idLaporan, $idUraian);
            if (!mysqli_stmt_execute($hapusFotoStmt)) {
              throw new RuntimeException('Foto lama tidak dapat dihapus.');
            }
          }

          foreach ($fileFoto as $file) {
            $upload = uploadLampiranFoto($file);
            if (!$upload['ok']) {
              throw new RuntimeException($upload['message']);
            }
            $pathFotoBaru[] = $upload['path_file'];
            $simpanFoto = mysqli_prepare($conn, 'INSERT INTO lampiran_foto (id_laporan, id_uraian, nama_file, path_file, ukuran_file, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($simpanFoto, 'iissii', $idLaporan, $idUraian, $upload['nama_file'], $upload['path_file'], $upload['ukuran_file'], $idUser);
            if (!mysqli_stmt_execute($simpanFoto)) {
              throw new RuntimeException('Foto baru tidak dapat disimpan.');
            }
          }

          mysqli_commit($conn);
          foreach ($fotoYangDihapus as $foto) {
            $path = dirname(__DIR__, 2) . '/' . $foto['path_file'];
            if (is_file($path)) {
              @unlink($path);
            }
          }
        } catch (Throwable $exception) {
          mysqli_rollback($conn);
          foreach ($pathFotoBaru as $path) {
            @unlink(dirname(__DIR__, 2) . '/' . $path);
          }
          $error = $exception->getMessage();
        }
      }
    }
  }

  if ($bolehUbahUraian && $action === 'hapus') {
    $idUraian = (int) ($_POST['id_uraian'] ?? 0);
    hapusLampiranUraian($conn, $idLaporan, $idUraian);
    $stmt = mysqli_prepare($conn, 'DELETE FROM uraian_kegiatan WHERE id_uraian = ? AND id_laporan = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $idUraian, $idLaporan);
    mysqli_stmt_execute($stmt);
    $total = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM uraian_kegiatan WHERE id_laporan = {$idLaporan}"))['total'];
    if ($total === 0) {
      mysqli_query($conn, "UPDATE laporan SET uraian_selesai = 0 WHERE id_laporan = {$idLaporan}");
    }
  }

  if ($error === '') {
    $aktivitas = ['tambah' => 'Tambah data', 'edit' => 'Edit data', 'hapus' => 'Hapus data', 'simpan_draft' => 'Edit data'][$action] ?? null;
    if ($aktivitas !== null) {
      logActivity($conn, $aktivitas, 'uraian_kegiatan', $idLaporan);
    }
    header("Location: uraian.php?id={$idLaporan}");
    exit;
  }
}

$dataStmt = mysqli_prepare($conn, '
    SELECT id_uraian, urutan, jam, uraian, created_at
    FROM uraian_kegiatan
    WHERE id_laporan = ? AND sudah_direkap = 0
    ORDER BY urutan ASC, id_uraian ASC
');
mysqli_stmt_bind_param($dataStmt, 'i', $idLaporan);
mysqli_stmt_execute($dataStmt);
$uraianKegiatan = mysqli_fetch_all(mysqli_stmt_get_result($dataStmt), MYSQLI_ASSOC);

$fotoStmt = mysqli_prepare($conn, 'SELECT id_lampiran, id_uraian, path_file, nama_file FROM lampiran_foto WHERE id_laporan = ? ORDER BY created_at ASC, id_lampiran ASC');
mysqli_stmt_bind_param($fotoStmt, 'i', $idLaporan);
mysqli_stmt_execute($fotoStmt);
$lampiranPerUraian = [];
$fotoResult = mysqli_stmt_get_result($fotoStmt);
while ($foto = mysqli_fetch_assoc($fotoResult)) {
  $lampiranPerUraian[(int) $foto['id_uraian']][] = $foto;
}

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
        <?php if ($bolehUbahUraian) { ?>
          <form method="post" enctype="multipart/form-data">
            <?= csrf_input() ?>
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
            <div class="mt-3">
              <label class="form-label" for="lampiran_foto">Lampiran Foto <span class="text-muted fw-normal">(opsional)</span></label>
              <input class="form-control" type="file" id="lampiran_foto" name="lampiran_foto[]" accept="image/jpeg,image/png,image/webp" multiple>
              <div class="form-text attachment-help">JPG, PNG, atau WEBP; maksimal 5 foto, masing-masing maksimal 5 MB.</div>
            </div>
            <div class="activity-form-meta"><small class="text-muted">Waktu dicatat otomatis saat kegiatan ditambahkan.</small><small class="text-muted"><span id="jumlahKarakter">0</span> / 500 karakter</small></div>
            <div class="text-end mt-3"><button class="btn btn-inventaris-primary" type="submit"><i class="bi bi-plus-lg me-2"></i>Tambah Kegiatan</button></div>
          </form>
        <?php } elseif ($uraianTersimpan) { ?>
          <div class="alert alert-info mb-0"><i class="bi bi-info-circle-fill me-2"></i>Uraian kegiatan sudah disimpan ke rekap. Anda masih dapat menambah, mengubah, atau menghapus data selama laporan belum difinalisasi.</div>
        <?php } else { ?>
          <div class="alert alert-success mb-0">Laporan telah dikirim sehingga uraian kegiatan tidak dapat diubah.</div>
        <?php } ?>
        <?php if ($error !== '') { ?><div class="alert alert-danger mt-3 mb-0"><?= htmlspecialchars($error) ?></div><?php } ?>
      </div>
    </section>

    <section class="inventaris-card">
      <div class="card-body">
        <h2 class="inventaris-heading">Daftar Uraian Kegiatan</h2>
        <div class="inventory-table table-responsive mobile-scroll-table activity-list-table" tabindex="0" aria-label="Tabel daftar uraian kegiatan, geser ke samping untuk melihat semua kolom">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>No.</th>
                <th>Tanggal &amp; Waktu</th>
                <th>Shift</th>
                <th>Uraian Kegiatan</th>
                <th>Lampiran Foto</th>
                <?php if ($bolehUbahUraian): ?><th class="text-center">Aksi</th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!$uraianKegiatan) { ?><tr>
                  <td colspan="<?= $bolehUbahUraian ? 6 : 5 ?>" class="text-center text-muted py-5">Belum ada uraian kegiatan yang ditambahkan.</td>
                </tr><?php } ?>
              <?php foreach ($uraianKegiatan as $index => $row) { $lampiranBaris = $lampiranPerUraian[(int) $row['id_uraian']] ?? []; ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td>
                    <div><?= $tanggalTampil ?></div><small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars(substr($row['jam'], 0, 5)) ?> WIB</small>
                  </td>
                  <td><?= htmlspecialchars($laporan['nama_shift']) ?></td>
                  <td class="activity-description"><?= nl2br(htmlspecialchars($row['uraian'])) ?></td>
                  <td>
                    <?php if ($lampiranBaris): ?><div class="d-flex flex-wrap gap-1">
                        <?php foreach ($lampiranBaris as $foto): ?><a href="../../<?= htmlspecialchars($foto['path_file']) ?>" target="_blank" title="Lihat <?= htmlspecialchars($foto['nama_file']) ?>"><img src="../../<?= htmlspecialchars($foto['path_file']) ?>" alt="Lampiran kegiatan" width="44" height="44" class="rounded border object-fit-cover"></a><?php endforeach; ?>
                      </div><?php else: ?><span class="text-muted">-</span><?php endif; ?>
                  </td>
                  <?php if ($bolehUbahUraian): ?><td class="text-center inventory-actions"><button class="btn btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#edit<?= (int) $row['id_uraian'] ?>" aria-label="Edit uraian"><i class="bi bi-pencil-square"></i></button><form method="post" class="d-inline" onsubmit="return confirm('Hapus uraian kegiatan ini?')"><?= csrf_input() ?><input type="hidden" name="action" value="hapus"><input type="hidden" name="id_uraian" value="<?= (int) $row['id_uraian'] ?>"><button class="btn btn-delete" type="submit" aria-label="Hapus uraian"><i class="bi bi-trash3"></i></button></form></td><?php endif; ?>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <input class="mobile-table-scrollbar" type="range" min="0" value="0" aria-label="Geser tabel uraian kegiatan ke samping">
        <p class="mobile-scroll-hint mb-0"><i class="bi bi-arrow-left-right me-1"></i>Geser tabel ke samping untuk melihat semua kolom.</p>
      </div>
    </section>

    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a class="btn btn-light btn-inventaris-outline" href="../dashboard.php"><i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard</a>
      <?php if ($bolehUbahUraian): ?><form method="post" action="uraian.php?id=<?= $idLaporan ?>" class="d-inline"><?= csrf_input() ?><input type="hidden" name="action" value="simpan_draft"><button class="btn btn-inventaris-primary" type="submit"><i class="bi bi-floppy me-2"></i>Simpan Draft &amp; Lihat Rekap</button></form><?php endif; ?>
      <a class="btn btn-inventaris-primary" href="detail.php?id=<?= $idLaporan ?>"><i class="bi bi-file-earmark-text me-2"></i>Lihat Rekap Uraian Kegiatan</a>
    </div>
  </div>
</main>

<?php if ($bolehUbahUraian) {
  foreach ($uraianKegiatan as $row) {
    $lampiranBaris = $lampiranPerUraian[(int) $row['id_uraian']] ?? []; ?>
    <div class="modal fade" id="edit<?= (int) $row['id_uraian'] ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="post" enctype="multipart/form-data">
            <?= csrf_input() ?>
            <div class="modal-header">
              <h5 class="modal-title">Edit Uraian Kegiatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="id_uraian" value="<?= (int) $row['id_uraian'] ?>"><label class="form-label">Uraian Kegiatan</label><textarea class="form-control activity-textarea mb-3" name="uraian" maxlength="500" required><?= htmlspecialchars($row['uraian']) ?></textarea><label class="form-label">Foto Saat Ini</label><?php if ($lampiranBaris): ?><div class="row g-2 mb-3"><?php foreach ($lampiranBaris as $foto): ?><div class="col-sm-4"><label class="border rounded p-2 w-100 text-center"><img src="../../<?= htmlspecialchars($foto['path_file']) ?>" alt="Foto kegiatan" class="img-fluid rounded mb-2" style="height:110px;object-fit:cover;"><span class="d-block small"><input type="checkbox" name="hapus_foto[]" value="<?= (int) $foto['id_lampiran'] ?>"> Hapus foto ini</span></label></div><?php endforeach; ?></div><?php else: ?><p class="text-muted small">Belum ada foto untuk uraian kegiatan ini.</p><?php endif; ?><label class="form-label">Tambah atau Ganti Foto</label><input class="form-control" type="file" name="lampiran_foto[]" accept="image/jpeg,image/png,image/webp" multiple><div class="form-text">Pilih foto baru untuk menambah foto. Centang foto lama yang keliru untuk menghapusnya. Maksimal total 5 foto.</div></div>
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
