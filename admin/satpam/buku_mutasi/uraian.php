<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'satpam') {
    header("Location: ../../login.php");
    exit;
}

require "../../config/database.php";

$title = "Uraian Kegiatan";
$base_url = "../../";
include "../../includes/header.php";

$id_user     = $_SESSION['id_user'];
$id_laporan  = $_SESSION['id_laporan'];

if (empty($id_laporan)) {

    header("Location:index.php");
    exit;

}

$q = mysqli_query($conn,"
SELECT
    l.*
FROM laporan l

JOIN anggota_shift a
ON a.id_laporan=l.id_laporan

WHERE

l.id_laporan='$id_laporan'
AND a.id_satpam='$id_user'

LIMIT 1
");

if(mysqli_num_rows($q)==0){
    header("Location:index.php");
    exit;
}

$laporan=mysqli_fetch_assoc($q);

if(isset($_POST['simpan']) && $laporan['status']=='draft'){
    $jam=mysqli_real_escape_string($conn,$_POST['jam']);
    $uraian=mysqli_real_escape_string($conn,$_POST['uraian']);

    $u=mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COALESCE(MAX(urutan),0)+1 urutan
    FROM uraian_kegiatan
    WHERE id_laporan='$id_laporan'"));

    $urutan=$u['urutan'];

    mysqli_query($conn,"
    INSERT INTO uraian_kegiatan
    (

        id_laporan,
        created_by,
        urutan,
        jam,
        uraian

    )

    VALUES
    (

        '$id_laporan',
        '$id_user',
        '$urutan',
        '$jam',
        '$uraian'

    )
    ");

    mysqli_query($conn,"
    UPDATE laporan
    SET uraian_selesai=1
    WHERE id_laporan='$id_laporan'
    ");

    header("Location: uraian.php");
    exit;
}

if(isset($_POST['edit']) && $laporan['status']=='draft'){
    $id=(int)$_POST['id_uraian'];
    $jam=mysqli_real_escape_string($conn,$_POST['jam']);
    $uraian=mysqli_real_escape_string($conn,$_POST['uraian']);

    mysqli_query($conn,"
    UPDATE uraian_kegiatan

    SET

    jam='$jam',
    uraian='$uraian',
    updated_at=NOW()

    WHERE

    id_uraian='$id'
    AND id_laporan='$id_laporan'
    ");

    header("Location: uraian.php");
    exit;
}

if(isset($_GET['hapus']) && $laporan['status']=='draft'){
    $id=(int)$_GET['hapus'];

    mysqli_query($conn,"
    DELETE FROM uraian_kegiatan
    WHERE id_uraian='$id'
    AND id_laporan='$id_laporan'
    ");

    $cek=mysqli_query($conn,"
    SELECT id_uraian
    FROM uraian_kegiatan
    WHERE id_laporan='$id_laporan'");

    if(mysqli_num_rows($cek)==0){
        mysqli_query($conn,"
        UPDATE laporan
        SET uraian_selesai=0
        WHERE id_laporan='$id_laporan'");
    }

    header("Location: uraian.php");
    exit;
}

$data=mysqli_query($conn,"
SELECT

u.*,

us.nama

FROM uraian_kegiatan u

LEFT JOIN users us

ON u.created_by=us.id_user

WHERE

u.id_laporan='$id_laporan'

ORDER BY u.urutan ASC
?>
<div>
  <div class="wrapper">
    <?php include "../../includes/satpam_sidebar.php"; ?>
      <div class="main">

      <div class="container-fluid py-4">

      <div class="d-flex justify-content-between mb-3">

      <h3>Uraian Kegiatan</h3>

      <div>

      <a href="detail.php">Kembali</a>

      <?php if($laporan['status']=='draft'){ ?>
      
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambah">Tambah</button>
    <?php } ?>
  </div>
</div>

<div class="card">
<div class="card-body p-0">
<table class="table table-bordered mb-0">
<thead>
<tr>
<th>No</th>
<th>Jam</th>
<th>Uraian</th>
<th width="140">Aksi</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($data)==0){ ?>
<tr><td colspan="4" class="text-center py-4">Belum ada data.</td></tr>
<?php } while($r=mysqli_fetch_assoc($data)){ ?>
<tr>
<td><?= $r['urutan'] ?></td>
<td><?= htmlspecialchars($r['jam']) ?></td>
<td><?= htmlspecialchars($r['uraian']) ?></td>
<td>

<?= htmlspecialchars($r['nama'] ?? '-') ?>

</td>
<td>
<?php if($laporan['status']=='draft'){ ?>
<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#e<?= $r['id_uraian']?>">Edit</button>
<a class="btn btn-danger btn-sm" onclick="return confirm('Hapus data?')" href="?id=<?= $id_laporan ?>&hapus=<?= $r['id_uraian']?>">Hapus</a>
<?php }else{ ?>
<span class="badge bg-success">Read Only</span>
<?php } ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

<?php if($laporan['status']=='draft'){ ?>
<div class="modal fade" id="tambah"><div class="modal-dialog"><div class="modal-content">
<form method="post">
<div class="modal-header"><h5>Tambah Uraian</h5></div>
<div class="modal-body">
<label>Jam</label>
<input type="time" name="jam" class="form-control mb-3" required>
<label>Uraian</label>
<textarea name="uraian" class="form-control" required></textarea>
</div>
<div class="modal-footer">
<button class="btn btn-primary" name="simpan">Simpan</button>
</div>
</form>
</div></div></div>

<?php
$edit=mysqli_query($conn,SELECT *

FROM uraian_kegiatan

WHERE id_laporan='$id_laporan'

ORDER BY urutan ASC
while($e=mysqli_fetch_assoc($edit)){
?>
<div class="modal fade" id="e<?= $e['id_uraian']?>"><div class="modal-dialog"><div class="modal-content">
<form method="post">
<input type="hidden" name="id_uraian" value="<?= $e['id_uraian'] ?>">
<div class="modal-header"><h5>Edit Uraian</h5></div>
<div class="modal-body">
<label>Jam</label>
<input type="time" name="jam" value="<?= $e['jam'] ?>" class="form-control mb-3" required>
<label>Uraian</label>
<textarea name="uraian" class="form-control" required><?= htmlspecialchars($e['uraian']) ?></textarea>
</div>
<div class="modal-footer">
<button class="btn btn-warning" name="edit">Update</button>
</div>
</form>
</div></div></div>
<?php } } ?>

</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<?php include "../../includes/footer.php"; ?>
