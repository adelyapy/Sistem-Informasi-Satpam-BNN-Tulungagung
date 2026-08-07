<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (($_SESSION['role'] ?? '') === 'admin') {
  include __DIR__ . '/admin_navbar.php';
  return;
}

if (($_SESSION['role'] ?? '') === 'satpam') {
  include __DIR__ . '/satpam_navbar.php';
  return;
}
?>
<nav class="navbar navbar-satpam shadow-sm">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand text-primary fw-bold" href="<?= $base_url ?>index.php">Buku Mutasi Satpam</a>
    <span class="profile-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></span>
  </div>
</nav>