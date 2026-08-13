<?php
require_once '../../config/satpam_auth.php';
require_once '../../config/report_attachment.php';

if (!ensureLampiranFotoTable($conn) || !ensureInventarisDraftColumn($conn) || !ensureStatusRekapColumns($conn)) {
  exit('Penyimpanan inventaris tidak dapat disiapkan.');
}

$idUser = (int) ($_SESSION['id_user'] ?? 0);
$idLaporan = (int) ($_GET['id'] ?? $_SESSION['id_laporan'] ?? 0);
if ($idLaporan < 1) {
  header('Location: index.php');
  exit;
}

$laporanStmt = mysqli_prepare($conn, '
  SELECT l.id_laporan, l.status, l.inventaris_draft_disimpan
  FROM laporan l
  INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
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

$kondisiBarang = ['Lengkap berfungsi dengan baik', 'Lengkap baik', 'Lengkap', 'Baik'];
$inventarisTersimpan = (int) $laporan['inventaris_draft_disimpan'] === 1;
$bolehUbahInventaris = $laporan['status'] === 'draft';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'simpan_draft') {
    $total = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventaris WHERE id_laporan = {$idLaporan} AND sudah_direkap = 0"))['total'];
    if ($laporan['status'] !== 'draft') {
      $error = 'Laporan yang telah dikirim tidak dapat diubah.';
    } elseif ($total < 1) {
      $error = 'Tambahkan minimal satu barang sebelum menyimpan draft inventaris.';
    } else {
      $simpanDraft = mysqli_prepare($conn, 'UPDATE inventaris SET sudah_direkap = 1 WHERE id_laporan = ? AND sudah_direkap = 0');
      mysqli_stmt_bind_param($simpanDraft, 'i', $idLaporan);
      if (mysqli_stmt_execute($simpanDraft)) {
        mysqli_query($conn, "UPDATE laporan SET inventaris_selesai = 1, inventaris_draft_disimpan = 1, updated_at = NOW() WHERE id_laporan = {$idLaporan} AND status = 'draft'");
        header("Location: detail.php?id={$idLaporan}");
        exit;
      }
      $error = 'Draft inventaris tidak dapat disimpan.';
    }
  } elseif (!$bolehUbahInventaris) {
    $error = 'Laporan telah dikirim sehingga inventaris tidak dapat diubah.';
  } elseif ($action === 'tambah') {
    $namaBarang = trim($_POST['nama_barang'] ?? '');
    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $files = $_FILES['lampiran_foto'] ?? null;
    $jumlahFoto = is_array($files['name'] ?? null) ? count(array_filter($files['name'], static fn($nama) => $nama !== '')) : 0;

    if ($namaBarang === '' || !$jumlah || !in_array($keterangan, $kondisiBarang, true)) {
      $error = 'Lengkapi data barang dengan benar.';
    } elseif ($jumlahFoto > 5) {
      $error = 'Maksimal 5 foto lampiran untuk setiap barang.';
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
        $urutanResult = mysqli_query($conn, "SELECT COALESCE(MAX(urutan), 0) + 1 AS urutan FROM inventaris WHERE id_laporan = {$idLaporan}");
        $urutan = (int) mysqli_fetch_assoc($urutanResult)['urutan'];
        $tambah = mysqli_prepare($conn, 'INSERT INTO inventaris (id_laporan, created_by, urutan, nama_barang, jumlah, keterangan) VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($tambah, 'iiisis', $idLaporan, $idUser, $urutan, $namaBarang, $jumlah, $keterangan);
        if (!mysqli_stmt_execute($tambah)) {
          throw new RuntimeException('Data inventaris gagal disimpan.');
        }
        $idInventaris = (int) mysqli_insert_id($conn);

        foreach ($fileFoto as $file) {
          $upload = uploadLampiranFoto($file);
          if (!$upload['ok']) {
            throw new RuntimeException($upload['message']);
          }
          $pathTerunggah[] = $upload['path_file'];
          $simpanFoto = mysqli_prepare($conn, 'INSERT INTO lampiran_foto (id_laporan, id_inventaris, nama_file, path_file, ukuran_file, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)');
          mysqli_stmt_bind_param($simpanFoto, 'iissii', $idLaporan, $idInventaris, $upload['nama_file'], $upload['path_file'], $upload['ukuran_file'], $idUser);
          if (!mysqli_stmt_execute($simpanFoto)) {
            throw new RuntimeException('Lampiran foto inventaris gagal disimpan.');
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
  } elseif ($action === 'edit') {
    $idInventaris = (int) ($_POST['id_inventaris'] ?? 0);
    $namaBarang = trim($_POST['nama_barang'] ?? '');
    $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $hapusFotoInput = $_POST['hapus_foto'] ?? [];
    if (!is_array($hapusFotoInput)) {
      $hapusFotoInput = [];
    }
    $hapusFoto = array_values(array_unique(array_filter(array_map('intval', $hapusFotoInput), static fn($id) => $id > 0)));
    $files = $_FILES['lampiran_foto'] ?? null;
    $jumlahFotoBaru = is_array($files['name'] ?? null) ? count(array_filter($files['name'], static fn($nama) => $nama !== '')) : 0;

    if ($idInventaris < 1 || $namaBarang === '' || !$jumlah || !in_array($keterangan, $kondisiBarang, true)) {
      $error = 'Data perubahan inventaris tidak valid.';
    } else {
      $fotoLamaStmt = mysqli_prepare($conn, 'SELECT id_lampiran, path_file FROM lampiran_foto WHERE id_laporan = ? AND id_inventaris = ?');
      mysqli_stmt_bind_param($fotoLamaStmt, 'ii', $idLaporan, $idInventaris);
      mysqli_stmt_execute($fotoLamaStmt);
      $fotoLama = mysqli_fetch_all(mysqli_stmt_get_result($fotoLamaStmt), MYSQLI_ASSOC);
      $fotoYangDihapus = array_values(array_filter($fotoLama, static fn($foto) => in_array((int) $foto['id_lampiran'], $hapusFoto, true)));

      if (count($fotoLama) - count($fotoYangDihapus) + $jumlahFotoBaru > 5) {
        $error = 'Maksimal 5 foto untuk setiap barang setelah perubahan.';
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
          $ubah = mysqli_prepare($conn, 'UPDATE inventaris SET nama_barang = ?, jumlah = ?, keterangan = ?, updated_at = NOW() WHERE id_inventaris = ? AND id_laporan = ?');
          mysqli_stmt_bind_param($ubah, 'sisii', $namaBarang, $jumlah, $keterangan, $idInventaris, $idLaporan);
          if (!mysqli_stmt_execute($ubah)) {
            throw new RuntimeException('Data inventaris tidak dapat diperbarui.');
          }

          foreach ($fotoYangDihapus as $foto) {
            $hapusFotoStmt = mysqli_prepare($conn, 'DELETE FROM lampiran_foto WHERE id_lampiran = ? AND id_laporan = ? AND id_inventaris = ?');
            $idLampiran = (int) $foto['id_lampiran'];
            mysqli_stmt_bind_param($hapusFotoStmt, 'iii', $idLampiran, $idLaporan, $idInventaris);
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
            $simpanFoto = mysqli_prepare($conn, 'INSERT INTO lampiran_foto (id_laporan, id_inventaris, nama_file, path_file, ukuran_file, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($simpanFoto, 'iissii', $idLaporan, $idInventaris, $upload['nama_file'], $upload['path_file'], $upload['ukuran_file'], $idUser);
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
  } elseif ($action === 'hapus') {
    $idInventaris = (int) ($_POST['id_inventaris'] ?? 0);
    hapusLampiranInventaris($conn, $idLaporan, $idInventaris);
    $hapus = mysqli_prepare($conn, 'DELETE FROM inventaris WHERE id_inventaris = ? AND id_laporan = ?');
    mysqli_stmt_bind_param($hapus, 'ii', $idInventaris, $idLaporan);
    mysqli_stmt_execute($hapus);
    $total = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventaris WHERE id_laporan = {$idLaporan}"))['total'];
    if ($total === 0) {
      mysqli_query($conn, "UPDATE laporan SET inventaris_selesai = 0 WHERE id_laporan = {$idLaporan}");
    }
  }

  if ($error === '') {
    header("Location: inventaris.php?id={$idLaporan}");
    exit;
  }
}

$dataStmt = mysqli_prepare($conn, '
  SELECT i.*, u.nama AS nama_input
  FROM inventaris i
  LEFT JOIN users u ON u.id_user = i.created_by
  WHERE i.id_laporan = ? AND i.sudah_direkap = 0
  ORDER BY i.urutan ASC, i.id_inventaris ASC
');
mysqli_stmt_bind_param($dataStmt, 'i', $idLaporan);
mysqli_stmt_execute($dataStmt);
$inventaris = mysqli_fetch_all(mysqli_stmt_get_result($dataStmt), MYSQLI_ASSOC);

$fotoStmt = mysqli_prepare($conn, 'SELECT id_lampiran, id_inventaris, path_file, nama_file FROM lampiran_foto WHERE id_laporan = ? AND id_inventaris IS NOT NULL ORDER BY created_at ASC, id_lampiran ASC');
mysqli_stmt_bind_param($fotoStmt, 'i', $idLaporan);
mysqli_stmt_execute($fotoStmt);
$lampiranPerInventaris = [];
$fotoResult = mysqli_stmt_get_result($fotoStmt);
while ($foto = mysqli_fetch_assoc($fotoResult)) {
  $lampiranPerInventaris[(int) $foto['id_inventaris']][] = $foto;
}

$title = 'Input Inventaris';
$pageTitle = 'Input Inventaris';
$base_url = '../../';
$activeMenu = 'inventaris';
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
        <h2 class="inventaris-heading">Form Input Inventaris</h2>
        <?php if ($bolehUbahInventaris): ?>
          <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="tambah">
            <div class="col-lg-5"><label class="form-label" for="nama_barang">Nama Barang</label><input class="form-control" id="nama_barang" name="nama_barang" placeholder="Masukkan nama barang" required></div>
            <div class="col-lg-3"><label class="form-label" for="jumlah">Jumlah</label><input class="form-control" id="jumlah" name="jumlah" type="number" min="1" placeholder="Masukkan jumlah" required></div>
            <div class="col-lg-4"><label class="form-label" for="keterangan">Keterangan</label><select class="form-select" id="keterangan" name="keterangan" required><option value="">Pilih keterangan</option><?php foreach ($kondisiBarang as $kondisi): ?><option value="<?= htmlspecialchars($kondisi) ?>"><?= htmlspecialchars($kondisi) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-8"><label class="form-label" for="lampiran_foto">Lampiran Foto <span class="text-muted fw-normal">(opsional)</span></label><input class="form-control" type="file" id="lampiran_foto" name="lampiran_foto[]" accept="image/jpeg,image/png,image/webp" multiple><div class="form-text">JPG, PNG, atau WEBP; maksimal 5 foto, masing-masing maksimal 5 MB.</div></div>
            <div class="col-lg-4 text-lg-end"><button class="btn btn-inventaris-primary" type="submit"><i class="bi bi-plus-lg me-2"></i>Tambah Barang</button></div>
          </form>
        <?php elseif ($inventarisTersimpan): ?>
          <div class="alert alert-info mb-0"><i class="bi bi-info-circle-fill me-2"></i>Inventaris sudah disimpan ke rekap. Anda masih dapat menambah, mengubah, atau menghapus data selama laporan belum difinalisasi.</div>
        <?php else: ?>
          <div class="alert alert-success mb-0">Laporan telah dikirim sehingga data inventaris tidak dapat diubah.</div>
        <?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-danger mt-3 mb-0"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      </div>
    </section>

    <section class="inventaris-card">
      <div class="card-body">
        <h2 class="inventaris-heading">Daftar Inventaris</h2>
        <div class="inventory-table table-responsive">
          <table class="table align-middle">
            <thead><tr><th>No.</th><th>Nama Barang</th><th>Jumlah</th><th>Keterangan</th><th>Lampiran Foto</th><?php if ($bolehUbahInventaris): ?><th class="text-center">Aksi</th><?php endif; ?></tr></thead>
            <tbody>
              <?php if (!$inventaris): ?><tr><td colspan="<?= $bolehUbahInventaris ? 6 : 5 ?>" class="text-center text-muted py-5">Belum ada inventaris yang ditambahkan.</td></tr><?php endif; ?>
              <?php foreach ($inventaris as $index => $row): $lampiranBaris = $lampiranPerInventaris[(int) $row['id_inventaris']] ?? []; ?>
                <tr>
                  <td><?= $index + 1 ?></td><td class="fw-medium"><?= htmlspecialchars($row['nama_barang']) ?></td><td><?= (int) $row['jumlah'] ?></td><td><span class="inventory-badge"><?= htmlspecialchars($row['keterangan']) ?></span></td>
                  <td><?php if ($lampiranBaris): ?><div class="d-flex flex-wrap gap-1"><?php foreach ($lampiranBaris as $foto): ?><a href="../../<?= htmlspecialchars($foto['path_file']) ?>" target="_blank" title="Lihat <?= htmlspecialchars($foto['nama_file']) ?>"><img src="../../<?= htmlspecialchars($foto['path_file']) ?>" alt="Lampiran <?= htmlspecialchars($row['nama_barang']) ?>" width="44" height="44" class="rounded border object-fit-cover"></a><?php endforeach; ?></div><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                  <?php if ($bolehUbahInventaris): ?><td class="text-center inventory-actions"><button class="btn btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#edit<?= (int) $row['id_inventaris'] ?>" aria-label="Edit <?= htmlspecialchars($row['nama_barang']) ?>"><i class="bi bi-pencil-square"></i></button><form method="post" class="d-inline" onsubmit="return confirm('Hapus barang ini?')"><input type="hidden" name="action" value="hapus"><input type="hidden" name="id_inventaris" value="<?= (int) $row['id_inventaris'] ?>"><button class="btn btn-delete" type="submit" aria-label="Hapus <?= htmlspecialchars($row['nama_barang']) ?>"><i class="bi bi-trash3"></i></button></form></td><?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a class="btn btn-light btn-inventaris-outline" href="../dashboard.php"><i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard</a>
      <?php if ($bolehUbahInventaris): ?><form method="post" action="inventaris.php?id=<?= $idLaporan ?>" class="d-inline"><input type="hidden" name="action" value="simpan_draft"><button class="btn btn-inventaris-primary" type="submit"><i class="bi bi-floppy me-2"></i>Simpan Draft &amp; Lihat Rekap</button></form><?php endif; ?>
      <a class="btn btn-inventaris-primary" href="detail.php?id=<?= $idLaporan ?>"><i class="bi bi-file-earmark-text me-2"></i>Lihat Rekap Inventaris</a>
    </div>
  </div>
</main>

<?php if ($bolehUbahInventaris): foreach ($inventaris as $row): $lampiranBaris = $lampiranPerInventaris[(int) $row['id_inventaris']] ?? []; ?>
  <div class="modal fade" id="edit<?= (int) $row['id_inventaris'] ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Edit Inventaris</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="id_inventaris" value="<?= (int) $row['id_inventaris'] ?>"><label class="form-label">Nama Barang</label><input class="form-control mb-3" name="nama_barang" value="<?= htmlspecialchars($row['nama_barang']) ?>" required><label class="form-label">Jumlah</label><input class="form-control mb-3" name="jumlah" type="number" min="1" value="<?= (int) $row['jumlah'] ?>" required><label class="form-label">Keterangan</label><select class="form-select mb-3" name="keterangan" required><?php foreach ($kondisiBarang as $kondisi): ?><option value="<?= htmlspecialchars($kondisi) ?>" <?= $row['keterangan'] === $kondisi ? 'selected' : '' ?>><?= htmlspecialchars($kondisi) ?></option><?php endforeach; ?></select><label class="form-label">Foto Saat Ini</label><?php if ($lampiranBaris): ?><div class="row g-2 mb-3"><?php foreach ($lampiranBaris as $foto): ?><div class="col-sm-4"><label class="border rounded p-2 w-100 text-center"><img src="../../<?= htmlspecialchars($foto['path_file']) ?>" alt="Foto inventaris" class="img-fluid rounded mb-2" style="height:110px;object-fit:cover;"><span class="d-block small"><input type="checkbox" name="hapus_foto[]" value="<?= (int) $foto['id_lampiran'] ?>"> Hapus foto ini</span></label></div><?php endforeach; ?></div><?php else: ?><p class="text-muted small">Belum ada foto untuk barang ini.</p><?php endif; ?><label class="form-label">Tambah atau Ganti Foto</label><input class="form-control" type="file" name="lampiran_foto[]" accept="image/jpeg,image/png,image/webp" multiple><div class="form-text">Pilih foto baru untuk menambah foto. Centang foto lama yang keliru untuk menghapusnya. Maksimal total 5 foto.</div></div><div class="modal-footer"><button class="btn btn-inventaris-primary" type="submit">Simpan Perubahan</button></div></form></div></div></div>
<?php endforeach; endif; ?>

<?php include '../../includes/footer.php'; ?>
