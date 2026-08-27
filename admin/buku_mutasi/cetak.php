<?php

require_once '../../config/admin_auth.php';
require_once '../../config/report_signature.php';
require_once '../../config/report_attachment.php';

date_default_timezone_set('Asia/Jakarta');

ensureLaporanTtdKepalaColumn($conn);
ensureLaporanTtdSatpamColumn($conn);
if (!ensureLampiranFotoTable($conn)) {
  exit('Tabel lampiran foto tidak dapat disiapkan.');
}

$idLaporan = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$idLaporan) {
  header('Location: index.php');
  exit;
}

$laporanStmt = mysqli_prepare($conn, '
  SELECT l.*, pembuat.nama AS nama_pembuat, pembuat.kode_satpam AS kode_pembuat,
         s.nama_shift, s.jam_mulai, s.jam_selesai,
         kepala.nama AS nama_kepala
  FROM laporan l
  LEFT JOIN users pembuat ON pembuat.id_user = l.created_by
  LEFT JOIN jadwal_shift jadwal ON jadwal.id_jadwal = l.id_jadwal
  LEFT JOIN shift s ON s.id_shift = jadwal.id_shift
  LEFT JOIN users kepala ON kepala.id_user = l.validated_by
  WHERE l.id_laporan = ?
  LIMIT 1
');
mysqli_stmt_bind_param($laporanStmt, 'i', $idLaporan);
mysqli_stmt_execute($laporanStmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt));
if (!$laporan) {
  header('Location: index.php');
  exit;
}

$petugasStmt = mysqli_prepare($conn, '
  SELECT u.id_user, u.nama, u.kode_satpam, u.ttd
  FROM anggota_shift anggota
  INNER JOIN users u ON u.id_user = anggota.id_satpam
  WHERE anggota.id_laporan = ?
  ORDER BY u.nama ASC
');
mysqli_stmt_bind_param($petugasStmt, 'i', $idLaporan);
mysqli_stmt_execute($petugasStmt);
$petugasShift = mysqli_fetch_all(mysqli_stmt_get_result($petugasStmt), MYSQLI_ASSOC);

// Laporan lama tanpa anggota_shift tetap dapat dicetak dengan data pembuat laporan.
if (!$petugasShift && !empty($laporan['nama_pembuat'])) {
  $petugasShift[] = [
    'id_user' => (int) $laporan['created_by'],
    'nama' => $laporan['nama_pembuat'],
    'kode_satpam' => $laporan['kode_pembuat'],
    'ttd' => $laporan['ttd_satpam'] ?? null,
  ];
}

$uraianStmt = mysqli_prepare($conn, '
  SELECT uk.id_uraian, uk.jam, uk.uraian, pengguna.nama AS nama_input
  FROM uraian_kegiatan uk
  LEFT JOIN users pengguna ON pengguna.id_user = uk.created_by
  WHERE uk.id_laporan = ?
  ORDER BY uk.urutan ASC, uk.id_uraian ASC
');
mysqli_stmt_bind_param($uraianStmt, 'i', $idLaporan);
mysqli_stmt_execute($uraianStmt);
$uraianKegiatan = mysqli_fetch_all(mysqli_stmt_get_result($uraianStmt), MYSQLI_ASSOC);

$inventarisStmt = mysqli_prepare($conn, '
  SELECT i.id_inventaris, i.nama_barang, i.jumlah, i.keterangan, i.created_at,
         pengguna.nama AS nama_input
  FROM inventaris i
  LEFT JOIN users pengguna ON pengguna.id_user = i.created_by
  WHERE i.id_laporan = ?
  ORDER BY i.urutan ASC, i.id_inventaris ASC
');
mysqli_stmt_bind_param($inventarisStmt, 'i', $idLaporan);
mysqli_stmt_execute($inventarisStmt);
$inventaris = mysqli_fetch_all(mysqli_stmt_get_result($inventarisStmt), MYSQLI_ASSOC);

$lampiranStmt = mysqli_prepare($conn, '
  SELECT id_lampiran, id_uraian, id_inventaris, path_file, nama_file
  FROM lampiran_foto
  WHERE id_laporan = ?
  ORDER BY created_at ASC, id_lampiran ASC
');
mysqli_stmt_bind_param($lampiranStmt, 'i', $idLaporan);
mysqli_stmt_execute($lampiranStmt);
$fotoUraian = [];
$fotoInventaris = [];
$lampiranResult = mysqli_stmt_get_result($lampiranStmt);
while ($foto = mysqli_fetch_assoc($lampiranResult)) {
  if (!empty($foto['id_uraian'])) {
    $fotoUraian[(int) $foto['id_uraian']][] = $foto;
  }
  if (!empty($foto['id_inventaris'])) {
    $fotoInventaris[(int) $foto['id_inventaris']][] = $foto;
  }
}

$bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggalObjek = new DateTimeImmutable($laporan['tanggal_laporan']);
$tanggalLaporan = $tanggalObjek->format('d') . ' ' . $bulan[(int) $tanggalObjek->format('n')] . ' ' . $tanggalObjek->format('Y');
$jamShift = substr((string) $laporan['jam_mulai'], 0, 5) . ' - ' . substr((string) $laporan['jam_selesai'], 0, 5) . ' WIB';
$tanggalCetak = date('d/m/Y H:i') . ' WIB';

function cetakFoto(array $foto, string $label): string
{
  if (!$foto) {
    return '<span class="empty-photo">-</span>';
  }

  $html = '<div class="photo-list">';
  foreach ($foto as $item) {
    $path = htmlspecialchars('../../' . ltrim((string) $item['path_file'], '/'), ENT_QUOTES, 'UTF-8');
    $alt = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $html .= '<img src="' . $path . '" alt="' . $alt . '" class="report-photo">';
  }
  return $html . '</div>';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Kegiatan Satpam - <?= htmlspecialchars($tanggalLaporan) ?></title>
  <style>
    @page { size: A4 portrait; margin: 14mm 13mm; }

    * { box-sizing: border-box; }
    body { margin: 0; color: #111; font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.35; }
    .print-actions { margin: 18px; text-align: right; font-family: Arial, sans-serif; }
    .print-actions button { border: 0; border-radius: 4px; padding: 9px 14px; cursor: pointer; font-weight: 700; }
    .btn-print { background: #0d6a35; color: #fff; }
    .btn-back { background: #6c757d; color: #fff; margin-left: 8px; }
    .report-page { max-width: 794px; margin: 0 auto; padding: 12mm 13mm; }
    .letterhead { display: grid; grid-template-columns: 175px minmax(0, 1fr) 145px; align-items: end; gap: 4px; margin: 0; padding: 0 0 8px; border-bottom: 3px solid #111; }
    .letterhead-logo { align-self: center; text-align: center; }
    .letterhead-logo img { display: block; width: 105px; height: 105px; margin: 0 auto; object-fit: contain; }
    .letterhead-logo p { margin: 2px 0 0; font-family: Arial, sans-serif; font-size: 10.5pt; font-weight: 800; line-height: 1.02; text-transform: uppercase; }
    .letterhead-title { align-self: center; text-align: center; letter-spacing: .2px; }
    .letterhead-title p { margin-left: 0; margin-right: 0; }
    .letterhead-title .institution { margin-top: 0; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 16pt; font-weight: 800; line-height: 1.08; }
    .letterhead-title .unit { margin-top: 2px; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 15pt; font-weight: 800; line-height: 1.08; }
    .letterhead-title .address, .letterhead-title .contact, .letterhead-title .email { margin-top: 2px; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11.5pt; line-height: 1.12; }
    .letterhead-postal { margin: 0 0 5px; font-family: Arial, sans-serif; font-size: 10.5pt; text-align: right; white-space: nowrap; }
    .letterhead { display: grid; grid-template-columns: 175px minmax(0, 1fr) 145px; align-items: end; gap: 4px; margin: 0; padding: 0 0 8px; border-bottom: 3px solid #111; }
    .letterhead-logo { align-self: center; text-align: center; }
    .letterhead-logo img { display: block; width: 105px; height: 105px; margin: 0 auto; object-fit: contain; }
    .letterhead-logo p { margin: 2px 0 0; font-family: Arial, sans-serif; font-size: 10.5pt; font-weight: 800; line-height: 1.02; text-transform: uppercase; }
    .letterhead-title { align-self: center; text-align: center; letter-spacing: .2px; }
    .letterhead-title p { margin-left: 0; margin-right: 0; }
    .letterhead-title .institution { margin-top: 0; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 16pt; font-weight: 800; line-height: 1.08; }
    .letterhead-title .unit { margin-top: 2px; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 15pt; font-weight: 800; line-height: 1.08; }
    .letterhead-title .address, .letterhead-title .contact, .letterhead-title .email { margin-top: 2px; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11.5pt; line-height: 1.12; }
    .letterhead-postal { margin: 0 0 5px; font-family: Arial, sans-serif; font-size: 10.5pt; text-align: right; white-space: nowrap; }
    .section-title { margin: 20px 0 8px; font-size: 11pt; font-weight: 700; text-transform: uppercase; }
    .identity-table { width: 100%; border-collapse: collapse; margin: 12px 0 18px; }
    .identity-table td { padding: 3px 0; vertical-align: top; }
    .identity-table .label { width: 145px; font-weight: 700; }
    .identity-table .colon { width: 12px; text-align: center; }
    .petugas-list { margin: 0; padding-left: 19px; }
    .report-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; border: 1px solid #222; }
    .report-table th, .report-table td { border: 1px solid #222; padding: 6px; vertical-align: top; overflow-wrap: anywhere; }
    .report-table th { background: #e9ecef; text-align: center; font-weight: 700; }
    .number, .time, .quantity { text-align: center; }
    .table-time { white-space: nowrap; }
    .photo-list { display: flex; flex-wrap: wrap; gap: 4px; justify-content: center; }
    .report-photo { width: 54px; height: 54px; border: 1px solid #888; object-fit: cover; }
    .empty-photo { display: inline-block; min-width: 12px; text-align: center; }
    .photo-attachment-section { margin-top: 12px; break-inside: avoid; page-break-inside: avoid; }
    .attachment-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 7px; }
    .attachment-item { margin: 0; border: 1px solid #777; padding: 4px; text-align: center; break-inside: avoid; page-break-inside: avoid; }
    .attachment-item img { width: 100%; height: 92px; object-fit: cover; display: block; }
    .attachment-item figcaption { margin-top: 4px; font-size: 8pt; line-height: 1.2; }
    .signature-section { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 34px; margin-top: 35px; break-inside: avoid; page-break-inside: avoid; }
    .signature-party { min-width: 0; text-align: center; }
    .signature-party-head { min-height: 63px; display: flex; flex-direction: column; justify-content: flex-start; }
    .signature-heading { margin: 0; font-weight: 700; text-transform: uppercase; text-align: left; }
    .signature-party:first-child .signature-heading { margin-top: 39px; }
    .petugas-signature-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 14px; }
    .signature-card { min-height: 138px; text-align: center; break-inside: avoid; page-break-inside: avoid; }
    .signature-image { display: block; width: 145px; height: 66px; margin: 10px auto 5px; object-fit: contain; }
    .signature-space { height: 81px; }
    .signature-line { margin: 0 auto 3px; border-top: 1px solid #111; width: 150px; }
    .signature-name { font-weight: 700; }
    .signature-code { font-size: 9.5pt; }
    .kepala-signature { text-align: center; }
    .kepala-signature .date { margin: 0 0 3px; }
    .kepala-signature .role { margin: 0; font-weight: 700; text-transform: uppercase; }
    .print-date-footer { display: none; }

    @media print {
      html, body { width: 210mm; min-height: 297mm; background: #fff; }
      body { font-size: 10.5pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .print-actions { display: none !important; }
      .report-page { width: auto; max-width: none; margin: 0; padding: 0; }
      .letterhead, .identity-table, .report-table, .photo-attachment-section, .signature-section, .attachment-item, .signature-card { break-inside: avoid; page-break-inside: avoid; }
      .report-table { page-break-inside: auto; }
      .report-table tr { break-inside: avoid; page-break-inside: avoid; }
      .report-table thead { display: table-header-group; }
      .report-photo, .attachment-item img, .signature-image { image-rendering: auto; }
      .print-date-footer { display: block; position: fixed; left: 0; bottom: 0; font-size: 8.5pt; }
    }
  </style>
</head>
<body>
  <div class="print-actions">
    <button class="btn-print" type="button" onclick="window.print()">Cetak</button>
    <button class="btn-back" type="button" onclick="window.location.href='index.php'">Kembali ke Monitoring Laporan</button>
  </div>

  <main class="report-page">
    <header class="letterhead">
      <div class="letterhead-logo">
        <img src="../../assets/img/logo-bnn-manual.png" alt="Logo Badan Narkotika Nasional">
        <p>Kabupaten<br>Tulungagung</p>
      </div>
      <div class="letterhead-title">
        <p class="institution">BADAN NARKOTIKA NASIONAL</p>
        <p class="unit">KABUPATEN TULUNGAGUNG</p>
        <p class="address">Jln. SULTAN AGUNG III No. 1 A</p>
        <p class="contact">Call Center 0821 5224 9911</p>
        <p class="email">Email. Bnnkab_tulungagung@bnn.go.id</p>
      </div>
      <p class="letterhead-postal">Kode Pos 66226</p>
    </header>

    <section>
      <h2 class="section-title">Identitas Laporan</h2>
      <table class="identity-table">
        <tr><td class="label">Tanggal Laporan</td><td class="colon">:</td><td><?= htmlspecialchars($tanggalLaporan) ?></td></tr>
        <tr><td class="label">Shift</td><td class="colon">:</td><td><?= htmlspecialchars($laporan['nama_shift'] ?: '-') ?></td></tr>
        <tr><td class="label">Jam Shift</td><td class="colon">:</td><td><?= htmlspecialchars($jamShift) ?></td></tr>
        <tr>
          <td class="label">Petugas Shift</td><td class="colon">:</td>
          <td>
            <ol class="petugas-list">
              <?php foreach ($petugasShift as $petugas): ?>
                <li><?= htmlspecialchars($petugas['nama']) ?> (<?= htmlspecialchars($petugas['kode_satpam']) ?>)</li>
              <?php endforeach; ?>
            </ol>
          </td>
        </tr>
      </table>
    </section>

    <section>
      <h2 class="section-title">Uraian Kegiatan</h2>
      <table class="report-table">
        <thead><tr><th style="width: 6%">No.</th><th style="width: 20%">Tanggal &amp; Waktu</th><th style="width: 10%">Shift</th><th>Uraian Kegiatan</th><th style="width: 17%">Pengunggah</th><th style="width: 16%">Lampiran Foto</th></tr></thead>
        <tbody>
          <?php if ($uraianKegiatan): ?>
            <?php foreach ($uraianKegiatan as $index => $uraian): ?>
              <tr>
                <td class="number"><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($tanggalLaporan) ?><br><span class="table-time"><?= htmlspecialchars(substr((string) $uraian['jam'], 0, 5)) ?> WIB</span></td>
                <td><?= htmlspecialchars($laporan['nama_shift'] ?: '-') ?></td>
                <td><?= nl2br(htmlspecialchars($uraian['uraian'])) ?></td>
                <td><?= htmlspecialchars($uraian['nama_input'] ?: '-') ?></td>
                <td><?= cetakFoto($fotoUraian[(int) $uraian['id_uraian']] ?? [], 'Lampiran kegiatan') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="number">Belum ada uraian kegiatan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

    <?php if (false && $fotoUraian): ?>
      <section class="photo-attachment-section">
        <h2 class="section-title">Lampiran Foto Kegiatan</h2>
        <div class="attachment-grid">
          <?php foreach ($uraianKegiatan as $uraian): ?>
            <?php foreach ($fotoUraian[(int) $uraian['id_uraian']] ?? [] as $foto): ?>
              <figure class="attachment-item">
                <img src="../../<?= htmlspecialchars($foto['path_file']) ?>" alt="Lampiran kegiatan">
                <figcaption><?= htmlspecialchars(substr((string) $uraian['jam'], 0, 5)) ?> WIB — <?= htmlspecialchars(mb_strimwidth($uraian['uraian'], 0, 55, '…')) ?></figcaption>
              </figure>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section>
      <h2 class="section-title">Inventaris</h2>
      <table class="report-table">
        <thead><tr><th style="width: 6%">No.</th><th style="width: 17%">Waktu Input</th><th style="width: 19%">Nama Barang</th><th style="width: 8%">Jumlah</th><th>Keterangan</th><th style="width: 16%">Pengunggah</th><th style="width: 14%">Lampiran Foto</th></tr></thead>
        <tbody>
          <?php if ($inventaris): ?>
            <?php foreach ($inventaris as $index => $item): ?>
              <tr>
                <td class="number"><?= $index + 1 ?></td>
                <td><?= date('d-m-Y', strtotime($item['created_at'])) ?><br><span class="table-time"><?= date('H:i', strtotime($item['created_at'])) ?> WIB</span></td>
                <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                <td class="quantity"><?= (int) $item['jumlah'] ?></td>
                <td><?= nl2br(htmlspecialchars($item['keterangan'])) ?></td>
                <td><?= htmlspecialchars($item['nama_input'] ?: '-') ?></td>
                <td><?= cetakFoto($fotoInventaris[(int) $item['id_inventaris']] ?? [], 'Lampiran foto inventaris') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="number">Belum ada data inventaris.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

    <section class="signature-section">
      <div class="signature-party">
        <div class="signature-party-head">
          <p class="signature-heading">Petugas Shift</p>
        </div>
        <div class="petugas-signature-grid">
          <?php foreach ($petugasShift as $petugas): ?>
            <div class="signature-card">
              <?php if (!empty($petugas['ttd'])): ?>
                <img class="signature-image" src="../../uploads/ttd/<?= rawurlencode($petugas['ttd']) ?>" alt="Tanda tangan <?= htmlspecialchars($petugas['nama']) ?>">
              <?php else: ?>
                <div class="signature-space"></div>
              <?php endif; ?>
              <div class="signature-line"></div>
              <div class="signature-name"><?= htmlspecialchars($petugas['nama']) ?></div>
              <div class="signature-code"><?= htmlspecialchars($petugas['kode_satpam']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="signature-party kepala-signature">
        <div class="signature-party-head">
          <p class="date">Tulungagung, <?= htmlspecialchars($tanggalLaporan) ?></p>
          <p style="margin: 0;">Mengetahui,</p>
          <p class="role">Kepala BNN Kabupaten Tulungagung</p>
        </div>
        <?php if ($laporan['status'] === 'tervalidasi' && !empty($laporan['ttd_kepala'])): ?>
          <img class="signature-image" src="../../uploads/ttd/<?= rawurlencode($laporan['ttd_kepala']) ?>" alt="Tanda tangan Kepala BNN">
        <?php else: ?>
          <div class="signature-space"></div>
        <?php endif; ?>
        <div class="signature-line"></div>
        <div class="signature-name"><?= htmlspecialchars($laporan['nama_kepala'] ?: 'Kepala BNN') ?></div>
      </div>
    </section>
  </main>

  <div class="print-date-footer">Dicetak: <?= htmlspecialchars($tanggalCetak) ?></div>

  <script>window.onload = () => window.print();</script>
  <script>
    window.onload = () => {
      const halamanLaporan = document.querySelector('.report-page');
      const bagian = Array.from(halamanLaporan.children).filter((elemen) => elemen.tagName === 'SECTION');
      const uraian = bagian.find((elemen) => elemen.querySelector('.section-title')?.textContent.trim() === 'Uraian Kegiatan');
      const inventaris = bagian.find((elemen) => elemen.querySelector('.section-title')?.textContent.trim() === 'Inventaris');

      if (uraian && inventaris) {
        halamanLaporan.insertBefore(inventaris, uraian);
      }

      window.print();
    };
  </script>
</body>
</html>