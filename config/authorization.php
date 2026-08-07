<?php

require_once __DIR__ . '/session.php';

/**
 * Membatasi halaman pada peran yang diizinkan. Path login disediakan oleh
 * halaman pemanggil agar redirect tetap benar pada struktur folder saat ini.
 */
function loginPath(): string
{
  $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
  foreach (['/admin/', '/kepala/', '/satpam/'] as $section) {
    $position = strpos($scriptPath, $section);
    if ($position !== false) {
      return substr($scriptPath, 0, $position) . '/login.php';
    }
  }

  return 'login.php';
}

function requireRole(string $role, ?string $loginPath = null): void
{
  if (!isset($_SESSION['login'], $_SESSION['role']) || $_SESSION['role'] !== $role) {
    $_SESSION = [];
    header('Location: ' . ($loginPath ?? loginPath()));
    exit;
  }
}

/**
 * Satpam hanya boleh membuka laporan shift yang tercatat sebagai anggotanya.
 */
function satpamCanAccessLaporan(mysqli $conn, int $idLaporan, int $idSatpam): bool
{
  $statement = mysqli_prepare($conn, '
        SELECT 1
        FROM anggota_shift
        WHERE id_laporan = ? AND id_satpam = ?
        LIMIT 1
    ');
  mysqli_stmt_bind_param($statement, 'ii', $idLaporan, $idSatpam);
  mysqli_stmt_execute($statement);

  return mysqli_num_rows(mysqli_stmt_get_result($statement)) === 1;
}

function requireSatpamLaporanAccess(mysqli $conn, int $idLaporan, ?string $fallbackPath = null): void
{
  $idSatpam = (int) ($_SESSION['id_user'] ?? 0);
  if ($idLaporan < 1 || !satpamCanAccessLaporan($conn, $idLaporan, $idSatpam)) {
    header('Location: ' . ($fallbackPath ?? 'index.php'));
    exit;
  }
}
