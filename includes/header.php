<?php
require_once __DIR__ . '/../config/session.php';

if (!isset($title)) {
  $title = 'e-SATPAM — Elektronik Sistem Administrasi Satpam';
}

if (!isset($base_url)) {
  $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
  $base_url = '/';
  foreach (['/admin/', '/kepala/', '/satpam/'] as $section) {
    $position = strpos($scriptPath, $section);
    if ($position !== false) {
      $base_url = substr($scriptPath, 0, $position) . '/';
      break;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="e-SATPAM — Elektronik Sistem Administrasi Satpam BNN Kabupaten Tulungagung">
  <meta name="author" content="PKL Universitas Nusantara PGRI Kediri 2026">
  <title><?= htmlspecialchars($title) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
  <link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
  <link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../assets/css/dashboard.css') ?>">
  <link rel="stylesheet" href="<?= $base_url ?>assets/css/public-pages.css">
  <?php if (($_SESSION['role'] ?? '') === 'admin') { ?>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard-admin.css">
  <?php } ?>
</head>

<body>
  <div class="page-wrapper">
