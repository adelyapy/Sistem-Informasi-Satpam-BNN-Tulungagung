<?php
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sectionPos = strpos($scriptPath, '/satpam/');
$appBase = $sectionPos === false ? '' : substr($scriptPath, 0, $sectionPos);
?>

<div class="sidebar">
    <div class="sidebar-header text-center">
        <img src="<?= $appBase ?>/assets/img/logo-bnn.png" width="70" alt="Logo BNN">
        <h5 class="mt-3 mb-1">Buku Mutasi Satpam</h5>
        <small>SATPAM</small>
    </div>

    <ul class="sidebar-menu">
        <li><a href="<?= $appBase ?>/satpam/dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
        <li><a href="<?= $appBase ?>/satpam/buku_mutasi/inventaris.php"><i class="bi bi-box-seam"></i> Input Inventaris</a></li>
        <li><a href="<?= $appBase ?>/satpam/buku_mutasi/uraian.php"><i class="bi bi-clipboard2-check"></i> Input Uraian Kegiatan</a></li>
        <li><a href="<?= $appBase ?>/satpam/buku_mutasi/index.php"><i class="bi bi-journal-text"></i> Daftar Laporan</a></li>
        <li><a href="<?= $appBase ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
</div>
