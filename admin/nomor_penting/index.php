<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Nomor Penting";
$base_url = "../../";

$query = mysqli_query($conn, "
    SELECT *
    FROM nomor_penting
    ORDER BY urutan ASC
");

include "../../includes/header.php";

?>

<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/dashboard.css">

<?php include "../../includes/navbar.php"; ?>
<?php include "../../includes/admin_sidebar.php"; ?>

<div class="main-content">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h3 class="fw-bold mb-1">
                    Nomor Penting
                </h3>

                <p class="text-muted mb-0">
                    Kelola daftar nomor penting.
                </p>

            </div>

            <a href="tambah.php" class="btn btn-primary">

                <i class="bi bi-plus-circle me-2"></i>

                Tambah Nomor

            </a>

        </div>

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th width="60">No</th>
                                <th width="90">Urutan</th>
                                <th>Instansi</th>
                                <th width="180">Nomor</th>
                                <th>Keterangan</th>
                                <th width="180" class="text-center">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if(mysqli_num_rows($query)>0): ?>

                            <?php $no=1; ?>

                            <?php while($data=mysqli_fetch_assoc($query)): ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>

                                    <span class="badge bg-primary">

                                        <?= $data['urutan']; ?>

                                    </span>

                                </td>

                                <td>

                                    <?= htmlspecialchars($data['instansi']); ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($data['nomor_telepon']); ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($data['keterangan']); ?>

                                </td>

                                <td class="text-center">

                                    <a
                                        href="detail.php?id=<?= $data['id_nomor']; ?>"
                                        class="btn btn-info btn-sm">

                                        <i class="bi bi-eye"></i>

                                    </a>

                                    <a
                                        href="edit.php?id=<?= $data['id_nomor']; ?>"
                                        class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="hapusNomor(<?= $data['id_nomor']; ?>)">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </td>

                            </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6" class="text-center py-5">

                                    <i class="bi bi-telephone-x fs-1 text-secondary"></i>

                                    <h5 class="mt-3">

                                        Belum ada data nomor penting

                                    </h5>

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

function hapusNomor(id){

    Swal.fire({

        title:'Hapus Data?',

        text:'Data nomor penting akan dihapus.',

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

<?php include "../../includes/footer.php"; ?>