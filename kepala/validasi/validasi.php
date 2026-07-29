<?php
session_start();
if(!isset($_SESSION['login'])||$_SESSION['role']!='kepala'){header("Location:../../login.php");exit;}
require "../../config/database.php";
$id=(int)$_GET['id'];
$idUser=$_SESSION['id_user'];
mysqli_query($conn,"
UPDATE laporan
SET status='tervalidasi',
validated_by='$idUser',
validated_at=NOW(),
updated_at=NOW()
WHERE id_laporan='$id'
AND status='menunggu_validasi'
");
echo "<script>alert('Laporan berhasil divalidasi.');window.location='index.php';</script>";
?>