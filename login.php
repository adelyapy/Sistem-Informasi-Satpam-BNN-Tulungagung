<?php
require_once "config/session.php";

if (isset($_SESSION['login'], $_SESSION['role'])) {
  $destination = [
    'admin' => 'admin/dashboard/dashboard.php',
    'kepala' => 'kepala/dashboard.php',
    'satpam' => 'satpam/dashboard.php',
  ][$_SESSION['role']] ?? null;

  if ($destination) {
    header("Location: {$destination}");
    exit;
  }
}

require_once "config/database.php";
require_once "config/shift_config.php";

ensureShiftDobel($conn);

$title = "Login";
$base_url = "./";

$users = [];
$shift = [];

$qUser = mysqli_query($conn, "
    SELECT
        id_user,
        nama
    FROM users
    WHERE role='satpam'
    AND status='aktif'
    ORDER BY id_user ASC
");

while ($row = mysqli_fetch_assoc($qUser)) {
  $users[] = $row;
}

$qShift = mysqli_query($conn, "
    SELECT
        id_shift,
        nama_shift,
        jam_mulai,
        jam_selesai
    FROM shift
    ORDER BY jam_mulai
");

while ($row = mysqli_fetch_assoc($qShift)) {
  // Shift gabungan adalah jadwal administrasi, bukan pilihan login.
  // Satpam hanya dapat masuk pada shift yang sedang berlangsung.
  if ($row['nama_shift'] !== 'Shift 1 & 2' && shiftSedangBerlangsung($row)) {
    $shift[] = $row;
  }
}

include "includes/header.php";
?>

<link rel="stylesheet" href="assets/css/login.css">

<main class="login-page min-vh-100 d-flex align-items-center">

  <div class="container">

    <div class="row justify-content-center">

      <div class="col-lg-5 col-md-7">

        <div class="card-app login-card shadow">

          <div class="card-body p-4">

            <div class="text-center mb-4">

              <img
                src="assets/img/logo-esatpam.png"
                class="login-logo mb-3"
                width="90"
                alt="Logo">

              <h3 class="fw-bold mb-1">
                Login
              </h3>

              <p class="text-muted mb-0">
                e-SATPAM — Elektronik Sistem Administrasi Satpam
              </p>

            </div>

            <?php if (!empty($_SESSION['login_error'])) { ?>
              <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_SESSION['login_error']);
                unset($_SESSION['login_error']); ?>
              </div>
            <?php } ?>
            <?php if (!empty($_SESSION['login_success'])) { ?>
              <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($_SESSION['login_success']);
                unset($_SESSION['login_success']); ?>
              </div>
            <?php } ?>

            <form action="login_process.php" method="POST">
              <?= csrf_input() ?>

              <div class="mb-3">

                <label class="form-label">
                  Login Sebagai
                </label>

                <select
                  class="form-select"
                  id="role"
                  name="role"
                  required>

                  <option value="">-- Pilih Role --</option>

                  <option value="admin">
                    Admin
                  </option>

                  <option value="kepala">
                    Kepala BNN
                  </option>

                  <option value="satpam">
                    Satpam
                  </option>

                </select>

              </div>



              <div class="mb-3" id="userGroup" style="display:none;">

                <label class="form-label">
                  User
                </label>

                <select
                  class="form-select"
                  id="id_user"
                  name="id_user">

                  <option value="">
                    -- Pilih Satpam --
                  </option>

                  <?php foreach ($users as $user) { ?>

                    <option value="<?= $user['id_user']; ?>">
                      <?= htmlspecialchars($user['nama']); ?>
                    </option>

                  <?php } ?>

                </select>

              </div>

              <div class="mb-3" id="shiftGroup" style="display:none;">

                <label class="form-label">
                  Shift
                </label>

                <select
                  class="form-select"
                  id="id_shift"
                  name="id_shift">

                  <option value="">
                    -- Pilih Shift --
                  </option>

                  <?php foreach ($shift as $s) { ?>

                    <option value="<?= $s['id_shift']; ?>">

                      <?= htmlspecialchars($s['nama_shift']); ?>
                      (<?= substr($s['jam_mulai'], 0, 5); ?>
                      -
                      <?= substr($s['jam_selesai'], 0, 5); ?>)

                    </option>

                  <?php } ?>

                </select>

                <small class="form-text text-muted">Hanya shift yang sedang berlangsung yang dapat dipilih.</small>

              </div>

              <div class="mb-3" id="passwordGroup" style="display:none;">

                <label class="form-label" for="identifier">
                  Username atau Email
                </label>

                <input
                  type="text"
                  class="form-control mb-3"
                  id="identifier"
                  name="identifier"
                  autocomplete="username"
                  placeholder="Masukkan username atau email">

                <label class="form-label">
                  Password
                </label>

                <input
                  type="password"
                  class="form-control"
                  id="password"
                  name="password"
                  placeholder="Masukkan password">

                <div class="text-end mt-2"><a href="forgot_password.php" class="small">Lupa password?</a></div>

              </div>
              <button
                class="btn btn-primary-app w-100 mt-2"
                type="submit">

                <i class="bi bi-box-arrow-in-right me-2"></i>
                Masuk

              </button>

              <a
                href="index.php"
                class="btn btn-outline-secondary w-100 mt-2">

                <i class="bi bi-arrow-left me-2"></i>
                Kembali

              </a>

            </form>

          </div>

        </div>

      </div>

    </div>

  </div>

</main>

<script>
  const role = document.getElementById('role');
  const user = document.getElementById('id_user');
  const passwordGroup = document.getElementById('passwordGroup');
  const shiftGroup = document.getElementById('shiftGroup');
  const password = document.getElementById('password');
  const identifier = document.getElementById('identifier');
  const shift = document.getElementById('id_shift');

  role.addEventListener('change', function() {

    const userGroup = document.getElementById('userGroup');

    if (role.value === "satpam") {

      userGroup.style.display = "block";
      shiftGroup.style.display = "block";
      passwordGroup.style.display = "none";

      user.required = true;
      shift.required = true;
      password.required = false;
      identifier.required = false;

    } else if (role.value === "admin" || role.value === "kepala") {

      userGroup.style.display = "none";
      shiftGroup.style.display = "none";
      passwordGroup.style.display = "block";

      user.required = false;
      shift.required = false;
      password.required = true;
      identifier.required = true;

    } else {

      userGroup.style.display = "none";
      shiftGroup.style.display = "none";
      passwordGroup.style.display = "none";

      user.required = false;
      shift.required = false;
      password.required = false;
      identifier.required = false;

    }

  });
</script>

<?php include "includes/footer.php"; ?>
