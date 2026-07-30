<?php
require_once "database.php";

/* =========================
   FUNGSI LAMA
========================= */

function e($string){
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function redirect($url){
    header("Location: $url");
    exit;
}

function formatTanggal($tanggal){
    if(empty($tanggal) || $tanggal=="0000-00-00") return "-";
    $bulan=[1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    $p=explode("-",$tanggal);
    return $p[2]." ".$bulan[(int)$p[1]]." ".$p[0];
}

function formatJam($jam){
    return date("H:i",strtotime($jam));
}

/* =========================
   HELPER SHIFT
========================= */

function getLaporanAktif(mysqli $conn,$idJadwal){
    $q=mysqli_prepare($conn,"
        SELECT *
        FROM laporan
        WHERE id_jadwal=?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($q,"i",$idJadwal);
    mysqli_stmt_execute($q);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($q));
}

function createLaporanShift(mysqli $conn,$idJadwal,$createdBy){
    $tanggal=date("Y-m-d");

    $q=mysqli_prepare($conn,"
        INSERT INTO laporan
        (
            id_jadwal,
            created_by,
            tanggal_laporan
        )
        VALUES
        (?,?,?)
    ");

    mysqli_stmt_bind_param(
        $q,
        "iis",
        $idJadwal,
        $createdBy,
        $tanggal
    );

    mysqli_stmt_execute($q);

    return mysqli_insert_id($conn);
}

function isAnggotaShift(mysqli $conn,$idLaporan,$idSatpam){
    $q=mysqli_prepare($conn,"
        SELECT id_anggota
        FROM anggota_shift
        WHERE id_laporan=?
        AND id_satpam=?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($q,"ii",$idLaporan,$idSatpam);
    mysqli_stmt_execute($q);
    $r=mysqli_stmt_get_result($q);
    return mysqli_num_rows($r)>0;
}

function tambahAnggotaShift(mysqli $conn,$idLaporan,$idSatpam){

    if(isAnggotaShift($conn,$idLaporan,$idSatpam)){
        return true;
    }

    $status="sudah_login";

    $q=mysqli_prepare($conn,"
        INSERT INTO anggota_shift
        (
            id_laporan,
            id_satpam,
            status_login,
            login_at
        )
        VALUES
        (?,?,?,NOW())
    ");

    mysqli_stmt_bind_param(
        $q,
        "iis",
        $idLaporan,
        $idSatpam,
        $status
    );

    return mysqli_stmt_execute($q);
}

function updateLoginAnggota(mysqli $conn,$idLaporan,$idSatpam){

    $status="sudah_login";

    $q=mysqli_prepare($conn,"
        UPDATE anggota_shift
        SET
            status_login=?,
            login_at=NOW()
        WHERE
            id_laporan=?
        AND
            id_satpam=?
    ");

    mysqli_stmt_bind_param(
        $q,
        "sii",
        $status,
        $idLaporan,
        $idSatpam
    );

    return mysqli_stmt_execute($q);
}

function getAnggotaShift(mysqli $conn,$idLaporan){

    $q=mysqli_prepare($conn,"
        SELECT
            u.id_user,
            u.nama
        FROM anggota_shift a
        INNER JOIN users u
            ON u.id_user=a.id_satpam
        WHERE a.id_laporan=?
        ORDER BY u.nama
    ");

    mysqli_stmt_bind_param($q,"i",$idLaporan);
    mysqli_stmt_execute($q);

    return mysqli_stmt_get_result($q);
}

function getNamaAnggotaShift(mysqli $conn,$idLaporan){

    $hasil=[];

    $r=getAnggotaShift($conn,$idLaporan);

    while($d=mysqli_fetch_assoc($r)){
        $hasil[]=$d["nama"];
    }

    return $hasil;
}
