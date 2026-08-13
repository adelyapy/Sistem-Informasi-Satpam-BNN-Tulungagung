<?php
require_once '../../config/database.php';
require_once '../../config/material_pdf.php';

if (!ensureMateriPdfColumns($conn)) {
  exit('PDF materi tidak dapat disiapkan.');
}

$kategori = mysqli_query($conn, '
  SELECT k.id_kategori, k.nama_kategori, COUNT(m.id_materi) AS total_materi
  FROM kategori_buku_saku k
  INNER JOIN materi_buku_saku m ON m.id_kategori = k.id_kategori
  WHERE m.pdf_path IS NOT NULL AND m.pdf_path <> \'\'
  GROUP BY k.id_kategori, k.nama_kategori
  ORDER BY k.nama_kategori ASC
');
$dokumen = mysqli_query($conn, '
  SELECT id_buku, judul, path_file, ukuran_file, created_at
  FROM buku_saku
  ORDER BY created_at DESC
');

$title = 'Buku PDF Satpam';
$base_url = '../../';
include '../../includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading">
      <div><h1><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Buku PDF Satpam</h1><p>Setiap materi tersedia sebagai PDF dan dikelompokkan berdasarkan kategori.</p></div>
      <a href="index.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>

    <h2 class="public-title fs-3 mb-3">Materi Berdasarkan Kategori</h2>
    <div class="row g-4">
      <?php if (mysqli_num_rows($kategori)): ?>
        <?php while ($data = mysqli_fetch_assoc($kategori)): ?>
          <div class="col-lg-4 col-md-6">
            <article class="public-card category-card h-100 text-center">
              <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="public-icon pdf"><i class="bi bi-folder2-open"></i></div>
                <h3 class="public-title fs-4"><?= htmlspecialchars($data['nama_kategori']) ?></h3>
                <p class="public-description mb-3"><?= (int) $data['total_materi'] ?> PDF Materi</p>
                <a class="public-primary mt-auto" href="pdf_materi.php?kategori=<?= (int) $data['id_kategori'] ?>"><i class="bi bi-arrow-right-circle me-1"></i>Lihat PDF</a>
              </div>
            </article>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12"><div class="content-card text-center p-5"><i class="bi bi-file-earmark-x fs-1 text-danger"></i><h2 class="public-title mt-3">PDF Materi Belum Tersedia</h2><p class="public-description">PDF materi belum dibuat.</p></div></div>
      <?php endif; ?>
    </div>

    <?php if (mysqli_num_rows($dokumen)): ?>
      <h2 class="public-title fs-3 mt-5 mb-3">Dokumen PDF Tambahan</h2>
      <div class="row g-4">
        <?php while ($data = mysqli_fetch_assoc($dokumen)): ?>
          <div class="col-lg-4 col-md-6"><article class="public-card book-pdf-card h-100"><div class="card-body text-center d-flex flex-column"><div class="public-icon pdf mx-auto"><i class="bi bi-file-earmark-pdf-fill"></i></div><h3 class="public-title fs-4"><?= htmlspecialchars($data['judul']) ?></h3><p class="file-meta mb-3"><?= number_format((int) $data['ukuran_file'] / 1024, 2) ?> KB<br><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($data['created_at'])) ?></p><a href="baca.php?id=<?= (int) $data['id_buku'] ?>" class="public-primary mt-auto"><i class="bi bi-eye me-1"></i>Baca PDF</a></div></article></div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>
