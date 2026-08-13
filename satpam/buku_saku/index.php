<?php
require_once '../../config/database.php';

$title = 'Buku Saku Satpam';
$base_url = '../../';
include '../../includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="public-heading text-center">
      <h1><i class="bi bi-bookshelf text-primary me-2"></i>Buku Saku Satpam</h1>
      <p>Seluruh materi dan dokumen buku saku tersedia dalam format PDF.</p>
    </div>
    <div class="row justify-content-center g-4">
      <div class="col-lg-6">
        <article class="public-card public-choice-card h-100 text-center">
          <div class="card-body d-flex flex-column align-items-center justify-content-center">
            <div class="public-icon pdf"><i class="bi bi-file-earmark-pdf-fill"></i></div>
            <h2 class="public-title">Buku PDF</h2>
            <p class="public-description">Pilih kategori, lalu baca atau unduh PDF untuk setiap materi buku saku.</p>
            <a href="pdf.php" class="public-primary public-danger mt-3"><i class="bi bi-arrow-right-circle me-1"></i>Lihat Buku PDF</a>
          </div>
        </article>
      </div>
    </div>
    <div class="text-center mt-4"><a href="../../index.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda</a></div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>
