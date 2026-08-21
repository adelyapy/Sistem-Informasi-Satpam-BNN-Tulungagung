<?php

require_once 'config/session.php';
require_once 'config/database.php';

$error = '';
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['password_confirmation'] ?? '');

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $password)) {
        $error = 'Password minimal 8 karakter dan harus memuat huruf serta angka.';
    } elseif (!hash_equals($password, $confirmPassword)) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $error = 'Tautan reset password tidak valid atau sudah kedaluwarsa.';
    } else {
        $tokenHash = hash('sha256', $token);
        $resetStmt = mysqli_prepare($conn, 'SELECT id, user_id FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
        mysqli_stmt_bind_param($resetStmt, 's', $tokenHash);
        mysqli_stmt_execute($resetStmt);
        $reset = mysqli_fetch_assoc(mysqli_stmt_get_result($resetStmt));

        if (!$reset) {
            $error = 'Tautan reset password tidak valid atau sudah kedaluwarsa.';
        } else {
            try {
                mysqli_begin_transaction($conn);
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $userStmt = mysqli_prepare($conn, 'UPDATE users SET password = ?, session_version = session_version + 1 WHERE id_user = ?');
                mysqli_stmt_bind_param($userStmt, 'si', $passwordHash, $reset['user_id']);
                mysqli_stmt_execute($userStmt);

                $useStmt = mysqli_prepare($conn, 'UPDATE password_resets SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
                mysqli_stmt_bind_param($useStmt, 'i', $reset['id']);
                mysqli_stmt_execute($useStmt);
                mysqli_commit($conn);

                $_SESSION = [];
                session_destroy();
                session_start();
                $_SESSION['login_success'] = 'Password berhasil diperbarui. Silakan login kembali.';
                header('Location: login.php');
                exit;
            } catch (Throwable $exception) {
                mysqli_rollback($conn);
                appLog($exception);
                $error = 'Password tidak dapat diperbarui. Silakan coba kembali.';
            }
        }
    }
}

$title = 'Reset Password';
$base_url = './';
include 'includes/header.php';
?>

<main class="login-page min-vh-100 d-flex align-items-center">
  <div class="container"><div class="row justify-content-center"><div class="col-lg-5 col-md-7">
    <div class="card-app login-card shadow"><div class="card-body p-4">
      <div class="text-center mb-4"><h3 class="fw-bold mb-1">Reset Password</h3><p class="text-muted mb-0">Buat password baru untuk akun Anda.</p></div>
      <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3"><label class="form-label">Password Baru</label><input type="password" class="form-control" name="password" required autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label">Konfirmasi Password Baru</label><input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password"></div>
        <button class="btn btn-primary-app w-100 mt-2" type="submit">Simpan Password Baru</button>
      </form>
    </div></div>
  </div></div></div>
</main>

<?php include 'includes/footer.php'; ?>
