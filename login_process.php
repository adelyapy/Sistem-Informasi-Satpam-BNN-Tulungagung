<?php
require_once "config/session.php";
require_once "config/database.php";

function loginFailed(string $message): never
{
    $_SESSION['login_error'] = $message;
    header("Location: login.php");
    exit;
}

function createSession(array $user, array $extra = []): void
{
    session_regenerate_id(true);
    $_SESSION = array_merge([
        'login' => true,
        'id_user' => (int) $user['id_user'],
        'nama' => $user['nama'],
        'role' => $user['role'],
    ], $extra);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$role = $_POST['role'] ?? '';
if (!in_array($role, ['admin', 'kepala', 'satpam'], true)) {
    loginFailed('Peran login tidak valid.');
}

if ($role === 'admin' || $role === 'kepala') {
    $password = (string) ($_POST['password'] ?? '');
    if ($password === '') {
        loginFailed('Password wajib diisi.');
    }

    $stmt = mysqli_prepare($conn, "SELECT id_user, nama, role, password FROM users WHERE role = ? AND status = 'aktif' LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $role);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user || empty($user['password']) || !password_verify($password, $user['password'])) {
        loginFailed('Username atau password tidak valid.');
    }

    createSession($user);
    header("Location: " . ($role === 'admin' ? 'admin/dashboard/dashboard.php' : 'kepala/dashboard.php'));
    exit;
}

$idUser = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$idShift = filter_input(INPUT_POST, 'id_shift', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$idUser || !$idShift) {
    loginFailed('Satpam dan shift wajib dipilih.');
}

$tanggal = date('Y-m-d');

try {
    mysqli_begin_transaction($conn);

    $userStmt = mysqli_prepare($conn, "SELECT id_user, nama, role FROM users WHERE id_user = ? AND role = 'satpam' AND status = 'aktif' LIMIT 1");
    mysqli_stmt_bind_param($userStmt, "i", $idUser);
    mysqli_stmt_execute($userStmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));
    if (!$user) {
        throw new RuntimeException('Satpam tidak ditemukan atau tidak aktif.');
    }

    $jadwalStmt = mysqli_prepare($conn, "SELECT id_jadwal FROM jadwal_shift WHERE id_satpam = ? AND id_shift = ? AND tanggal = ? AND status = 'bertugas' LIMIT 1");
    mysqli_stmt_bind_param($jadwalStmt, "iis", $idUser, $idShift, $tanggal);
    mysqli_stmt_execute($jadwalStmt);
    $jadwal = mysqli_fetch_assoc(mysqli_stmt_get_result($jadwalStmt));
    if (!$jadwal) {
        throw new RuntimeException('Tidak ada jadwal bertugas pada shift yang dipilih.');
    }

    $idJadwal = (int) $jadwal['id_jadwal'];
    $laporanStmt = mysqli_prepare($conn, "
        INSERT INTO laporan (id_jadwal, created_by, tanggal_laporan, status, inventaris_selesai, uraian_selesai)
        VALUES (?, ?, ?, 'draft', 0, 0)
        ON DUPLICATE KEY UPDATE id_laporan = LAST_INSERT_ID(id_laporan)
    ");
    mysqli_stmt_bind_param($laporanStmt, "iis", $idJadwal, $idUser, $tanggal);
    mysqli_stmt_execute($laporanStmt);
    $idLaporan = (int) mysqli_insert_id($conn);

    $anggotaStmt = mysqli_prepare($conn, "
        INSERT INTO anggota_shift (id_laporan, id_satpam, status_login, login_at)
        VALUES (?, ?, 'sudah_login', NOW())
        ON DUPLICATE KEY UPDATE status_login = 'sudah_login', login_at = NOW()
    ");
    mysqli_stmt_bind_param($anggotaStmt, "ii", $idLaporan, $idUser);
    mysqli_stmt_execute($anggotaStmt);

    mysqli_commit($conn);
} catch (Throwable $error) {
    mysqli_rollback($conn);
    loginFailed($error->getMessage());
}

createSession($user, [
    'id_shift' => $idShift,
    'id_jadwal' => $idJadwal,
    'id_laporan' => $idLaporan,
]);

header("Location: satpam/dashboard.php");
exit;
