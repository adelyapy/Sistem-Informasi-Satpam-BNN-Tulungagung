<?php
require_once '../../config/admin_auth.php';

date_default_timezone_set('Asia/Jakarta');

$periode = $_GET['periode'] ?? 'harian';
if (!in_array($periode, ['harian', 'mingguan', 'bulanan'], true)) {
  $periode = 'harian';
}

$tanggalInput = $_GET['tanggal'] ?? date('Y-m-d');
$tanggalObjek = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggalInput, new DateTimeZone('Asia/Jakarta'));
if (!$tanggalObjek || $tanggalObjek->format('Y-m-d') !== $tanggalInput) {
  $tanggalObjek = new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
}

$idShift = filter_input(INPUT_GET, 'shift', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$tanggalAwal = $tanggalObjek;
$tanggalAkhir = $tanggalObjek;
$judulPeriode = 'Jadwal Harian';

if ($periode === 'mingguan') {
  $tanggalAwal = $tanggalObjek->modify('monday this week');
  $tanggalAkhir = $tanggalAwal->modify('+6 days');
  $judulPeriode = 'Jadwal Mingguan';
} elseif ($periode === 'bulanan') {
  $tanggalAwal = $tanggalObjek->modify('first day of this month');
  $tanggalAkhir = $tanggalObjek->modify('last day of this month');
  $judulPeriode = 'Jadwal Bulanan';
}

$where = "js.tanggal BETWEEN '" . $tanggalAwal->format('Y-m-d') . "' AND '" . $tanggalAkhir->format('Y-m-d') . "'";
if ($idShift > 0) {
  $where .= ' AND js.id_shift = ' . $idShift;
}

$jadwal = mysqli_query($conn, "
  SELECT js.tanggal, js.status, u.nama, u.kode_satpam, s.nama_shift, s.jam_mulai, s.jam_selesai
  FROM jadwal_shift js
  INNER JOIN users u ON u.id_user = js.id_satpam
  INNER JOIN shift s ON s.id_shift = js.id_shift
  WHERE {$where}
  ORDER BY js.tanggal ASC, s.jam_mulai ASC, u.nama ASC
");

$bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$formatTanggal = static function (DateTimeImmutable $tanggal) use ($bulan): string {
  return $tanggal->format('d') . ' ' . $bulan[(int) $tanggal->format('n')] . ' ' . $tanggal->format('Y');
};

if ($periode === 'harian') {
  $keteranganPeriode = $formatTanggal($tanggalAwal);
} elseif ($periode === 'mingguan') {
  $keteranganPeriode = $formatTanggal($tanggalAwal) . ' s.d. ' . $formatTanggal($tanggalAkhir);
} else {
  $keteranganPeriode = $bulan[(int) $tanggalAwal->format('n')] . ' ' . $tanggalAwal->format('Y');
}

$kembali = 'index.php?' . http_build_query([
  'tanggal' => $tanggalInput,
  'shift' => $idShift ?: null,
]);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($judulPeriode) ?> Satpam</title>
  <style>
    @page { size: A4 portrait; margin: 14mm; }
    * { box-sizing: border-box; }
    body { margin: 0; color: #111; font-family: Arial, sans-serif; font-size: 11pt; }
    .actions { margin: 18px; text-align: right; }
    .actions button { border: 0; border-radius: 4px; padding: 9px 14px; color: #fff; cursor: pointer; font-weight: 700; }
    .print { background: #0d6a35; }
    .back { background: #6c757d; margin-left: 8px; }
    .page { max-width: 794px; margin: 0 auto; padding: 12mm 13mm; }
    .letterhead { position: relative; display: flex; min-height: 70px; align-items: center; justify-content: center; padding: 4px 76px 9px; border-bottom: 3px solid #111; text-align: center; }
    .letterhead img { position: absolute; left: 0; top: 50%; width: 62px; height: 62px; object-fit: contain; transform: translateY(-50%); }
    .letterhead p { margin: 0; }
    .institution { font-size: 14pt; font-weight: 700; }
    .document { margin-top: 4px !important; font-size: 12pt; font-weight: 700; }
    .period { margin: 15px 0 12px; text-align: center; }
    .period p { margin: 0; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #222; padding: 7px 6px; vertical-align: top; overflow-wrap: anywhere; }
    th { background: #e9ecef; text-align: center; font-weight: 700; }
    .center { text-align: center; }
    .empty { padding: 24px; text-align: center; color: #555; }
    @media print {
      html, body { width: 210mm; min-height: 297mm; }
      .actions { display: none !important; }
      .page { max-width: none; margin: 0; padding: 0; }
      .letterhead, .period, table, tr { break-inside: avoid; page-break-inside: avoid; }
      table { page-break-inside: auto; }
      thead { display: table-header-group; }
    }
  </style>
</head>
<body>
  <div class="actions">
    <button class="print" type="button" onclick="window.print()">Cetak</button>
    <button class="back" type="button" onclick="window.location.href='<?= htmlspecialchars($kembali, ENT_QUOTES, 'UTF-8') ?>'">Kembali ke Jadwal Shift</button>
  </div>
  <main class="page">
    <header class="letterhead">
      <img src="../../assets/img/logo-bnn.png" alt="Logo BNN">
      <div>
        <p class="institution">BADAN NARKOTIKA NASIONAL</p>
        <p class="document">JADWAL SHIFT SATPAM</p>
      </div>
    </header>
    <section class="period">
      <p>Periode: <?= htmlspecialchars($keteranganPeriode) ?></p>
    </section>
    <table>
      <thead>
        <tr><th style="width:7%">No.</th><th style="width:17%">Tanggal</th><th>Nama Satpam</th><th style="width:14%">Kode</th><th style="width:17%">Shift</th><th style="width:17%">Jam Shift</th><th style="width:12%">Status</th></tr>
      </thead>
      <tbody>
        <?php if ($jadwal && mysqli_num_rows($jadwal) > 0): $nomor = 1; ?>
          <?php while ($row = mysqli_fetch_assoc($jadwal)): ?>
            <tr>
              <td class="center"><?= $nomor++ ?></td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['tanggal']))) ?></td>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td><?= htmlspecialchars($row['kode_satpam']) ?></td>
              <td><?= htmlspecialchars($row['nama_shift']) ?></td>
              <td class="center"><?= htmlspecialchars(substr($row['jam_mulai'], 0, 5)) ?> - <?= htmlspecialchars(substr($row['jam_selesai'], 0, 5)) ?> WIB</td>
              <td class="center"><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td class="empty" colspan="7">Tidak ada jadwal satpam pada periode yang dipilih.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
  <script>window.onload = () => window.print();</script>
</body>
</html>
