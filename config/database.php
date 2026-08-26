<?php

declare(strict_types=1);

require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/environment.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  /** @var mysqli $conn Koneksi database aplikasi yang digunakan bersama. */
  $conn = mysqli_connect(
    requireEnvironment('DB_HOST'),
    requireEnvironment('DB_USERNAME'),
    requireEnvironment('DB_PASSWORD'),
    requireEnvironment('DB_DATABASE'),
    (int) (environment('DB_PORT', '3306') ?? '3306')
  );
  mysqli_set_charset($conn, 'utf8mb4');
} catch (Throwable $error) {
  appLog($error);
  appSafeErrorResponse();
}
