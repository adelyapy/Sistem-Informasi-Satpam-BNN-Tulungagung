<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Edit Nomor Penting";
$base_url = "../../";

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = (int) $_GET['id'];

$query = mysqli_query($conn, "
    SELECT *
    FROM nomor_penting
    WHERE id_nomor='$id'
");

if (mysqli_num_rows($query) == 0) {
  header("Location: index.php");
  exit;
}

$data = mysqli_fetch_assoc($query);

if (isset($_POST['simpan'])) {

  $instansi      = e($_POST['instansi']);
  $nomorTelepon  = e($_POST['nomor_telepon']);
  $keterangan    = e($_POST['keterangan']);

  $update = mysqli_query($conn, "
        UPDATE nomor_penting
        SET
            instansi='$instansi',
            nomor_telepon='$nomorTelepon',
            keterangan='$keterangan',
            updated_at=NOW()
        WHERE id_nomor='$id'
    ");

  if ($update) {

    echo "

        <script>

        Swal.fire({

            icon:'success',

            title:'Berhasil',

            text:'Data berhasil diperbarui'

        }).then(()=>{

            window.location='index.php';

        });

        </script>

        ";
  } else {

    echo "

        <script>

        Swal.fire({

            icon:'error',

            title:'Gagal',

            text:'Data gagal diperbarui'

        });

        </script>

        ";
  }
}

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

        <h3 class="fw-bold">

          Edit Nomor Penting

        </h3>

        <p class="text-muted mb-0">

          Perbarui data nomor penting.

        </p>

      </div>

      <a href="index.php" class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>

        Kembali

      </a>

    </div>

    <div class="card shadow-sm border-0">

      <div class="card-body">

        <form method="POST">
          <?= csrf_input() ?>

          <div class="row">

            <div class="col-lg-6 mb-3">

              <label class="form-label">

                Instansi

              </label>

              <input
                type="text"
                name="instansi"
                class="form-control"
                value="<?= htmlspecialchars($data['instansi']); ?>"
                required>

            </div>

            <div class="col-lg-6 mb-3">

              <label class="form-label">

                Nomor Telepon

              </label>

              <input
                type="text"
                name="nomor_telepon"
                class="form-control"
                value="<?= htmlspecialchars($data['nomor_telepon']); ?>"
                required>

            </div>

            <div class="col-lg-6 mb-3">

              <label class="form-label">

                Keterangan

              </label>

              <input
                type="text"
                name="keterangan"
                class="form-control"
                value="<?= htmlspecialchars($data['keterangan']); ?>"
                required>

            </div>


            <div class="col-12">

              <button
                type="submit"
                name="simpan"
                class="btn btn-primary">

                <i class="bi bi-check-circle me-2"></i>

                Simpan Perubahan

              </button>

              <a
                href="index.php"
                class="btn btn-secondary">

                Batal

              </a>

            </div>

          </div>

        </form>

      </div>

    </div>

  </div>

</div>

<?php include "../../includes/footer.php"; ?>
