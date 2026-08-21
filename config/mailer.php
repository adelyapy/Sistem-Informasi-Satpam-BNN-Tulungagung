<?php

declare(strict_types=1);

require_once __DIR__ . '/environment.php';

function sendPasswordResetEmail(string $recipientEmail, string $recipientName, string $resetUrl): void
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('Dependensi PHPMailer belum dipasang.');
    }

    require_once $autoload;

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = requireEnvironment('SMTP_HOST');
    $mail->Port = (int) requireEnvironment('SMTP_PORT');
    $mail->SMTPAuth = true;
    $mail->Username = requireEnvironment('SMTP_USERNAME');
    $mail->Password = requireEnvironment('SMTP_PASSWORD');
    $mail->CharSet = 'UTF-8';

    $encryption = strtolower(requireEnvironment('SMTP_ENCRYPTION'));
    $mail->SMTPSecure = $encryption === 'ssl'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom(requireEnvironment('SMTP_FROM_EMAIL'), requireEnvironment('SMTP_FROM_NAME'));
    $mail->addAddress($recipientEmail, $recipientName);
    $mail->isHTML(true);
    $mail->Subject = 'Permintaan Reset Password Buku Mutasi Satpam';
    $mail->Body = '<p>Halo ' . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Klik tautan berikut untuk membuat password baru. Tautan berlaku selama 15 menit dan hanya dapat digunakan sekali.</p>'
        . '<p><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '">Reset Password</a></p>'
        . '<p>Jika Anda tidak meminta reset password, abaikan email ini.</p>';
    $mail->AltBody = "Halo {$recipientName},\n\nGunakan tautan berikut untuk membuat password baru (berlaku 15 menit):\n{$resetUrl}\n\nJika Anda tidak meminta reset password, abaikan email ini.";
    $mail->send();
}
