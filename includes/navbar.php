<?php

if (!isset($_SESSION)) {
    session_start();
}

require_once "../config/database.php";

$namaNavbar = $_SESSION['nama']; // default

if (isset($_SESSION['id_laporan'])) {

    $id_laporan = $_SESSION['id_laporan'];

    $q = mysqli_query($conn, "
        SELECT u.nama
        FROM anggota_shift a
        JOIN users u ON a.id_satpam = u.id_user
        WHERE a.id_laporan = '$id_laporan'
        ORDER BY u.nama
    ");

    $listNama = [];

    while ($row = mysqli_fetch_assoc($q)) {
        $listNama[] = $row['nama'];
    }

    if (!empty($listNama)) {
        $namaNavbar = implode(', ', $listNama);
    }
}

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">

    <div class="container-fluid">

        <button
            class="btn btn-primary border-0 me-2"
            type="button"
            id="sidebarToggle">

            <i class="bi bi-list fs-4"></i>

        </button>

        <a class="navbar-brand fw-bold" href="#">

            Buku Mutasi Satpam

        </a>

        <div class="ms-auto d-flex align-items-center">

            <div class="dropdown">

                <button
                    class="btn btn-primary dropdown-toggle border-0"
                    type="button"
                    data-bs-toggle="dropdown">

                    <i class="bi bi-person-circle me-2"></i>

                    <?= $namaNavbar; ?>

                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>

                        <span class="dropdown-item-text">

                            <strong><?= ucfirst($_SESSION['role']); ?></strong>

                        </span>

                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>

                        <a
                            class="dropdown-item"
                            href="../logout.php">

                            <i class="bi bi-box-arrow-right me-2"></i>

                            Logout

                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>