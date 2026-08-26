<?php
require_once '../config/admin_auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id) {
  $stmt = mysqli_prepare($conn, "UPDATE users SET status = 'nonaktif' WHERE id_user = ? AND role = 'satpam'");
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
  if (mysqli_stmt_affected_rows($stmt) === 1) {
    logActivity($conn, 'Nonaktifkan user', 'satpam', $id);
  }
}
header('Location: index.php?success=nonaktif');
exit;
