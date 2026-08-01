<?php
require_once "../../config/satpam_auth.php";

$idUser = (int) ($_SESSION['id_user'] ?? 0);
$idLaporan = (int) ($_GET['id'] ?? $_SESSION['id_laporan'] ?? 0);

if ($idLaporan < 1) {
    header('Location: index.php');
    exit;
}

$laporanStmt = mysqli_prepare($conn, '
    SELECT l.id_laporan, l.status
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $laporan['status'] === 'draft') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $namaBarang = trim($_POST['nama_barang'] ?? '');
        $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($namaBarang !== '' && $jumlah && in_array($keterangan, $kondisiBarang, true)) {
            $urutanResult = mysqli_query($conn, "SELECT COALESCE(MAX(urutan), 0) + 1 AS urutan FROM inventaris WHERE id_laporan = {$idLaporan}");
            $urutan = (int) mysqli_fetch_assoc($urutanResult)['urutan'];
            $stmt = mysqli_prepare($conn, 'INSERT INTO inventaris (id_laporan, created_by, urutan, nama_barang, jumlah, keterangan) VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iiisis', $idLaporan, $idUser, $urutan, $namaBarang, $jumlah, $keterangan);
            mysqli_stmt_execute($stmt);
            mysqli_query($conn, "UPDATE laporan SET inventaris_selesai = 1 WHERE id_laporan = {$idLaporan}");
        }
    }

    if ($action === 'edit') {
        $idInventaris = (int) ($_POST['id_inventaris'] ?? 0);
        $namaBarang = trim($_POST['nama_barang'] ?? '');
        $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $keterangan = trim($_POST['keterangan'] ?? '');
        if ($idInventaris > 0 && $namaBarang !== '' && $jumlah && in_array($keterangan, $kondisiBarang, true)) {
            $stmt = mysqli_prepare($conn, 'UPDATE inventaris SET nama_barang = ?, jumlah = ?, keterangan = ?, updated_at = NOW() WHERE id_inventaris = ? AND id_laporan = ?');
            mysqli_stmt_bind_param($stmt, 'sisii', $namaBarang, $jumlah, $keterangan, $idInventaris, $idLaporan);
            mysqli_stmt_execute($stmt);
        }
    }

    if ($action === 'hapus') {
        $idInventaris = (int) ($_POST['id_inventaris'] ?? 0);
        $stmt = mysqli_prepare($conn, 'DELETE FROM inventaris WHERE id_inventaris = ? AND id_laporan = ?');
        mysqli_stmt_bind_param($stmt, 'ii', $idInventaris, $idLaporan);
        mysqli_stmt_execute($stmt);
        $total = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventaris WHERE id_laporan = {$idLaporan}"))['total'];
        if ($total === 0) {
            mysqli_query($conn, "UPDATE laporan SET inventaris_selesai = 0 WHERE id_laporan = {$idLaporan}");
        }
    }

    header("Location: inventaris.php?id={$idLaporan}");
    exit;
}

$dataStmt = mysqli_prepare($conn, '
    SELECT i.*, u.nama AS nama_input
    FROM inventaris i
    LEFT JOIN users u ON u.id_user = i.created_by
    WHERE i.id_laporan = ?
    ORDER BY i.urutan ASC, i.id_inventaris ASC
');
mysqli_stmt_bind_param($dataStmt, 'i', $idLaporan);
mysqli_stmt_execute($dataStmt);
$inventaris = mysqli_fetch_all(mysqli_stmt_get_result($dataStmt), MYSQLI_ASSOC);

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

                    <?php if ($laporan['status'] === 'draft') { ?>
                        <form method="post" class="row g-3 align-items-end">
                            <input type="hidden" name="action" value="tambah">
                            <div class="col-lg-5">
                                <label class="form-label" for="nama_barang">Nama Barang</label>
                                <input class="form-control" id="nama_barang" name="nama_barang" placeholder="Masukkan nama barang" required>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label" for="jumlah">Jumlah</label>
                                <input class="form-control" id="jumlah" name="jumlah" type="number" min="1" placeholder="Masukkan jumlah" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label" for="keterangan">Keterangan</label>
                                <select class="form-select" id="keterangan" name="keterangan" required>
                                    <option value="">Pilih keterangan</option>
                                    <?php foreach ($kondisiBarang as $kondisi) { ?>
                                        <option value="<?= htmlspecialchars($kondisi) ?>"><?= htmlspecialchars($kondisi) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button class="btn btn-inventaris-primary" type="submit"><i class="bi bi-plus-lg me-2"></i>Tambah Barang</button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <div class="alert alert-success mb-0">Laporan telah dikirim sehingga data inventaris tidak dapat diubah.</div>
                    <?php } ?>
                </div>
            </section>

            <section class="inventaris-card">
                <div class="card-body">
                    <h2 class="inventaris-heading">Daftar Inventaris</h2>
                    <div class="inventory-table table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>No.</th><th>Nama Barang</th><th>Jumlah</th><th>Keterangan</th><th class="text-center">Aksi</th></tr></thead>
                            <tbody>
                            <?php if (!$inventaris) { ?>
                                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada inventaris yang ditambahkan.</td></tr>
                            <?php } ?>
                            <?php foreach ($inventaris as $index => $row) { ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td><?= (int) $row['jumlah'] ?></td>
                                    <td><span class="inventory-badge"><?= htmlspecialchars($row['keterangan']) ?></span></td>
                                    <td class="text-center inventory-actions">
                                    <?php if ($laporan['status'] === 'draft') { ?>
                                        <button class="btn btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#edit<?= (int) $row['id_inventaris'] ?>" aria-label="Edit <?= htmlspecialchars($row['nama_barang']) ?>"><i class="bi bi-pencil-square"></i></button>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Hapus barang ini?')">
                                            <input type="hidden" name="action" value="hapus"><input type="hidden" name="id_inventaris" value="<?= (int) $row['id_inventaris'] ?>">
                                            <button class="btn btn-delete" type="submit" aria-label="Hapus <?= htmlspecialchars($row['nama_barang']) ?>"><i class="bi bi-trash3"></i></button>
                                        </form>
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
                <a class="btn btn-inventaris-primary" href="detail.php?id=<?= $idLaporan ?>"><i class="bi bi-file-earmark-text me-2"></i>Lihat Laporan Inventaris</a>
            </div>
        </div>
</main>

<?php if ($laporan['status'] === 'draft') { foreach ($inventaris as $row) { ?>
<div class="modal fade" id="edit<?= (int) $row['id_inventaris'] ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <form method="post"><div class="modal-header"><h5 class="modal-title">Edit Inventaris</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="id_inventaris" value="<?= (int) $row['id_inventaris'] ?>">
        <label class="form-label">Nama Barang</label><input class="form-control mb-3" name="nama_barang" value="<?= htmlspecialchars($row['nama_barang']) ?>" required>
        <label class="form-label">Jumlah</label><input class="form-control mb-3" name="jumlah" type="number" min="1" value="<?= (int) $row['jumlah'] ?>" required>
        <label class="form-label">Keterangan</label><select class="form-select" name="keterangan" required><?php foreach ($kondisiBarang as $kondisi) { ?><option value="<?= htmlspecialchars($kondisi) ?>" <?= $row['keterangan'] === $kondisi ? 'selected' : '' ?>><?= htmlspecialchars($kondisi) ?></option><?php } ?></select>
    </div><div class="modal-footer"><button class="btn btn-inventaris-primary" type="submit">Simpan Perubahan</button></div></form>
</div></div></div>
<?php } } ?>

<?php include '../../includes/footer.php'; ?>
