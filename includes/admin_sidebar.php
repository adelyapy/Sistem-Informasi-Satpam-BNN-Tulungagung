<?php
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sectionPos = strpos($scriptPath, '/admin/');
if ($sectionPos === false) {
  $sectionPos = strpos($scriptPath, '/satpam/');
}
$appBase = $sectionPos === false ? '' : substr($scriptPath, 0, $sectionPos);
$activeMenu = $activeMenu ?? '';
?>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header text-center">

    <div class="sidebar-logo">
      <img src="<?= $appBase ?>/assets/img/logo-bnn.png"
        alt="Logo BNN">
    </div>

    <div class="sidebar-brand-name">BUKU MUTASI SATPAM</div>
    <div class="sidebar-brand-unit">BNN TULUNGAGUNG</div>

  </div>

  <ul class="sidebar-menu">

    <li>
      <a class="<?= $activeMenu == 'dashboard' ? 'active' : '' ?>"
        href="<?= $appBase ?>/admin/dashboard/dashboard.php">

        <i class="bi bi-house-door-fill"></i>

        Dashboard

      </a>
    </li>

    <li>
      <a class="<?= $activeMenu == 'monitoring_laporan' ? 'active' : '' ?>"
        href="<?= $appBase ?>/admin/buku_mutasi/index.php">

        <i class="bi bi-file-earmark-text"></i>

        Monitoring Laporan

      </a>
    </li>

    <li>
      <a class="<?= $activeMenu == 'data_satpam' ? 'active' : '' ?>"
        href="<?= $appBase ?>/satpam/index.php">

        <i class="bi bi-people-fill"></i>

        Data Satpam

      </a>
    </li>

    <li>
      <a class="<?= $activeMenu == 'jadwal_satpam' ? 'active' : '' ?>"
        href="<?= $appBase ?>/admin/shift/index.php">

        <i class="bi bi-calendar-week-fill"></i>

        Jadwal Satpam

      </a>
    </li>

    <li>
      <a class="<?= $activeMenu == 'profil_kepala' ? 'active' : '' ?>"
        href="<?= $appBase ?>/admin/profil_kepala.php">

        <i class="bi bi-person-vcard-fill"></i>

        Profil Kepala BNN

      </a>
    </li>

    <li>
      <a class="<?= $activeMenu == 'buku_saku' ? 'active' : '' ?>"
        href="<?= $appBase ?>/admin/buku_saku/index.php">

        <i class="bi bi-book-fill"></i>

        Buku Saku

      </a>
    </li>

    <li>
      <a class="<?= $activeMenu == 'nomor_penting' ? 'active' : '' ?>"
        href="<?= $appBase ?>/admin/nomor_penting/index.php">

        <i class="bi bi-telephone-fill"></i>

        Nomor Penting

      </a>
    </li>

    <li>

      <a href="<?= $appBase ?>/logout.php">

        <i class="bi bi-box-arrow-right"></i>

        Logout

      </a>

    </li>

  </ul>
</div>