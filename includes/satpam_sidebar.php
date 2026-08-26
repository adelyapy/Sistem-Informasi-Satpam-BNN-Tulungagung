<?php
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sectionPos = strpos($scriptPath, '/satpam/');
$appBase = $sectionPos === false ? '' : substr($scriptPath, 0, $sectionPos);
$activeMenu = $activeMenu ?? '';
?>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header text-center">
    <div class="sidebar-logo" aria-label="Logo BNN">
      <img src="<?= $appBase ?>/assets/img/logo-esatpam.png" alt="Logo e-SATPAM" onerror="this.remove()">
    </div>
    <h5 class="mt-3 mb-1">e-SATPAM — Elektronik Sistem Administrasi Satpam</h5>
    <small>BNN TULUNGAGUNG</small>
  </div>

  <ul class="sidebar-menu">
    <li><a class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="<?= $appBase ?>/satpam/dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
    <li><a class="<?= $activeMenu === 'inventaris' ? 'active' : '' ?>" href="<?= $appBase ?>/satpam/buku_mutasi/inventaris.php"><i class="bi bi-box-seam"></i> Input Inventaris</a></li>
    <li><a class="<?= $activeMenu === 'uraian' ? 'active' : '' ?>" href="<?= $appBase ?>/satpam/buku_mutasi/uraian.php"><i class="bi bi-clipboard2-check"></i> Input Uraian Kegiatan</a></li>
    <li><a class="<?= $activeMenu === 'laporan' ? 'active' : '' ?>" href="<?= $appBase ?>/satpam/buku_mutasi/index.php"><i class="bi bi-journal-text"></i> Daftar Laporan</a></li>
    <li><a href="<?= $appBase ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
  </ul>
</div>
