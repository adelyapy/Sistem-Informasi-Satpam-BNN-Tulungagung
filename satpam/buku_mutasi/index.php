<?php
require_once '../../config/satpam_auth.php';

date_default_timezone_set('Asia/Jakarta');

$idSatpam = (int) ($_SESSION['id_user'] ?? 0);
$tanggalAwal = $_GET['tanggal_awal'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
foreach (['tanggalAwal', 'tanggalAkhir'] as $namaVariabel) {
    if ($$namaVariabel !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $$namaVariabel)) {
        $$namaVariabel = '';
    }
}

$laporanStmt = mysqli_prepare($conn, '
    SELECT l.id_laporan, l.tanggal_laporan, l.status, l.created_at, l.validated_at,
           j.tanggal AS tanggal_shift, s.nama_shift, s.jam_mulai, s.jam_selesai,
           pembuat.nama AS dibuat_oleh
    FROM laporan l
    INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
    INNER JOIN jadwal_shift j ON j.id_jadwal = l.id_jadwal
    INNER JOIN shift s ON s.id_shift = j.id_shift
    LEFT JOIN users pembuat ON pembuat.id_user = l.created_by
    WHERE a.id_satpam = ?
    ORDER BY l.created_at DESC
');
mysqli_stmt_bind_param($laporanStmt, 'i', $idSatpam);
mysqli_stmt_execute($laporanStmt);
$laporan = mysqli_fetch_all(mysqli_stmt_get_result($laporanStmt), MYSQLI_ASSOC);

$laporan = array_values(array_filter($laporan, function (array $row) use ($tanggalAwal, $tanggalAkhir): bool {
    return ($tanggalAwal === '' || $row['tanggal_laporan'] >= $tanggalAwal)
        && ($tanggalAkhir === '' || $row['tanggal_laporan'] <= $tanggalAkhir);
}));

$perHalaman = 6;
$totalData = count($laporan);
$totalHalaman = max(1, (int) ceil($totalData / $perHalaman));
$halaman = max(1, min($totalHalaman, (int) ($_GET['halaman'] ?? 1)));
$laporanTampil = array_slice($laporan, ($halaman - 1) * $perHalaman, $perHalaman);

$bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$formatTanggal = function (string $nilai) use ($bulan): string {
    $tanggal = new DateTime($nilai);
    return $tanggal->format('d') . ' ' . $bulan[(int) $tanggal->format('n')] . ' ' . $tanggal->format('Y');
};
$filterQuery = function (int $targetHalaman) use ($tanggalAwal, $tanggalAkhir): string {
    return http_build_query(['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'halaman' => $targetHalaman]);
};

$title = 'Daftar Laporan';
$pageTitle = 'Daftar Laporan';
$base_url = '../../';
$activeMenu = 'laporan';
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
                <h2 class="inventaris-heading">Filter Laporan</h2>
                <form method="get" class="row g-3 align-items-end justify-content-start">
                    <div class="col-lg-4"><label class="form-label" for="tanggal_awal">Tanggal Awal</label><input class="form-control" id="tanggal_awal" name="tanggal_awal" type="date" value="<?= htmlspecialchars($tanggalAwal) ?>"></div>
                    <div class="col-lg-4"><label class="form-label" for="tanggal_akhir">Tanggal Akhir</label><input class="form-control" id="tanggal_akhir" name="tanggal_akhir" type="date" value="<?= htmlspecialchars($tanggalAkhir) ?>"></div>
                    <div class="col-lg-2 d-grid"><button class="btn btn-inventaris-primary" type="submit"><i class="bi bi-search me-2"></i>Tampilkan</button></div>
                </form>
            </div>
        </section>

        <section class="inventaris-card">
            <div class="card-body">
                <h2 class="inventaris-heading">Daftar Laporan</h2>
                <div class="inventory-table table-responsive">
                    <table class="table align-middle report-table">
                        <thead><tr><th>No.</th><th>Tanggal &amp; Waktu</th><th>Dibuat Oleh</th><th>Shift</th><th>Keterangan</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                        <?php if (!$laporanTampil) { ?><tr><td colspan="6" class="text-center text-muted py-5">Tidak ada laporan yang sesuai dengan filter.</td></tr><?php } ?>
                        <?php foreach ($laporanTampil as $index => $row) { $tervalidasi = $row['status'] === 'tervalidasi'; ?>
                            <tr>
                                <td><?= (($halaman - 1) * $perHalaman) + $index + 1 ?></td>
                                <td><div><?= $formatTanggal($row['tanggal_laporan']) ?></div><small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars(substr($row['created_at'], 11, 5)) ?> WIB</small></td>
                                <td><?= htmlspecialchars($row['dibuat_oleh'] ?: 'Satpam') ?></td>
                                <td><div class="fw-medium"><?= htmlspecialchars($row['nama_shift']) ?></div><small class="text-muted"><?= substr($row['jam_mulai'], 0, 5) ?>–<?= substr($row['jam_selesai'], 0, 5) ?></small></td>
                                <td><span class="report-status <?= $tervalidasi ? 'report-status-valid' : 'report-status-pending' ?>"><i class="bi <?= $tervalidasi ? 'bi-check-circle-fill' : 'bi-clock-history' ?> me-1"></i><?= $tervalidasi ? 'Sudah divalidasi Kepala BNN' : 'Belum divalidasi Kepala BNN' ?></span></td>
                                <td class="text-center report-actions"><a href="detail.php?id=<?= (int) $row['id_laporan'] ?>" aria-label="Lihat laporan" title="Lihat laporan"><i class="bi bi-eye"></i></a><?php if ($tervalidasi) { ?><a href="unduh.php?id=<?= (int) $row['id_laporan'] ?>" aria-label="Unduh laporan" title="Unduh laporan"><i class="bi bi-download"></i></a><?php } ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalData > 0) { $awal = (($halaman - 1) * $perHalaman) + 1; $akhir = min($halaman * $perHalaman, $totalData); ?>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4"><span class="text-muted small">Menampilkan <?= $awal ?> - <?= $akhir ?> dari <?= $totalData ?> data</span><nav aria-label="Halaman laporan"><ul class="pagination pagination-sm mb-0"><li class="page-item <?= $halaman === 1 ? 'disabled' : '' ?>"><a class="page-link" href="?<?= $filterQuery(max(1, $halaman - 1)) ?>">&laquo;</a></li><?php for ($nomor = 1; $nomor <= $totalHalaman; $nomor++) { ?><li class="page-item <?= $nomor === $halaman ? 'active' : '' ?>"><a class="page-link" href="?<?= $filterQuery($nomor) ?>"><?= $nomor ?></a></li><?php } ?><li class="page-item <?= $halaman === $totalHalaman ? 'disabled' : '' ?>"><a class="page-link" href="?<?= $filterQuery(min($totalHalaman, $halaman + 1)) ?>">&raquo;</a></li></ul></nav></div>
                <?php } ?>
            </div>
        </section>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
