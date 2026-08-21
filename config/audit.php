<?php

require_once __DIR__ . '/error_handler.php';

function requestIpAddress(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function logActivity(
    mysqli $conn,
    string $activity,
    string $module,
    ?int $recordId = null,
    ?int $userId = null
): void {
    try {
        $actorId = $userId ?? (isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : null);
        $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
        $ipAddress = requestIpAddress();

        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO audit_logs (user_id, aktivitas, modul, record_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'ississ', $actorId, $activity, $module, $recordId, $ipAddress, $userAgent);
        mysqli_stmt_execute($stmt);
    } catch (Throwable $error) {
        appLog($error);
    }
}
