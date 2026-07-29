<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require "../../config/database.php";

$title = "Monitoring Buku Mutasi";
$base_url = "../../";
include "../../includes/header.php";
include "../../includes/admin_sidebar.php";

$query = mysqli_query($conn, "
    SELECT
        l.id_laporan,
        j.tanggal,
        u.nama,
        s.nama_shift,
        l.status
    FROM laporan l
    JOIN users u ON l.created_by = u.id_user
    JOIN jadwal_shift j ON l.id_jadwal = j.id_jadwal
    JOIN shift s ON j.id_shift = s.id_shift
    ORDER BY j.tanggal DESC, l.id_laporan DESC
");
?>

<div class="main">
    <div class="container-fluid py-4">

        <h3 class="mb-4">Monitoring Buku Mutasi</h3>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Satpam</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($row=mysqli_fetch_assoc($query)){ ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $row['tanggal']; ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['nama_shift']); ?></td>
                            <td><?= ucfirst(str_replace('_',' ',$row['status'])); ?></td>
                            <td>
                                <a href="detail.php?id=<?= $row['id_laporan']; ?>" class="btn btn-primary btn-sm">Detail</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include "../../includes/footer.php"; ?>
