<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(?string $token = null): void
{
    $submittedToken = $token ?? ($_POST['_csrf_token'] ?? '');
    $storedToken = $_SESSION['_csrf_token'] ?? '';

    if (!is_string($submittedToken) || !is_string($storedToken) || $storedToken === '' || !hash_equals($storedToken, $submittedToken)) {
        http_response_code(419);
        echo 'Permintaan tidak dapat diproses. Silakan muat ulang halaman dan coba kembali.';
        exit;
    }
}

