<?php
require_once '../../config/satpam_auth.php';
require_once '../../config/report_signature.php';

ensureAnggotaShiftTtdColumn($conn);

$idLaporan = (int) ($_GET['id'] ?? 0);
$idSatpam = (int) ($_SESSION['id_user'] ?? 0);

$laporanStmt = mysqli_prepare($conn, '
    SELECT l.id_laporan, l.tanggal_laporan, l.ttd_kepala, s.nama_shift, s.jam_mulai, s.jam_selesai,
           u.nama AS pembuat, kepala.nama AS nama_kepala
    FROM laporan l
    INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
    INNER JOIN jadwal_shift j ON j.id_jadwal = l.id_jadwal
    INNER JOIN shift s ON s.id_shift = j.id_shift
    LEFT JOIN users u ON u.id_user = l.created_by
    LEFT JOIN users kepala ON kepala.id_user = l.validated_by
    WHERE l.id_laporan = ? AND a.id_satpam = ? AND l.status = "tervalidasi"
    LIMIT 1
');
mysqli_stmt_bind_param($laporanStmt, 'ii', $idLaporan, $idSatpam);
mysqli_stmt_execute($laporanStmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt));

if (!$laporan) {
  header('Location: index.php');
  exit;
}

$petugasStmt = mysqli_prepare($conn, '
    SELECT u.nama, u.kode_satpam, COALESCE(NULLIF(a.ttd_satpam, \'\'), u.ttd) AS ttd
    FROM anggota_shift a
    INNER JOIN users u ON u.id_user = a.id_satpam
    WHERE a.id_laporan = ?
    ORDER BY u.nama ASC
');
mysqli_stmt_bind_param($petugasStmt, 'i', $idLaporan);
mysqli_stmt_execute($petugasStmt);
$petugasShift = mysqli_fetch_all(mysqli_stmt_get_result($petugasStmt), MYSQLI_ASSOC);

$inventaris = mysqli_query($conn, "SELECT nama_barang, jumlah, keterangan FROM inventaris WHERE id_laporan = {$idLaporan} ORDER BY urutan ASC");
$uraian = mysqli_query($conn, "SELECT jam, uraian FROM uraian_kegiatan WHERE id_laporan = {$idLaporan} ORDER BY urutan ASC");

$namaFile = 'laporan-buku-mutasi-' . $idLaporan . '.html';
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '"');
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Laporan Buku Mutasi</title>
  <style>
    body {
      font: 14px Arial, sans-serif;
      color: #172033;
      margin: 36px
    }

    h1 {
      color: #004aad
    }

    h2 {
      margin-top: 28px;
      color: #004aad;
      font-size: 18px
    }

    table {
      width: 100%;
      border-collapse: collapse
    }

    th,
    td {
      border: 1px solid #cbd5e1;
      padding: 9px;
      text-align: left
    }

    th {
      background: #eaf1ff
    }

    .meta td {
      border: 0;
      padding: 4px
    }

    .signatures {
      display: flex;
      gap: 40px;
      margin-top: 36px
    }

    .signature-block {
      min-width: 220px;
      text-align: center
    }

    .signature-block img {
      display: block;
      max-width: 150px;
      max-height: 75px;
      margin: 12px auto;
      object-fit: contain
    }
  </style>
</head>

<body>
  <h1>Laporan Buku Mutasi Satpam</h1>
  <table class="meta">
    <tr>
      <td>Tanggal</td>
      <td>: <?= htmlspecialchars($laporan['tanggal_laporan']) ?></td>
    </tr>
    <tr>
      <td>Shift</td>
      <td>: <?= htmlspecialchars($laporan['nama_shift']) ?> (<?= substr($laporan['jam_mulai'], 0, 5) ?>–<?= substr($laporan['jam_selesai'], 0, 5) ?>)</td>
    </tr>
    <tr>
      <td>Dibuat oleh</td>
      <td>: <?= htmlspecialchars($laporan['pembuat'] ?: 'Satpam') ?></td>
    </tr>
    <tr>
      <td>Petugas Shift</td>
      <td>: <?php foreach ($petugasShift as $index => $petugas): ?><?= $index ? '<br>' : '' ?><?= htmlspecialchars($petugas['nama']) ?> (<?= htmlspecialchars($petugas['kode_satpam']) ?>)<?php endforeach; ?></td>
    </tr>
    <tr>
      <td>Status</td>
      <td>: Sudah divalidasi Kepala BNN</td>
    </tr>
  </table>
  <h2>Inventaris</h2>
  <table>
    <tr>
      <th>No.</th>
      <th>Nama Barang</th>
      <th>Jumlah</th>
      <th>Keterangan</th>
    </tr><?php $nomor = 1;
          while ($row = mysqli_fetch_assoc($inventaris)) { ?><tr>
        <td><?= $nomor++ ?></td>
        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
        <td><?= (int) $row['jumlah'] ?></td>
        <td><?= htmlspecialchars($row['keterangan']) ?></td>
      </tr><?php } ?>
  </table>
  <h2>Uraian Kegiatan</h2>
  <table>
    <tr>
      <th>No.</th>
      <th>Waktu</th>
      <th>Uraian</th>
    </tr><?php $nomor = 1;
          while ($row = mysqli_fetch_assoc($uraian)) { ?><tr>
        <td><?= $nomor++ ?></td>
        <td><?= htmlspecialchars(substr($row['jam'], 0, 5)) ?></td>
        <td><?= nl2br(htmlspecialchars($row['uraian'])) ?></td>
      </tr><?php } ?>
  </table>
  <div class="signatures">
    <div class="signature-block">
      <strong>Kepala BNN</strong>
      <?php if (!empty($laporan['ttd_kepala'])): ?><img src="../../uploads/ttd/<?= rawurlencode($laporan['ttd_kepala']) ?>" alt="Tanda tangan Kepala BNN"><?php else: ?><br><br><br><?php endif; ?>
      <div>_________________________</div>
      <div><?= htmlspecialchars($laporan['nama_kepala'] ?: 'Kepala BNN') ?></div>
    </div>
    <div class="signature-block">
      <strong>Petugas Shift</strong>
      <?php foreach ($petugasShift as $petugas): ?>
        <div>
          <?php if (!empty($petugas['ttd'])): ?><img src="../../uploads/ttd/<?= rawurlencode($petugas['ttd']) ?>" alt="Tanda tangan <?= htmlspecialchars($petugas['nama']) ?>"><?php else: ?><br><br><br><?php endif; ?>
          <div>_________________________</div>
          <div><?= htmlspecialchars($petugas['nama']) ?></div>
          <small><?= htmlspecialchars($petugas['kode_satpam']) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>

</html>
