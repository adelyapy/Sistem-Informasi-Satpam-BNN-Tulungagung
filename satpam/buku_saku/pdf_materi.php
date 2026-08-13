<?php
require_once '../../config/database.php';
require_once '../../config/material_pdf.php';

if (!ensureMateriPdfColumns($conn)) {
  exit('PDF materi tidak dapat disiapkan.');
}

if (!materiBukuSakuTableExists($conn)) {
  header('Location: pdf.php');
  exit;
}

$idKategori = (int) ($_GET['kategori'] ?? 0);
$kategoriStmt = mysqli_prepare($conn, 'SELECT id_kategori, nama_kategori FROM kategori_buku_saku WHERE id_kategori = ? LIMIT 1');
mysqli_stmt_bind_param($kategoriStmt, 'i', $idKategori);
mysqli_stmt_execute($kategoriStmt);
$kategori = mysqli_fetch_assoc(mysqli_stmt_get_result($kategoriStmt));
if (!$kategori) {
  header('Location: pdf.php');
  exit;
}

$materiStmt = mysqli_prepare($conn, 'SELECT id_materi, judul, pdf_path, pdf_size, pdf_generated_at FROM materi_buku_saku WHERE id_kategori = ? AND pdf_path IS NOT NULL AND pdf_path <> \'\' ORDER BY judul ASC');
mysqli_stmt_bind_param($materiStmt, 'i', $idKategori);
mysqli_stmt_execute($materiStmt);
$materi = mysqli_fetch_all(mysqli_stmt_get_result($materiStmt), MYSQLI_ASSOC);
$materi = array_values(array_filter($materi, 'pdfMateriTersedia'));

$title = $kategori['nama_kategori'] . ' - PDF';
$base_url = '../../';
include '../../includes/header.php';
?>
<main class="public-page"><div class="container public-shell"><div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading"><div><h1><i class="bi bi-folder2-open text-danger me-2"></i><?= htmlspecialchars($kategori['nama_kategori']) ?></h1><p>PDF materi dalam kategori ini.</p></div><a href="pdf.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali ke Kategori</a></div><div class="row g-4"><?php if ($materi): ?><?php foreach ($materi as $data): ?><div class="col-lg-4 col-md-6"><article class="public-card book-pdf-card h-100"><div class="card-body text-center d-flex flex-column"><div class="public-icon pdf mx-auto"><i class="bi bi-file-earmark-pdf-fill"></i></div><h2 class="public-title fs-4"><?= htmlspecialchars($data['judul']) ?></h2><p class="file-meta mb-3">PDF &bull; <?= number_format((int) $data['pdf_size'] / 1024, 2) ?> KB</p><a href="baca_materi_pdf.php?id=<?= (int) $data['id_materi'] ?>" class="public-primary mt-auto"><i class="bi bi-eye me-1"></i>Baca PDF</a></div></article></div><?php endforeach; ?><?php else: ?><div class="col-12"><div class="content-card text-center p-5"><i class="bi bi-file-earmark-x fs-1 text-danger"></i><h2 class="public-title mt-3">Belum Ada PDF Materi</h2><p class="public-description">PDF untuk kategori ini belum tersedia.</p></div></div><?php endif; ?></div></div></main>
<?php include '../../includes/footer.php'; ?>
