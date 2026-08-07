<?php
require_once '../../config/database.php';
$id = (int)($_GET['id'] ?? 0);
$qKategori = mysqli_query($conn, "SELECT * FROM kategori_buku_saku WHERE id_kategori='$id'");
if (!mysqli_num_rows($qKategori)) {
  header('Location:materi.php');
  exit;
}
$kategori = mysqli_fetch_assoc($qKategori);
$qMateri = mysqli_query($conn, "SELECT * FROM materi_buku_saku WHERE id_kategori='$id' ORDER BY judul ASC");
$title = $kategori['nama_kategori'];
$base_url = '../../';
include '../../includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading">
      <div>
        <h1><i class="bi bi-journal-text text-primary me-2"></i><?= htmlspecialchars($kategori['nama_kategori']) ?></h1>
        <p>Daftar materi pada kategori ini.</p>
      </div><a href="materi.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
    <div class="row g-4"><?php if (mysqli_num_rows($qMateri)): while ($data = mysqli_fetch_assoc($qMateri)): ?><div class="col-lg-4 col-md-6">
            <article class="public-card material-card h-100">
              <div class="card-body text-center d-flex flex-column align-items-center">
                <div class="public-icon"><i class="bi bi-file-earmark-text"></i></div>
                <h2 class="public-title fs-4"><?= htmlspecialchars($data['judul']) ?></h2><a href="detail.php?id=<?= (int)$data['id_materi'] ?>" class="public-primary mt-auto"><i class="bi bi-book me-1"></i>Baca Materi</a>
              </div>
            </article>
          </div><?php endwhile;
                          else: ?><div class="col-12">
          <div class="content-card text-center p-5"><i class="bi bi-folder2-open fs-1 text-primary"></i>
            <h2 class="public-title mt-3">Belum Ada Materi</h2>
            <p class="public-description">Belum terdapat materi pada kategori ini.</p>
          </div>
        </div><?php endif; ?></div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>