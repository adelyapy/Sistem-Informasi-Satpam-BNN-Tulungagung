<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'satpam') {
    header("Location: ../../login.php");
    exit;
}

require "../../config/database.php";

$title = "Buku Mutasi";
$base_url = "../../";
include "../../includes/header.php";

$id_laporan = $_SESSION['id_laporan'] ?? 0;

$query = mysqli_query($conn,"
SELECT
    l.*,
    s.nama_shift,
    s.jam_mulai,
    s.jam_selesai,
    j.tanggal
FROM laporan l
JOIN jadwal_shift j
    ON l.id_jadwal=j.id_jadwal
JOIN shift s
    ON j.id_shift=s.id_shift
WHERE l.id_laporan='$id_laporan'
LIMIT 1
");
?>

<div class="wrapper">

    <?php include "../../includes/satpam_sidebar.php"; ?>

    <div class="main">

        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h3 class="fw-bold mb-1">
                        Buku Mutasi
                    </h3>

                    <small class="text-muted">
                        Riwayat laporan buku mutasi satpam
                    </small>
                </div>
            </div>

            <?php
            if (isset($_GET['success'])) {
            ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($_GET['success']); ?>
                </div>

            <?php
            }
            ?>

            <div class="card shadow-sm border-0">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>Tanggal</th>

                                    <th>Shift</th>

                                    <th>Status</th>

                                    <th>Inventaris</th>

                                    <th>Uraian</th>

                                    <th width="120" class="text-center">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php

                                if (mysqli_num_rows($query) == 0) {

                                ?>

                                    <tr>

                                        <td colspan="7" class="text-center py-5">

                                            <i class="bi bi-journal-x fs-1 text-secondary"></i>

                                            <br><br>

                                            Belum ada laporan aktif pada shift ini.

                                        </td>

                                    </tr>

                                    <?php

                                } else {

                                    $no = 1;

                                    while ($row = mysqli_fetch_assoc($query)) {

                                        switch ($row['status']) {

                                            case 'draft':
                                                $badge = 'secondary';
                                                $status = 'Draft';
                                                break;

                                            case 'menunggu_validasi':
                                                $badge = 'warning';
                                                $status = 'Menunggu Validasi';
                                                break;

                                            case 'tervalidasi':
                                                $badge = 'success';
                                                $status = 'Tervalidasi';
                                                break;

                                            default:
                                                $badge = 'dark';
                                                $status = '-';
                                        }

                                    ?>

                                        <tr>

                                            <td>

                                                <?= date('d-m-Y', strtotime($row['tanggal_laporan'])); ?>

                                            </td>

                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars($row['nama_shift']); ?>

                                                </strong>

                                                <br>

                                                <small class="text-muted">

                                                    <?= substr($row['jam_mulai'], 0, 5); ?>

                                                    -

                                                    <?= substr($row['jam_selesai'], 0, 5); ?>

                                                </small>

                                            </td>

                                            <td>

                                                <span class="badge bg-<?= $badge; ?>">

                                                    <?= $status; ?>

                                                </span>

                                            </td>

                                            <td>

                                                <?php

                                                if ($row['inventaris_selesai']) {

                                                    echo '<span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i>
                                                    Selesai
                                                    </span>';

                                                } else {

                                                    echo '<span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i>
                                                    Belum
                                                    </span>';

                                                }

                                                ?>

                                            </td>

                                            <td>

                                                <?php

                                                if ($row['uraian_selesai']) {

                                                    echo '<span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i>
                                                    Selesai
                                                    </span>';

                                                } else {

                                                    echo '<span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i>
                                                    Belum
                                                    </span>';

                                                }

                                                ?>

                                            </td>

                                            <td class="text-center">

                                                <a href="detail.php?id=<?= $row['id_laporan']; ?>"
                                                    class="btn btn-sm btn-primary">

                                                    <i class="bi bi-eye"></i>

                                                    Detail

                                                </a>

                                            </td>

                                        </tr>

                                <?php

                                    }
                                }

                                ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>