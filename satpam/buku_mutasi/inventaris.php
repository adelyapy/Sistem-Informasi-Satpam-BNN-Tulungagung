<?php
require_once "../../config/satpam_auth.php";

$title = "Inventaris";
$base_url = "../../";
include "../../includes/header.php";

$id_user     = (int) $_SESSION['id_user'];
$id_laporan  = (int) ($_GET['id'] ?? $_SESSION['id_laporan'] ?? 0);

if (empty($id_laporan)) {

    header("Location:index.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Ambil Data Laporan
|--------------------------------------------------------------------------
*/

$qLaporan = mysqli_query($conn,"
SELECT
    l.*,
    j.tanggal,
    s.nama_shift
FROM laporan l

JOIN jadwal_shift j
ON l.id_jadwal=j.id_jadwal

JOIN shift s
ON j.id_shift=s.id_shift

JOIN anggota_shift a
ON a.id_laporan=l.id_laporan

WHERE

l.id_laporan='$id_laporan'
AND a.id_satpam='$id_user'

LIMIT 1

");

if(mysqli_num_rows($qLaporan)==0){

    echo "<script>

    alert('Data tidak ditemukan');

    window.location='index.php';

    </script>";

    exit;

}

$laporan=mysqli_fetch_assoc($qLaporan);
$kondisiBarang = [
    'Lengkap berfungsi dengan baik',
    'Lengkap baik',
    'Lengkap',
    'Baik',
];

/*
|--------------------------------------------------------------------------
| Simpan Barang
|--------------------------------------------------------------------------
*/

if(isset($_POST['simpan']) && $laporan['status'] === 'draft'){

    $nama_barang = mysqli_real_escape_string($conn,$_POST['nama_barang']);

    $jumlah = (int)$_POST['jumlah'];

    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!in_array($keterangan, $kondisiBarang, true)) {
        header("Location: inventaris.php?id={$id_laporan}&error=kondisi");
        exit;
    }

    $keterangan = mysqli_real_escape_string($conn, $keterangan);

    $urutan = (int)$_POST['urutan'];

    mysqli_query($conn,"

    INSERT INTO inventaris
    (

        id_laporan,
        created_by,
        urutan,
        nama_barang,
        jumlah,
        keterangan,
        created_at

    )

    VALUES
    (

        '$id_laporan',
        '$id_user',
        '$urutan',
        '$nama_barang',
        '$jumlah',
        '$keterangan',
        NOW()

    )

    ");

    mysqli_query($conn,"
    UPDATE laporan
    SET inventaris_selesai=1
    WHERE id_laporan='$id_laporan'
    ");

    header("Location:inventaris.php");

    exit;

}

/*
|--------------------------------------------------------------------------
| Edit Barang
|--------------------------------------------------------------------------
*/

if(isset($_POST['edit']) && $laporan['status'] === 'draft'){

    $id_inventaris=(int)$_POST['id_inventaris'];

    $nama_barang=mysqli_real_escape_string($conn,$_POST['nama_barang']);

    $jumlah=(int)$_POST['jumlah'];

    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!in_array($keterangan, $kondisiBarang, true)) {
        header("Location: inventaris.php?id={$id_laporan}&error=kondisi");
        exit;
    }

    $keterangan = mysqli_real_escape_string($conn, $keterangan);

    $urutan=(int)$_POST['urutan'];

    mysqli_query($conn,"

    UPDATE inventaris

    SET

    urutan='$urutan',
    nama_barang='$nama_barang',
    jumlah='$jumlah',
    keterangan='$keterangan',
    updated_at=NOW()

    WHERE

    id_inventaris='$id_inventaris'
    AND id_laporan='$id_laporan'

    ");

    header("Location:inventaris.php");

    exit;

}

/*
|--------------------------------------------------------------------------
| Hapus Barang
|--------------------------------------------------------------------------
*/

if(isset($_GET['hapus']) && $laporan['status'] === 'draft'){

    $id=(int)$_GET['hapus'];

    mysqli_query($conn, "
    DELETE FROM inventaris
    WHERE id_inventaris='$id'
    AND id_laporan='$id_laporan'
    ");

    $cek=mysqli_query($conn,"
    SELECT

    i.*,

    u.nama

    FROM inventaris i

    LEFT JOIN users u

    ON i.created_by=u.id_user

    WHERE

    i.id_laporan='$id_laporan'

    ORDER BY i.urutan ASC
    ");

    if(mysqli_num_rows($cek)==0){

        mysqli_query($conn,"
        UPDATE laporan
        SET inventaris_selesai=0
        WHERE id_laporan='$id_laporan'
        ");

    }

    header("Location:inventaris.php");

    exit;

}

/*
|--------------------------------------------------------------------------
| Ambil Inventaris
|--------------------------------------------------------------------------
*/

$qInventaris=mysqli_query($conn,"
SELECT *
FROM inventaris
WHERE id_laporan='$id_laporan'
ORDER BY urutan ASC
");

?>

<div class="wrapper">

<?php include "../../includes/satpam_sidebar.php"; ?>

<div class="main">

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h3 class="fw-bold mb-1">

            Inventaris

        </h3>

        <small class="text-muted">

            Laporan Buku Mutasi

        </small>

    </div>

    <div>

        <a href="detail.php"
            class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

        <?php if($laporan['status']=="draft"){ ?>

            <button
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#modalTambah">

                <i class="bi bi-plus-circle"></i>

                Tambah Barang

            </button>

        <?php } ?>

    </div>

</div>


<div class="card shadow-sm border-0">

    <div class="card-header">

        <strong>

            Daftar Inventaris

        </strong>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th width="60">

                            No

                        </th>

                        <th>

                            Nama Barang

                        </th>

                        <th width="120">

                            Jumlah

                        </th>

                        <th>

                            Keterangan

                        </th>

                        <th width="170"
                            class="text-center">

                            Aksi

                        </th>

                        <th width="180">
                            Input Oleh
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                    if(mysqli_num_rows($qInventaris)==0){

                    ?>

                    <tr>

                        <td colspan="5"
                            class="text-center py-5">

                            <i class="bi bi-box-seam fs-1 text-secondary"></i>

                            <br><br>

                            Belum ada data inventaris.

                        </td>

                    </tr>

                    <?php

                    }else{

                        while($row=mysqli_fetch_assoc($qInventaris)){

                    ?>

                    <tr>

                        <td>

                            <?= $row['urutan']; ?>

                        </td>

                        <td>

                            <strong>

                                <?= htmlspecialchars($row['nama_barang']); ?>

                            </strong>

                        </td>

                        <td>

                            <?= $row['jumlah']; ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['nama'] ?? '-') ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['keterangan']); ?>

                        </td>

                        <td class="text-center">

                            <?php if($laporan['status']=="draft"){ ?>

                            <button

                                class="btn btn-warning btn-sm"

                                data-bs-toggle="modal"

                                data-bs-target="#edit<?= $row['id_inventaris']; ?>">

                                <i class="bi bi-pencil-square"></i>

                            </button>

                            <a

                                href="?id=<?= $id_laporan; ?>&hapus=<?= $row['id_inventaris']; ?>"

                                onclick="return confirm('Hapus barang ini?')"

                                class="btn btn-danger btn-sm">

                                <i class="bi bi-trash"></i>

                            </a>

                            <?php }else{ ?>

                            <button
                                class="btn btn-outline-secondary btn-sm"
                                disabled>

                                <i class="bi bi-eye"></i>

                            </button>

                            <?php } ?>

                        </td>

                    </tr>

                    <?php

                        }

                    }

                    ?>

                </tbody>

            </table>

            <?php if($laporan['status']=="draft"){ ?>

<div class="modal fade"
    id="modalTambah"
    tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title">

                        Tambah Inventaris

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">

                            Urutan

                        </label>

                        <input
                            type="number"
                            name="urutan"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Nama Barang

                        </label>

                        <input
                            type="text"
                            name="nama_barang"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Jumlah

                        </label>

                        <input
                            type="number"
                            name="jumlah"
                            class="form-control"
                            min="1"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Keadaan Barang</label>
                        <select name="keterangan" class="form-select" required>
                            <option value="">Pilih keadaan barang</option>
                            <?php foreach ($kondisiBarang as $kondisi) { ?>
                                <option value="<?= htmlspecialchars($kondisi) ?>"><?= htmlspecialchars($kondisi) ?></option>
                            <?php } ?>
                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-primary">

                        <i class="bi bi-save"></i>

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php } ?>
<?php

if ($laporan['status'] == "draft") {

    $qEdit = mysqli_query($conn, "
    SELECT *
    FROM inventaris
    WHERE id_laporan='$id_laporan'
    ORDER BY urutan ASC
    ");

    while ($edit = mysqli_fetch_assoc($qEdit)) {

?>

<div class="modal fade"
    id="edit<?= $edit['id_inventaris']; ?>"
    tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST">

                <input
                    type="hidden"
                    name="id_inventaris"
                    value="<?= $edit['id_inventaris']; ?>">

                <div class="modal-header">

                    <h5 class="modal-title">

                        Edit Inventaris

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">

                            Urutan

                        </label>

                        <input
                            type="number"
                            name="urutan"
                            class="form-control"
                            value="<?= $edit['urutan']; ?>"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Nama Barang

                        </label>

                        <input
                            type="text"
                            name="nama_barang"
                            class="form-control"
                            value="<?= htmlspecialchars($edit['nama_barang']); ?>"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Jumlah

                        </label>

                        <input
                            type="number"
                            name="jumlah"
                            class="form-control"
                            value="<?= $edit['jumlah']; ?>"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Keadaan Barang</label>
                        <select name="keterangan" class="form-select" required>
                            <?php foreach ($kondisiBarang as $kondisi) { ?>
                                <option value="<?= htmlspecialchars($kondisi) ?>" <?= $edit['keterangan'] === $kondisi ? 'selected' : '' ?>><?= htmlspecialchars($kondisi) ?></option>
                            <?php } ?>
                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        type="submit"
                        name="edit"
                        class="btn btn-warning">

                        <i class="bi bi-save"></i>

                        Update

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php

    }

}

?>

        </div>

    </div>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<?php include "../../includes/footer.php"; ?>

