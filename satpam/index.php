<?php

require_once "../config/admin_auth.php";
require_once "../config/function.php";

$title = "Data Satpam";
$base_url = "../";
$activeMenu = "data_satpam";

$query = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE role='satpam'
    ORDER BY nama ASC
");

include "../includes/header.php";
?>

<?php include "../includes/navbar.php"; ?>
<?php include "../includes/admin_sidebar.php"; ?>

<div class="main-content">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h3 class="fw-bold mb-1">
                    Data Satpam
                </h3>

                <p class="text-muted mb-0">
                    Kelola data seluruh anggota satpam.
                </p>

            </div>

            <a href="tambah.php" class="btn btn-primary">

                <i class="bi bi-plus-circle me-2"></i>

                Tambah Satpam

            </a>

        </div>

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th width="60">No</th>
                                <th width="80">Foto</th>
                                <th width="130">Kode</th>
                                <th>Nama Satpam</th>
                                <th width="180" class="text-center">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if(mysqli_num_rows($query) > 0): ?>

                            <?php $no=1; ?>

                            <?php while($data=mysqli_fetch_assoc($query)): ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>

                                    <?php if(!empty($data['foto'])): ?>

                                        <img
                                            src="<?= $base_url ?>uploads/foto/<?= $data['foto']; ?>"
                                            width="50"
                                            height="50"
                                            class="rounded-circle border"
                                            style="object-fit:cover;">

                                    <?php else: ?>

                                        <span class="table-avatar">
                                            <i class="bi bi-person-fill"></i>
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <span class="fw-semibold">

                                        <?= htmlspecialchars($data['kode_satpam']); ?>

                                    </span>

                                </td>

                                <td>

                                    <?= htmlspecialchars($data['nama']); ?>

                                </td>

                                <td class="text-center">

                                    <a
                                        href="detail.php?id=<?= $data['id_user']; ?>"
                                        class="btn btn-info btn-sm">

                                        <i class="bi bi-eye"></i>

                                    </a>

                                    <a
                                        href="edit.php?id=<?= $data['id_user']; ?>"
                                        class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="hapusSatpam(<?= $data['id_user']; ?>)">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </td>

                            </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="5" class="text-center py-5">

                                    <i class="bi bi-people fs-1 text-primary d-block mb-3"></i>

                                    <h6 class="fw-semibold">

                                        Belum ada data satpam

                                    </h6>

                                    <p class="text-muted mb-0">

                                        Silakan tambahkan data satpam terlebih dahulu.

                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

function hapusSatpam(id){

    Swal.fire({

        title:'Hapus Data?',

        text:'Data satpam akan dihapus.',

        icon:'warning',

        showCancelButton:true,

        confirmButtonColor:'#dc3545',

        cancelButtonColor:'#6c757d',

        confirmButtonText:'Ya, Hapus',

        cancelButtonText:'Batal'

    }).then((result)=>{

        if(result.isConfirmed){

            window.location='hapus.php?id='+id;

        }

    });

}

</script>

<?php include "../includes/footer.php"; ?>
