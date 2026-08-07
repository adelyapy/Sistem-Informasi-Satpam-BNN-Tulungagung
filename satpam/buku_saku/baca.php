<?php
require_once '../../config/database.php';
$id = (int)($_GET['id'] ?? 0);
$query = mysqli_query($conn, "SELECT buku_saku.*,users.nama FROM buku_saku LEFT JOIN users ON users.id_user=buku_saku.uploaded_by WHERE buku_saku.id_buku='$id' LIMIT 1");
if (!mysqli_num_rows($query)) {
  header('Location: pdf.php');
  exit;
}
$data = mysqli_fetch_assoc($query);
$title = $data['judul'];
$base_url = '../../';
include '../../includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading">
      <div>
        <h1><?= htmlspecialchars($data['judul']) ?></h1>
        <p>Buku PDF Satpam</p>
      </div>
      <div class="d-flex gap-2"><a href="pdf.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a><a href="../../<?= htmlspecialchars($data['path_file']) ?>" target="_blank" download class="btn btn-success rounded-3 fw-bold"><i class="bi bi-download me-1"></i>Download</a></div>
    </div>
    <div class="row g-4">
      <div class="col-lg-3">
        <aside class="content-card">
          <div class="card-body">
            <div class="public-icon pdf mx-auto"><i class="bi bi-file-earmark-pdf-fill"></i></div>
            <div class="small text-muted">Uploader</div>
            <div class="fw-bold mb-3"><?= htmlspecialchars($data['nama'] ?: 'Administrator') ?></div>
            <div class="small text-muted">Ukuran</div>
            <div class="fw-bold mb-3"><?= number_format($data['ukuran_file'] / 1024, 2) ?> KB</div>
            <div class="small text-muted">Tanggal upload</div>
            <div class="fw-bold"><?= date('d F Y', strtotime($data['created_at'])) ?></div>
          </div>
        </aside>
      </div>
      <div class="col-lg-9">
        <section class="content-card overflow-hidden">
          <div class="card-body border-bottom fw-bold">Preview Buku Saku</div><iframe class="pdf-preview" src="../../<?= htmlspecialchars($data['path_file']) ?>" title="<?= htmlspecialchars($data['judul']) ?>"></iframe>
        </section>
      </div>
    </div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>