<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

$cari = "";

if (isset($_GET['cari'])) {
  $cari = mysqli_real_escape_string($conn, $_GET['cari']);
}

$query = mysqli_query($conn, "
SELECT
m.*,
k.nama_kategori

FROM materi_buku_saku m

LEFT JOIN kategori_buku_saku k
ON m.id_kategori=k.id_kategori

WHERE
m.judul LIKE '%$cari%'

ORDER BY
m.id_materi ASC
");
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

            <i class="bi bi-book-fill"></i>

            Materi Buku Saku

          </h3>

          <a href="tambah.php" class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>

            Tambah Materi

          </a>

        </div>

        <div class="card shadow">

          <div class="card-body">

            <form method="GET">

              <div class="row mb-3">

                <div class="col-md-4">

                  <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari Judul..."
                    value="<?= $cari; ?>">

                </div>

                <div class="col-md-2">

                  <button class="btn btn-primary w-100">

                    Cari

                  </button>

                </div>

                <div class="col-md-2">

                  <a
                    href="index.php"
                    class="btn btn-secondary w-100">

                    Reset

                  </a>

                </div>

              </div>

            </form>

            <div class="table-responsive">

              <table class="table table-bordered table-hover align-middle">

                <thead class="table-primary">

                  <tr>

                    <th width="60">No</th>

                    <th>Judul</th>

                    <th width="200">Kategori</th>

                    <th width="120">Tanggal</th>

                    <th width="170">Aksi</th>

                  </tr>

                </thead>

                <tbody>

                  <?php

                  $no = 1;

                  while ($d = mysqli_fetch_assoc($query)):

                  ?>

                    <tr>

                      <td class="text-center">

                        <?= $no++; ?>

                      </td>

                      <td>

                        <b>

                          <?= htmlspecialchars($d['judul']); ?>

                        </b>

                      </td>

                      <td>

                        <span class="badge bg-success">

                          <?= htmlspecialchars($d['nama_kategori']); ?>

                        </span>

                      </td>

                      <td>

                        <?= date('d-m-Y', strtotime($d['created_at'])); ?>

                      </td>

                      <td class="text-center">

                        <a
                          href="detail.php?id=<?= $d['id_materi']; ?>"
                          class="btn btn-info btn-sm">

                          <i class="bi bi-eye"></i>

                        </a>

                        <a
                          href="edit.php?id=<?= $d['id_materi']; ?>"
                          class="btn btn-warning btn-sm">

                          <i class="bi bi-pencil"></i>

                        </a>

                        <a
                          href="hapus.php?id=<?= $d['id_materi']; ?>"
                          class="btn btn-danger btn-sm"
                          onclick="return confirm('Hapus materi ini?')">

                          <i class="bi bi-trash"></i>

                        </a>

                      </td>

                    </tr>

                  <?php endwhile; ?>

                  <?php

                  if (mysqli_num_rows($query) == 0):

                  ?>

                    <tr>

                      <td colspan="5" class="text-center">

                        Belum ada data materi.

                      </td>

                    </tr>

                  <?php endif; ?>

                </tbody>

              </table>

            </div>

          </div>

        </div>

      </main>

    </div>

  </div>

  <?php include "../../includes/footer.php"; ?>

</body>

</html>