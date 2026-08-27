<?php
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sectionPos = strpos($scriptPath, '/kepala/');
$appBase = $sectionPos === false ? '' : substr($scriptPath, 0, $sectionPos);
$activeMenu = $activeMenu ?? '';
?>
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
        <img src="<?= $appBase ?>/assets/img/logo-esatpam.png" alt="Logo e-SATPAM">
    </div>
    <div class="sidebar-brand-name">e-SATPAM <br>Elektronik Sistem Administrasi Satpam</div>
    <div class="sidebar-brand-unit">BNN TULUNGAGUNG</div>
  </div>
  <ul class="sidebar-menu">
    <li><a class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="<?= $appBase ?>/kepala/dashboard.php"><i class="bi bi-house-door-fill"></i>Dashboard</a></li>
    <li><a class="<?= $activeMenu === 'laporan' ? 'active' : '' ?>" href="<?= $appBase ?>/kepala/validasi/index.php"><i class="bi bi-journal-check"></i>Daftar Laporan</a></li>
    <li><a href="<?= $appBase ?>/logout.php"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
  </ul>
</div>
