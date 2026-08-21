<?php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/shift_config.php';
require_once 'config/login_security.php';

function loginFailed(string $message, ?string $role = null, ?string $identifier = null): never
{
    global $conn;

    if ($role !== null && $identifier !== null) {
        recordFailedLogin($conn, $role, $identifier);
    }

    $_SESSION['login_error'] = $message;
    header('Location: login.php');
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
        'session_version' => (int) ($user['session_version'] ?? 1),
    ], $extra);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$role = $_POST['role'] ?? '';
if (!in_array($role, ['admin', 'kepala', 'satpam'], true)) {
    loginFailed('Peran login tidak valid.', 'unknown', (string) ($_POST['identifier'] ?? $_POST['id_user'] ?? 'unknown'));
}

if ($role === 'admin' || $role === 'kepala') {
    $identifier = trim((string) ($_POST['identifier'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($identifier === '' || $password === '') {
        loginFailed('Username/email dan password wajib diisi.', $role, $identifier);
    }

    if (isLoginRateLimited($conn, $role, $identifier)) {
        loginFailed('Terlalu banyak percobaan login. Silakan coba kembali 15 menit lagi.');
    }

    $stmt = mysqli_prepare($conn, "SELECT id_user, nama, role, password, session_version FROM users WHERE role = ? AND status = 'aktif' AND (username = ? OR email = ?) LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'sss', $role, $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user || empty($user['password']) || !password_verify($password, $user['password'])) {
        loginFailed('Username atau password tidak valid.', $role, $identifier);
    }

    createSession($user);
    clearFailedLoginAttempts($conn, $role, $identifier);
    logActivity($conn, 'Login berhasil', 'autentikasi', null, (int) $user['id_user']);
    header('Location: ' . ($role === 'admin' ? 'admin/dashboard/dashboard.php' : 'kepala/dashboard.php'));
    exit;
}

$idUser = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$idShift = filter_input(INPUT_POST, 'id_shift', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$idUser || !$idShift) {
    loginFailed('Satpam dan shift wajib dipilih.', 'satpam', (string) ($idUser ?: 'unknown'));
}

if (isLoginRateLimited($conn, 'satpam', (string) $idUser)) {
    loginFailed('Terlalu banyak percobaan login. Silakan coba kembali 15 menit lagi.');
}

$tanggal = date('Y-m-d');

try {
    mysqli_begin_transaction($conn);

    if (!ensureShiftDobel($conn)) {
        throw new RuntimeException('Pilihan shift tidak dapat disiapkan.');
    }

    $userStmt = mysqli_prepare($conn, "SELECT id_user, nama, role, session_version FROM users WHERE id_user = ? AND role = 'satpam' AND status = 'aktif' LIMIT 1");
    mysqli_stmt_bind_param($userStmt, 'i', $idUser);
    mysqli_stmt_execute($userStmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));
    if (!$user) {
        throw new RuntimeException('Satpam tidak ditemukan atau tidak aktif.');
    }

    $shiftStmt = mysqli_prepare($conn, 'SELECT id_shift, nama_shift, jam_mulai, jam_selesai FROM shift WHERE id_shift = ? LIMIT 1');
    mysqli_stmt_bind_param($shiftStmt, 'i', $idShift);
    mysqli_stmt_execute($shiftStmt);
    $shiftTerpilih = mysqli_fetch_assoc(mysqli_stmt_get_result($shiftStmt));
    if (!$shiftTerpilih || $shiftTerpilih['nama_shift'] === 'Shift 1 & 2') {
        throw new RuntimeException('Shift yang dipilih tidak tersedia.');
    }

    if (!shiftSedangBerlangsung($shiftTerpilih)) {
        throw new RuntimeException('Anda hanya dapat login pada jam shift yang sedang berlangsung.');
    }

    $jadwalStmt = mysqli_prepare($conn, "
        INSERT INTO jadwal_shift (id_satpam, id_shift, tanggal, status)
        VALUES (?, ?, ?, 'bertugas')
        ON DUPLICATE KEY UPDATE id_jadwal = LAST_INSERT_ID(id_jadwal)
    ");
    mysqli_stmt_bind_param($jadwalStmt, 'iis', $idUser, $idShift, $tanggal);
    if (!mysqli_stmt_execute($jadwalStmt)) {
        throw new RuntimeException('Jadwal shift tidak dapat disiapkan.');
    }

    $idJadwal = (int) mysqli_insert_id($conn);
    if ($idJadwal < 1) {
        $jadwalCek = mysqli_prepare($conn, 'SELECT id_jadwal FROM jadwal_shift WHERE id_satpam = ? AND id_shift = ? AND tanggal = ? LIMIT 1');
        mysqli_stmt_bind_param($jadwalCek, 'iis', $idUser, $idShift, $tanggal);
        mysqli_stmt_execute($jadwalCek);
        $jadwal = mysqli_fetch_assoc(mysqli_stmt_get_result($jadwalCek));
        $idJadwal = (int) ($jadwal['id_jadwal'] ?? 0);
    }

    if ($idJadwal < 1) {
        throw new RuntimeException('Jadwal shift tidak ditemukan.');
    }

    $laporanStmt = mysqli_prepare($conn, "
        SELECT l.id_laporan
        FROM laporan l
        INNER JOIN jadwal_shift j ON j.id_jadwal = l.id_jadwal
        INNER JOIN anggota_shift a ON a.id_laporan = l.id_laporan
        WHERE l.tanggal_laporan = ? AND j.id_shift = ? AND a.id_satpam = ?
        ORDER BY
            (EXISTS (SELECT 1 FROM inventaris i WHERE i.id_laporan = l.id_laporan)
             + EXISTS (SELECT 1 FROM uraian_kegiatan u WHERE u.id_laporan = l.id_laporan)) DESC,
            l.created_at ASC
        LIMIT 1
    ");
    mysqli_stmt_bind_param($laporanStmt, 'sii', $tanggal, $idShift, $idUser);
    mysqli_stmt_execute($laporanStmt);
    $laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($laporanStmt));

    if ($laporan) {
        $idLaporan = (int) $laporan['id_laporan'];
    } else {
        $laporanStmt = mysqli_prepare($conn, "
            INSERT INTO laporan (id_jadwal, created_by, tanggal_laporan, status, inventaris_selesai, uraian_selesai)
            VALUES (?, ?, ?, 'draft', 0, 0)
        ");
        mysqli_stmt_bind_param($laporanStmt, 'iis', $idJadwal, $idUser, $tanggal);
        mysqli_stmt_execute($laporanStmt);
        $idLaporan = (int) mysqli_insert_id($conn);
    }

    $anggotaStmt = mysqli_prepare($conn, "
        INSERT INTO anggota_shift (id_laporan, id_satpam, status_login, login_at)
        VALUES (?, ?, 'sudah_login', NOW())
        ON DUPLICATE KEY UPDATE status_login = 'sudah_login', login_at = NOW()
    ");
    mysqli_stmt_bind_param($anggotaStmt, 'ii', $idLaporan, $idUser);
    mysqli_stmt_execute($anggotaStmt);

    mysqli_commit($conn);
} catch (Throwable $error) {
    mysqli_rollback($conn);
    appLog($error);
    loginFailed('Login satpam tidak dapat diproses. Silakan periksa pilihan shift.', 'satpam', (string) $idUser);
}

createSession($user, [
    'id_shift' => $idShift,
    'id_jadwal' => $idJadwal,
    'id_laporan' => $idLaporan,
]);

clearFailedLoginAttempts($conn, 'satpam', (string) $idUser);
logActivity($conn, 'Login berhasil', 'autentikasi', null, (int) $user['id_user']);

header('Location: satpam/dashboard.php');
exit;
