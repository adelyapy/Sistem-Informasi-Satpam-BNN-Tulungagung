<?php

declare(strict_types=1);

/** Memuat variabel environment sederhana dari file .env tanpa dependensi tambahan. */
function loadEnvironment(string $filePath): void
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        throw new RuntimeException('Konfigurasi environment aplikasi belum tersedia.');
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException('Konfigurasi environment aplikasi tidak dapat dibaca.');
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

function environment(string $name, ?string $default = null): ?string
{
    $value = getenv($name);

    return $value === false ? $default : $value;
}

function requireEnvironment(string $name): string
{
    $value = environment($name);
    if ($value === null) {
        throw new RuntimeException("Variabel environment {$name} belum diatur.");
    }

    return $value;
}

loadEnvironment(dirname(__DIR__) . '/.env');
