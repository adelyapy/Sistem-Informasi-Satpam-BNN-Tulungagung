<?php
$title = 'Tentang Kami';
$base_url = './';

/**
 * Foto dapat diganti tanpa mengubah kode: simpan file di assets/img dengan
 * nama contact-adelya atau contact-septia (JPG, JPEG, PNG, atau WEBP).
 */
function renderContactPersonAvatar(string $namaFile, string $nama): string
{
  foreach (['jpg', 'jpeg', 'png', 'webp'] as $ekstensi) {
    $pathRelatif = 'assets/img/' . $namaFile . '.' . $ekstensi;
    if (is_file(__DIR__ . '/' . $pathRelatif)) {
      return '<img src="' . htmlspecialchars($pathRelatif, ENT_QUOTES, 'UTF-8') . '" alt="Foto ' . htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') . '">';
    }
  }

  return '<i class="bi bi-person-fill" aria-hidden="true"></i>';
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/landing.css">
<link rel="stylesheet" href="assets/css/tentang.css?v=<?= filemtime(__DIR__ . '/assets/css/tentang.css') ?>">

<main class="about-page">
  <div class="container about-container">
    <section class="about-card" aria-labelledby="about-title">
      <img src="assets/img/logo-Esatpam.png" alt="Logo e-SATPAM" class="about-logo">

      <div class="about-title-row">
        <span class="about-info-icon" aria-hidden="true"><i class="bi bi-info-circle"></i></span>
        <h1 id="about-title">Tentang Kami</h1>
      </div>
      <h2 class="about-lead">e-SATPAM <br>Elektronik Sistem Administrasi Satpam BNN Kabupaten Tulungagung</h2>
      <span class="about-divider" aria-hidden="true"></span>

      <p class="about-description">Aplikasi ini dikembangkan sebagai sistem informasi untuk <br>membantu pencatatan inventaris, uraian kegiatan, pengelolaan buku saku, <br>serta proses validasi laporan Satpam.</p>

      <section class="about-project" aria-label="Informasi proyek">
        <img src="assets/img/logo-unp-kediri.png" alt="Logo Universitas Nusantara PGRI Kediri" class="about-unp-logo">
        <p>Proyek ini merupakan karya Mahasiswa PKL<br>Universitas Nusantara PGRI Kediri tahun 2026</p>
      </section>

      <section class="contact-section" aria-labelledby="contact-title">
        <div class="contact-heading">
          <span aria-hidden="true"></span>
          <h2 id="contact-title"><i class="bi bi-people"></i> Contact Person</h2>
          <span aria-hidden="true"></span>
        </div>
        <div class="row g-4 justify-content-center contact-list">
          <div class="col-md-6">
            <article class="contact-card">
              <div class="contact-avatar" aria-label="Foto Adelya Putri Yunita"><?= renderContactPersonAvatar('contact-adelya', 'Adelya Putri Yunita') ?></div>
              <div class="contact-content">
                <h3>Adelya Putri Yunita</h3>
                <p><i class="bi bi-telephone-fill"></i>0877 7787 5869</p>
                <p><i class="bi bi-envelope-fill"></i>adelyapy23@gmail.com</p>
              </div>
            </article>
          </div>
          <div class="col-md-6">
            <article class="contact-card">
              <div class="contact-avatar" aria-label="Foto Septia Amanda Aulia"><?= renderContactPersonAvatar('contact-septia', 'Septia Amanda Aulia') ?></div>
              <div class="contact-content">
                <h3>Septia Amanda Aulia</h3>
                <p><i class="bi bi-telephone-fill"></i>0821 4339 5284</p>
                <p><i class="bi bi-envelope-fill"></i>septiaamandaaulia01@gmail.com</p>
              </div>
            </article>
          </div>
        </div>
      </section>

      <a href="index.php" class="landing-card-action about-back"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
    </section>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
