<?php
require_once '../config/admin_auth.php';
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
  $stmt = mysqli_prepare($conn, "UPDATE users SET status = 'nonaktif' WHERE id_user = ? AND role = 'satpam'");
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
  logActivity($conn, 'Hapus data', 'satpam', $id);
}
header('Location: index.php');
exit;
