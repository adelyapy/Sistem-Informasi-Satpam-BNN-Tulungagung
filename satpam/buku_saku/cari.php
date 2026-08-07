<?php
require_once '../../config/database.php';
$title = 'Cari Materi Buku Saku';
$base_url = '../../';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$query = mysqli_query($conn, "SELECT materi_buku_saku.*,kategori_buku_saku.nama_kategori FROM materi_buku_saku LEFT JOIN kategori_buku_saku ON kategori_buku_saku.id_kategori=materi_buku_saku.id_kategori WHERE judul LIKE '%$keyword%' ORDER BY judul ASC");
include '../../includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading">
      <div>
        <h1><i class="bi bi-search text-primary me-2"></i>Pencarian Materi</h1>
        <p>Cari materi berdasarkan judul.</p>
      </div><a href="materi.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
    <form method="get" class="public-search mb-4">
      <div class="row g-2">
        <div class="col-md-8"><input class="form-control" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Masukkan judul materi..." required></div>
        <div class="col-md-4 d-flex gap-2"><button class="public-primary flex-grow-1"><i class="bi bi-search me-1"></i>Cari</button><a href="cari.php" class="public-back">Reset</a></div>
      </div>
    </form>
    <div class="row g-4"><?php if (mysqli_num_rows($query)): while ($data = mysqli_fetch_assoc($query)): ?><div class="col-lg-4 col-md-6">
            <article class="public-card material-card h-100">
              <div class="card-body text-center d-flex flex-column align-items-center">
                <div class="public-icon"><i class="bi bi-file-earmark-text"></i></div>
                <h2 class="public-title fs-4"><?= htmlspecialchars($data['judul']) ?></h2>
                <p class="public-description"><?= htmlspecialchars($data['nama_kategori']) ?></p><a href="detail.php?id=<?= (int)$data['id_materi'] ?>" class="public-primary mt-auto"><i class="bi bi-book me-1"></i>Baca Materi</a>
              </div>
            </article>
          </div><?php endwhile;
                          else: ?><div class="col-12">
          <div class="content-card text-center p-5"><i class="bi bi-search fs-1 text-primary"></i>
            <h2 class="public-title mt-3">Materi Tidak Ditemukan</h2>
            <p class="public-description">Tidak ada materi yang sesuai dengan kata kunci tersebut.</p>
          </div>
        </div><?php endif; ?></div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>