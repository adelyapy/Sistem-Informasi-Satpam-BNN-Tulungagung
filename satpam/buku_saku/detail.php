<?php
require_once '../../config/database.php';
$id = (int)($_GET['id'] ?? 0);
$query = mysqli_query($conn, "SELECT materi_buku_saku.*,kategori_buku_saku.nama_kategori FROM materi_buku_saku LEFT JOIN kategori_buku_saku ON kategori_buku_saku.id_kategori=materi_buku_saku.id_kategori WHERE id_materi='$id'");
if (!mysqli_num_rows($query)) {
  header('Location:materi.php');
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
        <p>Kategori: <strong><?= htmlspecialchars($data['nama_kategori']) ?></strong></p>
      </div><a href="javascript:history.back()" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
    <article class="content-card">
      <div class="card-body">
        <div class="materi-content"><?= $data['isi'] ?></div>
      </div>
    </article>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>