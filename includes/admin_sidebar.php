<?php
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sectionPos = strpos($scriptPath, '/admin/');
$appBase = $sectionPos === false ? '' : substr($scriptPath, 0, $sectionPos);
?>

<div class="sidebar">
    <div class="sidebar-header text-center">
        <img src="<?= $appBase ?>/assets/img/logo-bnn.png" width="70" alt="Logo BNN">
        <h5 class="mt-3 mb-1">Buku Mutasi Satpam</h5>
        <small>ADMIN BNN</small>
    </div>

    <ul class="sidebar-menu">
        <li>
          <a href="<?= $appBase ?>/admin/dashboard/dashboard.php">
            <i class="bi bi-house-door"></i> 
            <span>Dashboard</span>
          </a>
        </li>

        <li>
          <a href="<?= $appBase ?>/satpam/index.php">
            <i class="bi bi-people"></i>
            <span>Data Satpam</span>
          </a>
        </li>

        <li>
          <a href="<?= $appBase ?>/admin/buku_mutasi/index.php">
            <i class="bi bi-journal-text"></i>
            <span>Monitoring Laporan</span>
          </a>
        </li>

        <li>
          <a href="<?= $appBase ?>/admin/nomor_penting/index.php">
            <i class="bi bi-telephone"></i>
            <span>Nomor Penting</span>
          </a>
        </li>

        <li>
            <a href="<?= $appBase ?>/admin/buku_saku/index.php">
                <i class="bi bi-journal-bookmark"></i>
                <span>Buku Saku</span>
            </a>
        </li>

        <li>
          <a href="<?= $appBase ?>/logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
          </a>
        </li>
    </ul>
</div>
