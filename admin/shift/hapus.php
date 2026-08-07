<?php
require_once '../../config/admin_auth.php';
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
  $stmt = mysqli_prepare($conn, 'DELETE FROM jadwal_shift WHERE id_jadwal = ?');
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
}
header('Location: index.php');
exit;
