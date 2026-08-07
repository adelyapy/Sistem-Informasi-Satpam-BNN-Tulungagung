<?php
require_once 'config/database.php';
$title = 'Nomor Penting';
$base_url = './';
$query = mysqli_query($conn, 'SELECT * FROM nomor_penting ORDER BY urutan ASC, instansi ASC');
$total = mysqli_num_rows($query);
include 'includes/header.php';
?>
<main class="public-page">
  <div class="container public-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 public-heading">
      <div>
        <h1><i class="bi bi-telephone-fill text-primary me-2"></i>Nomor Penting</h1>
        <p>Daftar nomor penting yang dapat dihubungi saat keadaan darurat.</p>
      </div><a href="index.php" class="public-back"><i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda</a>
    </div>
    <div class="row g-4 align-items-stretch mb-4">
      <div class="col-md-4">
        <div class="public-card contact-stat h-100">
          <div class="card-body text-center">
            <div class="small text-muted">Total Nomor Penting</div>
            <div class="display-6 fw-bold text-primary"><?= $total ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="public-search h-100 d-flex align-items-center"><input type="search" id="searchNomor" class="form-control" placeholder="Cari instansi atau layanan..."></div>
      </div>
    </div>
    <div class="row g-4"><?php while ($row = mysqli_fetch_assoc($query)): $nama = strtolower($row['instansi']);
                            $icon = str_contains($nama, 'rumah sakit') || str_contains($nama, 'rs') ? 'bi-hospital-fill' : (str_contains($nama, 'damkar') ? 'bi-fire' : (str_contains($nama, 'pln') ? 'bi-lightning-fill' : (str_contains($nama, 'pol') ? 'bi-shield-fill' : 'bi-telephone-fill'))); ?><div class="col-lg-4 col-md-6 nomor-card">
          <article class="public-card contact-card h-100">
            <div class="card-body text-center d-flex flex-column align-items-center">
              <div class="public-icon"><i class="bi <?= $icon ?>"></i></div>
              <h2 class="public-title fs-4"><?= htmlspecialchars($row['instansi']) ?></h2>
              <div class="contact-number mt-3"><?= htmlspecialchars($row['nomor_telepon']) ?></div>
              <p class="public-description mb-3"><?= htmlspecialchars($row['keterangan']) ?></p><a href="tel:<?= htmlspecialchars($row['nomor_telepon']) ?>" class="public-primary mt-auto w-100"><i class="bi bi-telephone-fill me-1"></i>Hubungi</a>
            </div>
          </article>
        </div><?php endwhile; ?></div>
  </div>
</main>
<script>
  document.getElementById('searchNomor').addEventListener('input', function() {
    const value = this.value.toLowerCase();
    document.querySelectorAll('.nomor-card').forEach(card => card.style.display = card.innerText.toLowerCase().includes(value) ? '' : 'none');
  });
</script>
<?php include 'includes/footer.php'; ?>