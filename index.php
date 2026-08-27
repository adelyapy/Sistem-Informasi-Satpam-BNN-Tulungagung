<?php
$title = 'e-SATPAM — Elektronik Sistem Administrasi Satpam';
$base_url = './';

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/landing.css">

<main class="landing-page">
  <section class="landing-hero">
    <div class="container landing-content">
      <div class="landing-intro">
        <img src="assets/img/logo-esatpam.png" alt="Logo e-SATPAM" class="landing-logo">

        <h1>e-SATPAM <br>Elektronik Sistem <br>Administrasi Satpam</h1>
        <h2>BADAN NARKOTIKA NASIONAL</h2>
        <h3>KABUPATEN TULUNGAGUNG</h3>
        <span class="landing-divider" aria-hidden="true"></span>

        <p>
          Sistem Informasi Digital untuk pengelolaan Buku Saku Satpam,<br class="d-none d-md-block">
          Pendataan Inventaris, Uraian Kegiatan, serta Validasi Laporan.
        </p>
      </div>

      <section class="row g-4 justify-content-center landing-menus" aria-label="Menu utama">
        <div class="col-lg-4 col-md-6">
          <a href="satpam/buku_saku/index.php" class="landing-card">
            <span class="landing-card-icon"><i class="bi bi-book"></i></span>
            <h4>Buku Saku Satpam</h4>
            <p>Lihat SOP, buku saku, dan panduan kerja satpam.</p>
            <span class="landing-card-action">Buka <i class="bi bi-arrow-right"></i></span>
          </a>
        </div>

        <div class="col-lg-4 col-md-6">
          <a href="login.php" class="landing-card">
            <span class="landing-card-icon"><i class="bi bi-clipboard2-check"></i></span>
            <h4>e-SATPAM <br>Elektronik Sistem <br>Administrasi Satpam</h4>
            <p>Masuk ke sistem pengelolaan Buku Mutasi.</p>
            <span class="landing-card-action">Masuk <i class="bi bi-arrow-right"></i></span>
          </a>
        </div>

        <div class="col-lg-4 col-md-6">
          <a href="nomor_penting.php" class="landing-card">
            <span class="landing-card-icon"><i class="bi bi-telephone"></i></span>
            <h4>Nomor Penting</h4>
            <p>Daftar nomor darurat, instansi dan layanan penting.</p>
            <span class="landing-card-action">Lihat <i class="bi bi-arrow-right"></i></span>
          </a>
        </div>
      </section>

      <div class="text-center mt-4">
        <a class="landing-about-link" href="tentang.php"><i class="bi bi-info-circle me-1"></i>Tentang Aplikasi</a>
      </div>
    </div>
  </section>

  <footer class="landing-footer">
    <div class="container">
      <p>© 2026 Badan Narkotika Nasional Kabupaten Tulungagung</p>
      <small>Dikembangkan oleh Mahasiswa PKL Universitas Nusantara PGRI Kediri 2026</small>
    </div>
  </footer>
</main>

<?php include 'includes/footer.php'; ?>
