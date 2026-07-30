<?php
require_once "../../config/satpam_auth.php";

$title = "Uraian Kegiatan";
$base_url = "../../";

$idUser = (int) ($_SESSION['id_user'] ?? 0);
$idLaporan = (int) ($_GET['id'] ?? $_SESSION['id_laporan'] ?? 0);

if ($idLaporan <= 0) {
    header("Location: index.php");
    exit;
}

$laporanStmt = mysqli_prepare($conn, "
    SELECT l.*
    FROM laporan l
    INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
    WHERE l.id_laporan = ? AND a.id_satpam = ?
    LIMIT 1
");
mysqli_stmt_bind_param($laporanStmt, "ii", $idLaporan, $idUser);
mysqli_stmt_execute($laporanStmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt));

if (!$laporan) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $laporan['status'] === 'draft') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah' || $action === 'edit') {
        $jam = trim($_POST['jam'] ?? '');
        $uraian = trim($_POST['uraian'] ?? '');

        if (!preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $jam) || $uraian === '') {
            header("Location: uraian.php?id={$idLaporan}&error=invalid");
            exit;
        }

        if ($action === 'tambah') {
            $urutanResult = mysqli_query($conn, "SELECT COALESCE(MAX(urutan), 0) + 1 AS urutan FROM uraian_kegiatan WHERE id_laporan = {$idLaporan}");
            $urutan = (int) mysqli_fetch_assoc($urutanResult)['urutan'];
            $stmt = mysqli_prepare($conn, "INSERT INTO uraian_kegiatan (id_laporan, created_by, urutan, jam, uraian) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiiss", $idLaporan, $idUser, $urutan, $jam, $uraian);
            mysqli_stmt_execute($stmt);
        }

        if ($action === 'edit') {
            $idUraian = (int) ($_POST['id_uraian'] ?? 0);
            $stmt = mysqli_prepare($conn, "UPDATE uraian_kegiatan SET jam = ?, uraian = ? WHERE id_uraian = ? AND id_laporan = ?");
            mysqli_stmt_bind_param($stmt, "ssii", $jam, $uraian, $idUraian, $idLaporan);
            mysqli_stmt_execute($stmt);
        }

        mysqli_query($conn, "UPDATE laporan SET uraian_selesai = 1 WHERE id_laporan = {$idLaporan}");
    }

    if ($action === 'hapus') {
        $idUraian = (int) ($_POST['id_uraian'] ?? 0);
        $stmt = mysqli_prepare($conn, "DELETE FROM uraian_kegiatan WHERE id_uraian = ? AND id_laporan = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idUraian, $idLaporan);
        mysqli_stmt_execute($stmt);

        $remaining = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM uraian_kegiatan WHERE id_laporan = {$idLaporan}"));
        if ((int) $remaining['total'] === 0) {
            mysqli_query($conn, "UPDATE laporan SET uraian_selesai = 0 WHERE id_laporan = {$idLaporan}");
        }
    }

    header("Location: uraian.php?id={$idLaporan}");
    exit;
}

$dataStmt = mysqli_prepare($conn, "
    SELECT u.*, us.nama
    FROM uraian_kegiatan u
    LEFT JOIN users us ON us.id_user = u.created_by
    WHERE u.id_laporan = ?
    ORDER BY u.urutan ASC
");
mysqli_stmt_bind_param($dataStmt, "i", $idLaporan);
mysqli_stmt_execute($dataStmt);
$data = mysqli_stmt_get_result($dataStmt);

include "../../includes/header.php";
?>

<div class="wrapper">
    <?php include "../../includes/satpam_sidebar.php"; ?>
    <div class="main">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Uraian Kegiatan</h3>
                <div class="d-flex gap-2">
                    <a href="detail.php?id=<?= $idLaporan ?>" class="btn btn-outline-secondary">Kembali</a>
                    <?php if ($laporan['status'] === 'draft') { ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambah">Tambah</button>
                    <?php } ?>
                </div>
            </div>

            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger">Jam dan uraian kegiatan wajib diisi dengan benar.</div>
            <?php } ?>

            <div class="card"><div class="card-body p-0"><table class="table table-bordered mb-0">
                <thead><tr><th>No</th><th>Jam</th><th>Uraian</th><th>Dibuat oleh</th><th width="160">Aksi</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($data) === 0) { ?>
                    <tr><td colspan="5" class="text-center py-4">Belum ada data.</td></tr>
                <?php } while ($row = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><?= (int) $row['urutan'] ?></td>
                        <td><?= htmlspecialchars(substr($row['jam'], 0, 5)) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['uraian'])) ?></td>
                        <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                        <td>
                        <?php if ($laporan['status'] === 'draft') { ?>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= (int) $row['id_uraian'] ?>">Edit</button>
                            <form method="post" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="id_uraian" value="<?= (int) $row['id_uraian'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                            </form>
                        <?php } else { ?><span class="badge bg-success">Read Only</span><?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table></div></div>

            <?php if ($laporan['status'] === 'draft') { ?>
                <div class="modal fade" id="tambah" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                    <form method="post"><div class="modal-header"><h5 class="modal-title">Tambah Uraian</h5></div><div class="modal-body">
                        <input type="hidden" name="action" value="tambah">
                        <label class="form-label">Jam</label><input type="time" name="jam" class="form-control mb-3" required>
                        <label class="form-label">Uraian</label><textarea name="uraian" class="form-control" required></textarea>
                    </div><div class="modal-footer"><button class="btn btn-primary" type="submit">Simpan</button></div></form>
                </div></div></div>

                <?php mysqli_data_seek($data, 0); while ($row = mysqli_fetch_assoc($data)) { ?>
                    <div class="modal fade" id="edit<?= (int) $row['id_uraian'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                        <form method="post"><div class="modal-header"><h5 class="modal-title">Edit Uraian</h5></div><div class="modal-body">
                            <input type="hidden" name="action" value="edit"><input type="hidden" name="id_uraian" value="<?= (int) $row['id_uraian'] ?>">
                            <label class="form-label">Jam</label><input type="time" name="jam" value="<?= htmlspecialchars(substr($row['jam'], 0, 5)) ?>" class="form-control mb-3" required>
                            <label class="form-label">Uraian</label><textarea name="uraian" class="form-control" required><?= htmlspecialchars($row['uraian']) ?></textarea>
                        </div><div class="modal-footer"><button class="btn btn-warning" type="submit">Update</button></div></form>
                    </div></div></div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>
