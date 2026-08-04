<?php
require_once '../../config/database.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, 'SELECT path_file FROM buku_saku WHERE id_buku = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$buku = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$buku || empty($buku['path_file'])) {
    header('Location: pdf.php');
    exit;
}

header('Location: ../../' . ltrim($buku['path_file'], '/'));
exit;
