<?php
require_once '../../config/database.php';
$title = 'Buku PDF Satpam';
$base_url = '../../';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$sql = "SELECT buku_saku.*,users.nama FROM buku_saku LEFT JOIN users ON users.id_user=buku_saku.uploaded_by";
if ($keyword !== '') {
  $sql .= " WHERE judul LIKE '%$keyword%'";
}
$sql .= ' ORDER BY created_at DESC';
$query = mysqli_query($conn, $sql);
include '../../includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading">
      <div>
        <h1><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Buku PDF Satpam</h1>
        <p>Panduan, SOP, dan dokumen resmi yang dapat dipelajari anggota satpam.</p>
      </div><a href="index.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
    <form method="get" class="public-search mb-4">
      <div class="row g-2">
        <div class="col-md-8"><input class="form-control" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari buku..."></div>
        <div class="col-md-4"><button class="public-primary w-100"><i class="bi bi-search me-1"></i>Cari</button></div>
      </div>
    </form>
    <div class="row g-4"><?php if (mysqli_num_rows($query)): while ($data = mysqli_fetch_assoc($query)): ?><div class="col-lg-4 col-md-6">
            <article class="public-card book-pdf-card h-100">
              <div class="card-body text-center d-flex flex-column">
                <div class="public-icon pdf mx-auto"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                <h2 class="public-title fs-4"><?= htmlspecialchars($data['judul']) ?></h2>
                <p class="public-description">PDF Document</p>
                <p class="file-meta mb-3">Ukuran: <?= number_format($data['ukuran_file'] / 1024, 2) ?> KB<br><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($data['created_at'])) ?></p>
                <div class="mt-auto d-grid gap-2"><a href="baca.php?id=<?= (int)$data['id_buku'] ?>" class="public-primary"><i class="bi bi-eye me-1"></i>Baca Buku</a><a href="../../<?= htmlspecialchars($data['path_file']) ?>" target="_blank" download class="btn btn-success rounded-3 fw-bold"><i class="bi bi-download me-1"></i>Download</a></div>
              </div>
            </article>
          </div><?php endwhile;
                          else: ?><div class="col-12">
          <div class="content-card text-center p-5"><i class="bi bi-file-earmark-x fs-1 text-danger"></i>
            <h2 class="public-title mt-3">Belum Ada Buku Saku</h2>
            <p class="public-description">Admin belum mengunggah buku PDF.</p>
          </div>
        </div><?php endif; ?></div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>