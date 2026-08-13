<?php
require_once '../../config/database.php';
require_once '../../config/material_pdf.php';

if (!ensureMateriPdfColumns($conn)) {
  exit('PDF materi tidak dapat disiapkan.');
}

$idMateri = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, '
  SELECT m.id_materi, m.judul, m.pdf_path, m.pdf_size, m.pdf_generated_at, k.id_kategori, k.nama_kategori
  FROM materi_buku_saku m
  INNER JOIN kategori_buku_saku k ON k.id_kategori = m.id_kategori
  WHERE m.id_materi = ?
  LIMIT 1
');
mysqli_stmt_bind_param($stmt, 'i', $idMateri);
mysqli_stmt_execute($stmt);
$materi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$materi || !pdfMateriTersedia($materi)) {
  header('Location: pdf.php');
  exit;
}

$title = $materi['judul'];
$base_url = '../../';
include '../../includes/header.php';
?>
<main class="public-page"><div class="container public-shell"><div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading"><div><h1><?= htmlspecialchars($materi['judul']) ?></h1><p>Kategori: <?= htmlspecialchars($materi['nama_kategori']) ?></p></div><div class="d-flex gap-2 public-heading-actions"><a href="pdf_materi.php?kategori=<?= (int) $materi['id_kategori'] ?>" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a><a href="../../<?= htmlspecialchars($materi['pdf_path']) ?>" download class="btn btn-success rounded-3 fw-bold"><i class="bi bi-download me-1"></i>Download PDF</a></div></div><section class="content-card overflow-hidden"><div class="card-body border-bottom fw-bold">Preview PDF Materi</div><iframe class="pdf-preview" src="../../<?= htmlspecialchars($materi['pdf_path']) ?>" title="<?= htmlspecialchars($materi['judul']) ?>"></iframe></section></div></main>
<?php include '../../includes/footer.php'; ?>
