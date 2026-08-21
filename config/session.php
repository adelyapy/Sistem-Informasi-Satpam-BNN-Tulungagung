<?php

// Seluruh proses waktu aplikasi menggunakan WIB, bukan zona waktu bawaan server.
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
  // Jangan bergantung pada C:\xampp\tmp yang dapat memiliki izin tulis berbeda
  // untuk Apache/PHP. Sesi aplikasi disimpan pada folder privat proyek sendiri.
  $sessionPath = dirname(__DIR__) . '/storage/sessions';
  if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0700, true);
  }
  if (is_dir($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
  }

  session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
  ]);
  session_start();
}

// Token dibuat ketika session dimulai dan seluruh POST diperiksa sebelum
// modul aplikasi memproses input apa pun.
csrf_token();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf_token();
}
