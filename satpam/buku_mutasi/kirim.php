<?php
require_once "../../config/satpam_auth.php";
require_once "../../config/report_signature.php";
require_once "../../config/report_attachment.php";

if (!ensureInventarisDraftColumn($conn) || !ensureUraianDraftColumn($conn) || !ensureStatusRekapColumns($conn)) {
  exit('Status penyimpanan laporan tidak dapat disiapkan.');
}

$id_user     = (int) ($_SESSION['id_user'] ?? 0);
$id_laporan  = (int) ($_GET['id'] ?? $_POST['id_laporan'] ?? $_SESSION['id_laporan'] ?? 0);

if (empty($id_laporan)) {

  header("Location:index.php");
  exit;
}

$q = mysqli_prepare($conn, '
  SELECT l.*
  FROM laporan l
  INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
  WHERE l.id_laporan = ? AND a.id_satpam = ?
  LIMIT 1
');
mysqli_stmt_bind_param($q, 'ii', $id_laporan, $id_user);
mysqli_stmt_execute($q);
$hasilLaporan = mysqli_stmt_get_result($q);

if (mysqli_num_rows($hasilLaporan) === 0) {
  header("Location:index.php");
  exit;
}

$laporan = mysqli_fetch_assoc($hasilLaporan);

if ($laporan['status'] != 'draft') {
  echo "<script>
        alert('Laporan sudah dikirim.');
        window.location='detail.php?id={$id_laporan}';
    </script>";
  exit;
}

$inventaris = mysqli_num_rows(mysqli_query($conn, "
SELECT id_inventaris
FROM inventaris
WHERE id_laporan='$id_laporan'
"));

$uraian = mysqli_num_rows(mysqli_query($conn, "
SELECT id_uraian
FROM uraian_kegiatan
WHERE id_laporan='$id_laporan'
"));

$inventarisBelumDirekap = mysqli_num_rows(mysqli_query($conn, "
SELECT id_inventaris
FROM inventaris
WHERE id_laporan='$id_laporan' AND sudah_direkap = 0
"));

$uraianBelumDirekap = mysqli_num_rows(mysqli_query($conn, "
SELECT id_uraian
FROM uraian_kegiatan
WHERE id_laporan='$id_laporan' AND sudah_direkap = 0
"));

$errors = [];

$anggotaStmt = mysqli_prepare($conn, '
  SELECT anggota.id_satpam, u.nama, u.ttd
  FROM anggota_shift anggota
  INNER JOIN users u ON u.id_user = anggota.id_satpam
  WHERE anggota.id_laporan = ?
  ORDER BY u.nama ASC
');
mysqli_stmt_bind_param($anggotaStmt, 'i', $id_laporan);
mysqli_stmt_execute($anggotaStmt);
$anggotaShift = mysqli_fetch_all(mysqli_stmt_get_result($anggotaStmt), MYSQLI_ASSOC);

if ($inventaris === 0) {
  $errors[] = "Inventaris belum diisi.";
}

if ($uraian === 0) {
  $errors[] = "Uraian kegiatan belum diisi.";
}

if ((int) ($laporan['inventaris_draft_disimpan'] ?? 0) !== 1) {
  $errors[] = "Inventaris belum disimpan. Klik Simpan Draft pada menu Inventaris terlebih dahulu.";
}

if ((int) ($laporan['uraian_draft_disimpan'] ?? 0) !== 1) {
  $errors[] = "Uraian kegiatan belum disimpan. Klik Simpan Draft pada menu Uraian Kegiatan terlebih dahulu.";
}

if ($inventarisBelumDirekap > 0) {
  $errors[] = "Masih ada inventaris baru yang belum disimpan ke rekap.";
}

if ($uraianBelumDirekap > 0) {
  $errors[] = "Masih ada uraian kegiatan baru yang belum disimpan ke rekap.";
}

if (!$anggotaShift) {
  $errors[] = 'Laporan belum memiliki anggota shift.';
}

foreach ($anggotaShift as $anggota) {
  if (empty($anggota['ttd'])) {
    $errors[] = 'Tanda tangan Satpam ' . $anggota['nama'] . ' belum diunggah oleh Administrator.';
  }
}

if (isset($_POST['kirim'])) {

  if (count($errors) > 0) {
    $_SESSION['finalisasi_error'] = implode(' ', $errors);
    header("Location: detail.php?id={$id_laporan}");
    exit;
  }

  if (!ensureLaporanTtdSatpamColumn($conn) || !ensureAnggotaShiftTtdColumn($conn)) {
    echo "<script>alert('Sistem tidak dapat menyiapkan tanda tangan laporan.');history.back();</script>";
    exit;
  }

  try {
    mysqli_begin_transaction($conn);

    // Semua tanda tangan disalin ke relasi anggota_shift sebagai snapshot laporan.
    $snapshot = mysqli_prepare($conn, '
      UPDATE anggota_shift anggota
      INNER JOIN users u ON u.id_user = anggota.id_satpam
      SET anggota.ttd_satpam = u.ttd
      WHERE anggota.id_laporan = ?
    ');
    mysqli_stmt_bind_param($snapshot, 'i', $id_laporan);
    mysqli_stmt_execute($snapshot);

    // Kolom lama dipertahankan agar laporan lama dan halaman cetak lama tetap kompatibel.
    $ttdSatpamLegacy = $anggotaShift[0]['ttd'];
    $update = mysqli_prepare($conn, "
          UPDATE laporan
          SET status='menunggu_validasi', ttd_satpam=?, updated_at=NOW()
          WHERE id_laporan=? AND status='draft'
      ");
    mysqli_stmt_bind_param($update, 'si', $ttdSatpamLegacy, $id_laporan);
    mysqli_stmt_execute($update);

    if (mysqli_stmt_affected_rows($update) !== 1) {
      throw new RuntimeException('Laporan tidak dapat dikirim. Muat ulang halaman lalu coba kembali.');
    }

    mysqli_commit($conn);
  } catch (Throwable $exception) {
    mysqli_rollback($conn);
    appLog($exception);
    $_SESSION['finalisasi_error'] = 'Laporan tidak dapat dikirim. Muat ulang halaman lalu coba kembali.';
    header("Location: detail.php?id={$id_laporan}");
    exit;
  }

  logActivity($conn, 'Kirim laporan', 'laporan', $id_laporan);
  $_SESSION['finalisasi_success'] = 'Laporan berhasil difinalisasi dan dikirim ke Kepala BNN untuk divalidasi.';
  header("Location: detail.php?id={$id_laporan}");
  exit;
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kirim Laporan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <div class="card shadow">

          <div class="card-header">
            <h4 class="mb-0">Konfirmasi Pengiriman Laporan</h4>
          </div>

          <div class="card-body">

            <p>Pastikan seluruh data laporan sudah lengkap.</p>

            <ul class="list-group mb-4">

              <li class="list-group-item d-flex justify-content-between">
                <span>Inventaris</span>
                <span class="badge <?= $inventaris > 0 ? 'bg-success' : 'bg-danger' ?>">
                  <?= $inventaris ?> Data
                </span>
              </li>

              <li class="list-group-item d-flex justify-content-between">
                <span>Uraian Kegiatan</span>
                <span class="badge <?= $uraian > 0 ? 'bg-success' : 'bg-danger' ?>">
                  <?= $uraian ?> Data
                </span>
              </li>

            </ul>

            <?php if (count($errors) > 0) { ?>

              <div class="alert alert-danger">

                <strong>Laporan belum dapat dikirim.</strong>

                <ul class="mb-0 mt-2">
                  <?php foreach ($errors as $e) { ?>
                    <li><?= htmlspecialchars($e) ?></li>
                  <?php } ?>
                </ul>

              </div>

            <?php } else { ?>

              <div class="alert alert-success">
                Semua data telah lengkap dan siap dikirim ke Kepala untuk divalidasi.
              </div>

            <?php } ?>

            <form method="post" class="d-flex justify-content-end gap-2">
              <?= csrf_input() ?>

              <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">

              <a href="detail.php?id=<?= $id_laporan ?>" class="btn btn-secondary">
                Kembali
              </a>

              <button
                type="submit"
                name="kirim"
                class="btn btn-primary"
                <?= count($errors) > 0 ? 'disabled' : ''; ?>>
                Simpan Laporan &amp; Kirim Validasi
              </button>

            </form>

          </div>

        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
