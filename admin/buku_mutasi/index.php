<?php
require_once "../../config/admin_auth.php";

include "../../includes/header.php";
include "../../includes/admin_sidebar.php";

/*
|--------------------------------------------------------------------------
| Ambil Filter
|--------------------------------------------------------------------------
*/

$tanggal = $_GET['tanggal'] ?? '';
$shift    = $_GET['shift'] ?? '';
$status   = $_GET['status'] ?? '';
$cari     = trim($_GET['cari'] ?? '');

/*
|--------------------------------------------------------------------------
| Statistik Dashboard
|--------------------------------------------------------------------------
*/

$totalLaporan = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM laporan
    ")
)['total'];

$totalDraft = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM laporan
        WHERE status='draft'
    ")
)['total'];

$totalValid = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM laporan
        WHERE status='tervalidasi'
    ")
)['total'];

/*
|--------------------------------------------------------------------------
| Query Filter
|--------------------------------------------------------------------------
*/

$where = [];

if ($tanggal != '') {
    $where[] = "laporan.tanggal_laporan='$tanggal'";
}

if ($shift != '') {
    $where[] = "jadwal_shift.id_shift='$shift'";
}

if ($status != '') {
    $where[] = "laporan.status='$status'";
}

if ($cari != '') {

    $cari = mysqli_real_escape_string($conn,$cari);

    $where[] = "
        (
            users.nama LIKE '%$cari%'
            OR users.kode_satpam LIKE '%$cari%'
        )
    ";

}

$sqlWhere = '';

if(count($where)>0){

    $sqlWhere='WHERE '.implode(' AND ', $where);

}

/*
|--------------------------------------------------------------------------
| Query Laporan
|--------------------------------------------------------------------------
*/

$query = mysqli_query($conn,"

SELECT

    laporan.id_laporan,
    laporan.tanggal_laporan,
    laporan.status,

    users.nama,
    users.kode_satpam,

    jadwal_shift.id_jadwal,

    shift.nama_shift,
    shift.jam_mulai,
    shift.jam_selesai

FROM laporan

LEFT JOIN users
ON users.id_user = laporan.created_by

LEFT JOIN jadwal_shift
ON jadwal_shift.id_jadwal = laporan.id_jadwal

LEFT JOIN shift
ON shift.id_shift = jadwal_shift.id_shift

$sqlWhere

ORDER BY

laporan.tanggal_laporan DESC,

laporan.id_laporan DESC

");

?>

<div class="main bg-light">

    <div class="container-fluid py-4">

        <!-- Header -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-bold mb-1">
                    Laporan Satpam
                </h2>

                <p class="text-muted mb-0">
                    Monitoring seluruh laporan buku mutasi satpam.
                </p>

            </div>

        </div>

        <!-- Statistik -->

        <div class="row g-4 mb-4">

            <div class="col-lg-4">

                <div class="dashboard-card card-blue">

                    <div class="icon blue">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div>

                        <small>Total Laporan</small>

                        <h2><?= $totalLaporan ?></h2>

                    </div>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="dashboard-card card-orange">

                    <div class="icon orange">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div>

                        <small>Draft</small>

                        <h2><?= $totalDraft ?></h2>

                    </div>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="dashboard-card card-green">

                    <div class="icon green">
                        <i class="bi bi-check-circle"></i>
                    </div>

                    <div>

                        <small>Tervalidasi</small>

                        <h2><?= $totalValid ?></h2>

                    </div>

                </div>

            </div>

        </div>

        <div class="dashboard-box">

<form method="GET">

<div class="row g-3">

<div class="col-lg-3">

<input
type="date"
name="tanggal"
class="form-control"
value="<?= htmlspecialchars($tanggal) ?>">

</div>

<div class="col-lg-2">

<select
name="shift"
class="form-select">

<option value="">
Semua Shift
</option>

<?php

$shiftQuery = mysqli_query($conn,"
SELECT *
FROM shift
ORDER BY nama_shift
");

while($s = mysqli_fetch_assoc($shiftQuery)){

?>

<option
value="<?= $s['id_shift'] ?>"
<?= ($shift==$s['id_shift'])?'selected':'' ?>>

<?= htmlspecialchars($s['nama_shift']) ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-lg-2">

<select
name="status"
class="form-select">

<option value="">Semua Status</option>

<option
value="draft"
<?= ($status=="draft")?'selected':'' ?>>
Draft
</option>

<option
value="menunggu_validasi"
<?= ($status=="menunggu_validasi")?'selected':'' ?>>
Menunggu Validasi
</option>

<option
value="tervalidasi"
<?= ($status=="tervalidasi")?'selected':'' ?>>
Tervalidasi
</option>

</select>

</div>

<div class="col-lg-3">

<input

type="text"

name="cari"

class="form-control"

placeholder="Cari nama / kode satpam"

value="<?= htmlspecialchars($cari) ?>">

</div>

<div class="col-lg-2">

<div class="d-flex gap-2">

<button
type="submit"
class="btn btn-primary flex-fill">

<i class="bi bi-search"></i>

Filter

</button>

<a
href="index.php"
class="btn btn-outline-secondary">

Reset

</a>

</div>

</div>

</div>

</form>

</div>

<div class="dashboard-box mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h5 class="mb-0 fw-semibold">
            Daftar Laporan
        </h5>

        <span class="badge bg-primary">
            <?= mysqli_num_rows($query) ?> Data
        </span>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead class="table-light">

                <tr>

                    <th width="60">No</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Satpam</th>
                    <th>Shift</th>
                    <th>Status</th>
                    <th width="140" class="text-center">Aksi</th>

                </tr>

            </thead>

            <tbody>

            <?php if(mysqli_num_rows($query) > 0){ ?>

                <?php $no = 1; ?>

                <?php while($row = mysqli_fetch_assoc($query)){ ?>

                <tr>

                    <td>
                        <?= $no++ ?>
                    </td>

                    <td>

                        <strong>

                            <?= date('d/m/Y', strtotime($row['tanggal_laporan'])) ?>

                        </strong>

                    </td>

                    <td>

                        <span class="badge bg-dark">

                            <?= htmlspecialchars($row['kode_satpam']) ?>

                        </span>

                    </td>

                    <td>

                        <strong>

                            <?= htmlspecialchars($row['nama']) ?>

                        </strong>

                    </td>

                    <td>

                        <strong>

                            <?= htmlspecialchars($row['nama_shift']) ?>

                        </strong>

                        <br>

                        <small class="text-muted">

                            <?= substr($row['jam_mulai'],0,5) ?>

                            -

                            <?= substr($row['jam_selesai'],0,5) ?>

                        </small>

                    </td>

                    <td>

                        <?php

                        switch($row['status']){

                            case 'draft':

                                echo "<span class='badge bg-secondary'>Draft</span>";

                            break;

                            case 'menunggu_validasi':

                                echo "<span class='badge bg-warning text-dark'>Menunggu Validasi</span>";

                            break;

                            case 'tervalidasi':

                                echo "<span class='badge bg-success'>Tervalidasi</span>";

                            break;

                            default:

                                echo "<span class='badge bg-danger'>Unknown</span>";

                            break;

                        }

                        ?>

                    </td>

                    <td class="text-center">

                        <a
                        href="detail.php?id=<?= $row['id_laporan'] ?>"
                        class="btn btn-primary btn-sm"
                        title="Detail">

                            <i class="bi bi-eye"></i>

                        </a>

                        <?php if($row['status']=='tervalidasi'){ ?>

                            <a
                            href="cetak.php?id=<?= $row['id_laporan'] ?>"
                            class="btn btn-success btn-sm"
                            title="Cetak">

                                <i class="bi bi-printer"></i>

                            </a>

                        <?php } ?>

                    </td>

                </tr>

                <?php } ?>

            <?php }else{ ?>

                <tr>

                    <td colspan="7" class="text-center py-5">

                        <i class="bi bi-inbox display-5 d-block mb-3 text-secondary"></i>

                        <h5 class="text-secondary">

                            Tidak ada data laporan

                        </h5>

                        <small class="text-muted">

                            Silakan ubah filter atau tunggu laporan baru.

                        </small>

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

    </div>
</div>

<style>

.dashboard-card{
    border-radius:16px;
    padding:20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    color:#fff;
    transition:.25s;
}

.dashboard-card:hover{
    transform:translateY(-3px);
}

.card-blue{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
}

.card-orange{
    background:linear-gradient(135deg,#f59e0b,#d97706);
}

.card-green{
    background:linear-gradient(135deg,#10b981,#059669);
}

.dashboard-card .icon{
    width:58px;
    height:58px;
    border-radius:14px;
    display:flex;
    justify-content:center;
    align-items:center;
    background:rgba(255,255,255,.18);
    font-size:24px;
}

.dashboard-box{
    background:#fff;
    border-radius:16px;
    padding:24px;
    box-shadow:0 5px 20px rgba(0,0,0,.05);
}

.table th{
    white-space:nowrap;
    font-weight:600;
}

.table td{
    vertical-align:middle;
}

.btn-sm{
    border-radius:10px;
}

.badge{
    padding:8px 12px;
    font-size:12px;
}

</style>

<?php include "../../includes/footer.php"; ?>