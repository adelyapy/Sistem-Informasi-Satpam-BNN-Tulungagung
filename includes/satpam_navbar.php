<?php
if (!isset($_SESSION)) {
    session_start();
}

$namaProfil = $_SESSION['nama'] ?? 'Satpam';
$pageTitle = $pageTitle ?? '';
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sectionPos = strpos($scriptPath, '/satpam/');
$appBase = $sectionPos === false ? '..' : substr($scriptPath, 0, $sectionPos);
?>

<nav class="navbar navbar-satpam shadow-sm">
    <div class="container-fluid px-3 px-lg-4">

        <button class="btn btn-menu-toggle" type="button" id="sidebarToggle" aria-label="Buka menu navigasi" aria-controls="sidebar" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>

        <?php if ($pageTitle !== '') { ?>
            <h1 class="navbar-page-title mb-0"><?= htmlspecialchars($pageTitle) ?></h1>
        <?php } else { ?>
            <div class="navbar-brand-wrap mx-auto text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <div class="brand-logo" aria-label="Logo BNN">
                        <img src="<?= $appBase ?>/assets/img/logo-bnn.png" class="logo-bnn" alt="Logo BNN" onerror="this.remove()">
                        <i class="bi bi-shield-fill-check" aria-hidden="true"></i>
                    </div>
                    <div class="text-start">
                        <div class="title-app">BUKU MUTASI SATPAM</div>
                        <div class="subtitle-app">BNN TULUNGAGUNG</div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="navbar-profile d-flex align-items-center">
            <div class="profile-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <span class="profile-name"><?= htmlspecialchars($namaProfil) ?></span>
        </div>

    </div>
</nav>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
