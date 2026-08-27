<?php

require_once "../../config/admin_auth.php";
require_once "../../config/function.php";

$title = "Tambah Nomor Penting";
$base_url = "../../";

if (isset($_POST['simpan'])) {

  $instansi       = e($_POST['instansi']);
  $nomor_telepon  = e($_POST['nomor_telepon']);
  $keterangan     = e($_POST['keterangan']);

  $query = mysqli_query($conn, "
        INSERT INTO nomor_penting
        (
            instansi,
            nomor_telepon,
            keterangan
        )
        VALUES
        (
            '$instansi',
            '$nomor_telepon',
            '$keterangan'
        )
    ");

  if ($query) {

    echo "

        <script>

        Swal.fire({

            icon:'success',

            title:'Berhasil',

            text:'Nomor penting berhasil ditambahkan'

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

            text:'Data gagal disimpan'

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

<div class="main-content admin-monitoring-content">

  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

      <div>

        <h3 class="fw-bold">

          Tambah Nomor Penting

        </h3>

        <p class="text-muted mb-0">

          Tambahkan data nomor penting.

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
                required>

            </div>


            <div class="col-12">

              <button
                type="submit"
                name="simpan"
                class="btn btn-primary">

                <i class="bi bi-check-circle me-2"></i>

                Simpan

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
