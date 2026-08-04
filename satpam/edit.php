<?php

require_once "../config/admin_auth.php";
require_once "../config/function.php";

$title = "Detail Satpam";
$base_url = "../";
$activeMenu = "data_satpam";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$query = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id_user='$id'
    AND role='satpam'
");

if (mysqli_num_rows($query) == 0) {
    header("Location: index.php");
    exit;
}

$satpam = mysqli_fetch_assoc($query);

include "../includes/header.php";
?>

<?php include "../includes/navbar.php"; ?>
<?php include "../includes/admin_sidebar.php"; ?>

<div class="main-content">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h3 class="fw-bold">
                    Detail Satpam
                </h3>

                <p class="text-muted mb-0">
                    Informasi data anggota satpam.
                </p>

            </div>

            <a href="index.php" class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>

                Kembali

            </a>

        </div>

        <div class="card shadow-sm">

            <div class="card-body">

                <div class="row">

                    <div class="col-lg-4 text-center">

                        <?php if(!empty($satpam['foto'])): ?>

                            <img
                                src="../../uploads/foto/<?= $satpam['foto']; ?>"
                                class="img-thumbnail rounded-circle mb-3"
                                style="width:220px;height:220px;object-fit:cover;">

                        <?php else: ?>

                            <img
                                src="../../assets/img/default-user.png"
                                class="img-thumbnail rounded-circle mb-3"
                                style="width:220px;height:220px;object-fit:cover;">

                        <?php endif; ?>

                    </div>

                    <div class="col-lg-8">

                        <table class="table table-borderless">

                            <tr>
                                <th width="180">Kode Satpam</th>
                                <td>: <?= $satpam['kode_satpam']; ?></td>
                            </tr>

                            <tr>
                                <th>Nama Satpam</th>
                                <td>: <?= htmlspecialchars($satpam['nama']); ?></td>
                            </tr>

                            <tr>
                                <th>Tanda Tangan</th>
                                <td>

                                    <?php if(!empty($satpam['ttd'])): ?>

                                        <img
                                            src="../../uploads/ttd/<?= $satpam['ttd']; ?>"
                                            class="img-thumbnail p-2"
                                            style="height:120px;">

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>

                            </tr>

                        </table>

                        <div class="mt-4">

                            <a
                                href="edit.php?id=<?= $satpam['id_user']; ?>"
                                class="btn btn-warning">

                                <i class="bi bi-pencil-square me-2"></i>

                                Edit

                            </a>

                            <a
                                href="index.php"
                                class="btn btn-secondary">

                                Kembali

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include "../includes/footer.php"; ?>
