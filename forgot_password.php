<?php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/mailer.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    $message = 'Jika alamat email terdaftar, tautan reset password telah dikirim.';

    if ($email !== false) {
        $userStmt = mysqli_prepare($conn, "SELECT id_user, nama, email FROM users WHERE email = ? AND role IN ('admin', 'kepala') AND status = 'aktif' LIMIT 1");
        mysqli_stmt_bind_param($userStmt, 's', $email);
        mysqli_stmt_execute($userStmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));

        if ($user) {
            try {
                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);
                $deleteStmt = mysqli_prepare($conn, 'DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL');
                mysqli_stmt_bind_param($deleteStmt, 'i', $user['id_user']);
                mysqli_stmt_execute($deleteStmt);

                $insertStmt = mysqli_prepare($conn, 'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))');
                mysqli_stmt_bind_param($insertStmt, 'is', $user['id_user'], $tokenHash);
                mysqli_stmt_execute($insertStmt);

                $appUrl = rtrim(requireEnvironment('APP_URL'), '/');
                $resetUrl = $appUrl . '/reset_password.php?token=' . rawurlencode($rawToken);
                sendPasswordResetEmail($user['email'], $user['nama'], $resetUrl);
            } catch (Throwable $error) {
                appLog($error);
            }
        }
    }
}

$title = 'Lupa Password';
$base_url = './';
include 'includes/header.php';
?>

<main class="login-page min-vh-100 d-flex align-items-center">
  <div class="container"><div class="row justify-content-center"><div class="col-lg-5 col-md-7">
    <div class="card-app login-card shadow"><div class="card-body p-4">
      <div class="text-center mb-4"><h3 class="fw-bold mb-1">Lupa Password</h3><p class="text-muted mb-0">Masukkan email akun Admin atau Kepala BNN.</p></div>
      <?php if ($message !== ''): ?><div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_input() ?>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required autocomplete="email" placeholder="nama@instansi.go.id"></div>
        <button class="btn btn-primary-app w-100 mt-2" type="submit">Kirim Tautan Reset</button>
        <a href="login.php" class="btn btn-outline-secondary w-100 mt-2"><i class="bi bi-arrow-left me-2"></i>Kembali ke Login</a>
      </form>
    </div></div>
  </div></div></div>
</main>

<?php include 'includes/footer.php'; ?>
