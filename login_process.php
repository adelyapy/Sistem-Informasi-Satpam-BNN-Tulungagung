<?php
session_start();

require_once "config/database.php";
require_once "config/function.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit;
}

$role = $_POST['role'] ?? '';

/*==================================================
=
= LOGIN ADMIN / KEPALA
=
==================================================*/

if ($role == "admin" || $role == "kepala") {

    $id_user  = (int)$_POST['id_user'];
    $password = $_POST['password'];

    $query = mysqli_query($conn,"
        SELECT *
        FROM users
        WHERE id_user='$id_user'
        AND role='$role'
        AND status='aktif'
        LIMIT 1
    ");

    if(mysqli_num_rows($query)==0){
        die("<script>
        alert('User tidak ditemukan');
        location='login.php';
        </script>");
    }

    $user=mysqli_fetch_assoc($query);

    if(!password_verify($password,$user['password'])){

        die("<script>
        alert('Password salah');
        location='login.php';
        </script>");

    }

    $_SESSION['login']=true;
    $_SESSION['id_user']=$user['id_user'];
    $_SESSION['nama']=$user['nama'];
    $_SESSION['role']=$user['role'];

    header("Location: dashboard.php");
    exit;
}

/*==================================================
=
= LOGIN SATPAM
=
==================================================*/

$id_user=(int)$_POST['id_user'];
$id_shift=(int)$_POST['id_shift'];
$tanggal=date("Y-m-d");

/*---------------------------------
cek satpam
---------------------------------*/

$qUser=mysqli_query($conn,"
SELECT *
FROM users
WHERE
id_user='$id_user'
AND role='satpam'
AND status='aktif'
LIMIT 1
");

if(mysqli_num_rows($qUser)==0){

    die("<script>
    alert('Data satpam tidak ditemukan');
    location='login.php';
    </script>");

}

$user=mysqli_fetch_assoc($qUser);

/*---------------------------------
cek jadwal
---------------------------------*/

$qJadwal=mysqli_query($conn,"
SELECT *
FROM jadwal_shift
WHERE
id_satpam='$id_user'
AND id_shift='$id_shift'
AND tanggal='$tanggal'
AND status='bertugas'
LIMIT 1
");

if(mysqli_num_rows($qJadwal)==0){

    die("<script>
    alert('Satpam tidak memiliki jadwal pada shift tersebut');
    location='login.php';
    </script>");

}

$jadwal=mysqli_fetch_assoc($qJadwal);

$id_jadwal=$jadwal['id_jadwal'];

/*---------------------------------
cek laporan
---------------------------------*/

$qLaporan=mysqli_query($conn,"
SELECT *
FROM laporan
WHERE id_jadwal='$id_jadwal'
LIMIT 1
");

if(mysqli_num_rows($qLaporan)>0){

    $laporan=mysqli_fetch_assoc($qLaporan);

    $id_laporan=$laporan['id_laporan'];

}else{

    mysqli_query($conn,"
    INSERT INTO laporan
    (

        id_jadwal,
        created_by,
        tanggal_laporan,
        status,
        inventaris_selesai,
        uraian_selesai,
        created_at,
        updated_at

    )

    VALUES
    (

        '$id_jadwal',
        '$id_user',
        '$tanggal',
        'draft',
        0,
        0,
        NOW(),
        NOW()

    )
    ");

    $id_laporan=mysqli_insert_id($conn);

}

/*---------------------------------
anggota shift
---------------------------------*/

$qAnggota=mysqli_query($conn,"
SELECT *
FROM anggota_shift
WHERE
id_laporan='$id_laporan'
AND id_satpam='$id_user'
LIMIT 1
");

if(mysqli_num_rows($qAnggota)==0){

    mysqli_query($conn,"
    INSERT INTO anggota_shift
    (

        id_laporan,
        id_satpam,
        status_login,
        login_at

    )

    VALUES
    (

        '$id_laporan',
        '$id_user',
        'login',
        NOW()

    )
    ");

}else{

    mysqli_query($conn,"
    UPDATE anggota_shift
    SET

        status_login='login',
        login_at=NOW()

    WHERE

        id_laporan='$id_laporan'
        AND id_satpam='$id_user'
    ");

}

/*---------------------------------
session
---------------------------------*/

$_SESSION['login']=true;

$_SESSION['id_user']=$user['id_user'];

$_SESSION['nama']=$user['nama'];

$_SESSION['role']="satpam";

$_SESSION['id_shift']=$id_shift;

$_SESSION['id_jadwal']=$id_jadwal;

$_SESSION['id_laporan']=$id_laporan;

/*---------------------------------*/

header("Location: dashboard.php");
exit;