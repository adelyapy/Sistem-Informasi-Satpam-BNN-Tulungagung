<?php
if (!isset($title)) {
    $title = "Buku Mutasi Satpam";
}

if (!isset($base_url)) {
    $base_url = "./";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= $title; ?></title>

    <meta name="description"
        content="Sistem Informasi Satpam BNN Kabupaten Tulungagung">

    <meta name="author"
        content="PKL Universitas Nusantara PGRI Kediri 2026">

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Google Font -->

    <link rel="preconnect"
        href="https://fonts.googleapis.com">

    <link rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Global CSS -->

    <link rel="stylesheet"
        href="<?= $base_url ?>assets/css/style.css">

</head>

<body>