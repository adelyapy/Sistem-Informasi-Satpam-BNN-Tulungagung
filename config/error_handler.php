<?php

declare(strict_types=1);

/** Mengembalikan mode aplikasi yang didukung: development atau production. */
function appEnvironment(): string
{
    $environment = getenv('APP_ENV');

    // Error handler dimuat sebelum konfigurasi database; baca APP_ENV dari
    // .env secara minimal agar mode development juga berlaku sejak awal.
    if ($environment === false || $environment === '') {
        $environmentFile = dirname(__DIR__) . '/.env';
        if (is_readable($environmentFile)) {
            foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (preg_match('/^\s*APP_ENV\s*=\s*["\']?([^"\'\s#]+)["\']?\s*(?:#.*)?$/i', $line, $match)) {
                    $environment = $match[1];
                    break;
                }
            }
        }
    }

    $environment = strtolower(trim((string) ($environment ?: 'production')));

    return $environment === 'development' ? 'development' : 'production';
}

function appIsDevelopment(): bool
{
    return appEnvironment() === 'development';
}

/** Konfigurasi error aman untuk lingkungan production. */
function appLogPath(): string
{
    $configuredPath = getenv('APP_LOG_PATH') ?: '';
    if ($configuredPath !== '') {
        return $configuredPath;
    }

    return dirname(__DIR__) . '/storage/logs/app.log';
}

function appLog(Throwable|string $error): void
{
    $message = $error instanceof Throwable
        ? sprintf('%s: %s in %s:%d', get_class($error), $error->getMessage(), $error->getFile(), $error->getLine())
        : $error;

    $directory = dirname(appLogPath());
    if (!is_dir($directory)) {
        @mkdir($directory, 0750, true);
    }

    error_log(sprintf("[%s] %s\n", date('c'), $message), 3, appLogPath());
}

function appSafeErrorResponse(): never
{
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo 'Terjadi gangguan pada sistem. Silakan coba kembali beberapa saat lagi.';
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', appIsDevelopment() ? '1' : '0');
ini_set('display_startup_errors', appIsDevelopment() ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', appLogPath());

set_exception_handler(static function (Throwable $error): void {
    appLog($error);

    if (appIsDevelopment()) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }

        echo '<pre>' . htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') . '</pre>';
        exit;
    }

    appSafeErrorResponse();
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    appLog(sprintf('PHP error [%d]: %s in %s:%d', $severity, $message, $file, $line));

    // Development tetap menyerahkan error ke PHP agar detail dapat terlihat lokal.
    return !appIsDevelopment();
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    appLog(sprintf('Fatal PHP error [%d]: %s in %s:%d', $error['type'], $error['message'], $error['file'], $error['line']));
    if (!appIsDevelopment() && !headers_sent()) {
        appSafeErrorResponse();
    }
});
