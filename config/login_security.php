<?php

require_once __DIR__ . '/audit.php';

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_MINUTES = 15;

function loginAttemptKey(string $role, string $identifier): string
{
    return hash('sha256', strtolower(trim($role)) . '|' . strtolower(trim($identifier)) . '|' . requestIpAddress());
}

function isLoginRateLimited(mysqli $conn, string $role, string $identifier): bool
{
    $key = loginAttemptKey($role, $identifier);
    $stmt = mysqli_prepare(
        $conn,
        'SELECT COUNT(*) AS total FROM login_attempts WHERE identifier_hash = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    mysqli_stmt_bind_param($stmt, 's', $key);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return (int) ($row['total'] ?? 0) >= LOGIN_MAX_ATTEMPTS;
}

function recordFailedLogin(mysqli $conn, string $role, string $identifier): void
{
    try {
        $key = loginAttemptKey($role, $identifier);
        $ipAddress = requestIpAddress();
        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO login_attempts (identifier_hash, role, ip_address) VALUES (?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'sss', $key, $role, $ipAddress);
        mysqli_stmt_execute($stmt);
        logActivity($conn, 'Login gagal', 'autentikasi');
    } catch (Throwable $error) {
        appLog($error);
    }
}

function clearFailedLoginAttempts(mysqli $conn, string $role, string $identifier): void
{
    try {
        $key = loginAttemptKey($role, $identifier);
        $stmt = mysqli_prepare($conn, 'DELETE FROM login_attempts WHERE identifier_hash = ?');
        mysqli_stmt_bind_param($stmt, 's', $key);
        mysqli_stmt_execute($stmt);
    } catch (Throwable $error) {
        appLog($error);
    }
}
