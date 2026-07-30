<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

$result = mysqli_query($conn, "
SELECT *
FROM kategori_buku_saku
ORDER BY id_kategori ASC
");
?>

<!DOCTYPE html>
<html lang="id">

<?php include '../../includes/header.php'; ?>

<body>

<?php include '../../includes/navbar.php'; ?>

<div class="container-fluid">

    <div class="row">

        <?php include '../../includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

                <h3>
                    <i class="bi bi-journal-bookmark-fill"></i>
                    Data Kategori Buku Saku
                </h3>

                <a href="tambah.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Kategori
                </a>

            </div>

            <div class="card shadow">

                <div class="card-body">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-primary">

                            <tr>

                                <th width="60">No</th>

                                <th>Nama Kategori</th>

                                <th width="180">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php
                        $no = 1;

                        while($row = mysqli_fetch_assoc($result)):
                        ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no++; ?>
                                </td>

                                <td>

                                    <i class="bi bi-folder2-open text-warning"></i>

                                    <?= htmlspecialchars($row['nama_kategori']); ?>

                                </td>

                                <td class="text-center">

                                    <a href="edit.php?id=<?= $row['id_kategori']; ?>"
                                       class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                    <a href="hapus.php?id=<?= $row['id_kategori']; ?>"
                                       class="btn btn-danger btn-sm btn-hapus">

                                        <i class="bi bi-trash"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        <?php if(mysqli_num_rows($result)==0): ?>

                            <tr>

                                <td colspan="3" class="text-center">

                                    Belum ada kategori.

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </main>

    </div>

</div>

<?php include '../../includes/footer.php'; ?>

<script>

document.querySelectorAll('.btn-hapus').forEach(function(btn){

    btn.addEventListener('click',function(e){

        if(!confirm('Yakin ingin menghapus kategori ini?')){

            e.preventDefault();

        }

    });

});

</script>

</body>
</html>