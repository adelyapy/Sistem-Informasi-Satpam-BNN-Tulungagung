<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

if (!isset($_GET['id'])) {
  header("Location:index.php");
  exit;
}

$id = (int) $_GET['id'];

$query = mysqli_query($conn, "
SELECT
    m.*,
    k.nama_kategori
FROM materi_buku_saku m
LEFT JOIN kategori_buku_saku k
ON m.id_kategori = k.id_kategori
WHERE m.id_materi='$id'
");

if (mysqli_num_rows($query) == 0) {

  echo "
    <script>
        alert('Data tidak ditemukan');
        location='index.php';
    </script>";
  exit;
}

$data = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="id">

<?php include "../../includes/header.php"; ?>

<body>

  <?php include "../../includes/navbar.php"; ?>

  <div class="container-fluid">

    <div class="row">

      <?php include "../../includes/admin_sidebar.php"; ?>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

        <div class="d-flex justify-content-between align-items-center pt-3 pb-3">

          <h3>

            <i class="bi bi-book-half"></i>

            Detail Materi Buku Saku

          </h3>

          <a href="index.php"
            class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

          </a>

        </div>

        <div class="card shadow">

          <div class="card-body">

            <div class="mb-3">

              <h2>

                <?= htmlspecialchars($data['judul']); ?>

              </h2>

            </div>

            <table class="table table-bordered">

              <tr>

                <th width="180">

                  Kategori

                </th>

                <td>

                  <span class="badge bg-primary">

                    <?= htmlspecialchars($data['nama_kategori']); ?>

                  </span>

                </td>

              </tr>

              <tr>

                <th>

                  Tanggal Dibuat

                </th>

                <td>

                  <?= date('d F Y H:i', strtotime($data['created_at'])); ?>

                </td>

              </tr>

              <?php if (!empty($data['icon'])): ?>

                <tr>

                  <th>

                    Icon

                  </th>

                  <td>

                    <img
                      src="../../uploads/icon_buku_saku/<?= htmlspecialchars($data['icon']); ?>"
                      style="height:80px;">

                  </td>

                </tr>

              <?php endif; ?>

            </table>

            <hr>

            <div class="isi-materi">

              <?= sanitizeRichHtml((string) $data['isi']); ?>

            </div>

          </div>

        </div>

      </main>

    </div>

  </div>

  <?php include "../../includes/footer.php"; ?>

</body>

</html>
