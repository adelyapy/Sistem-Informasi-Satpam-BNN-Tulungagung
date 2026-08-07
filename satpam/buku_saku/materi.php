<?php
require_once '../../config/database.php';
$title = 'Materi Buku Saku';
$base_url = '../../';
$query = mysqli_query($conn, 'SELECT * FROM kategori_buku_saku ORDER BY nama_kategori ASC');
include '../../includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading">
      <div>
        <h1><i class="bi bi-journal-bookmark text-primary me-2"></i>Materi Buku Saku Satpam</h1>
        <p>Pilih kategori materi yang ingin dipelajari.</p>
      </div><a href="index.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
    <div class="row g-4"><?php if (mysqli_num_rows($query) > 0): while ($kategori = mysqli_fetch_assoc($query)): $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) jumlah FROM materi_buku_saku WHERE id_kategori='" . (int)$kategori['id_kategori'] . "'")); ?><div class="col-lg-4 col-md-6">
            <article class="public-card category-card h-100 text-center">
              <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="public-icon"><i class="bi bi-journal-bookmark"></i></div>
                <h2 class="public-title fs-4"><?= htmlspecialchars($kategori['nama_kategori']) ?></h2>
                <p class="public-description mb-3"><?= (int)$total['jumlah'] ?> Materi</p><a class="public-primary" href="list.php?id=<?= (int)$kategori['id_kategori'] ?>"><i class="bi bi-arrow-right-circle me-1"></i>Lihat Materi</a>
              </div>
            </article>
          </div><?php endwhile;
                          else: ?><div class="col-12">
          <div class="content-card text-center p-5"><i class="bi bi-folder-x fs-1 text-primary"></i>
            <h2 class="public-title mt-3">Belum Ada Kategori</h2>
            <p class="public-description">Admin belum menambahkan kategori materi.</p>
          </div>
        </div><?php endif; ?></div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>