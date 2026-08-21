<?php
require_once "../config/satpam_auth.php";
require_once "../config/function.php";

$id_user    = (int) ($_SESSION['id_user'] ?? 0);
$nama       = $_SESSION['nama'] ?? 'Satpam';
$id_laporan = (int) ($_SESSION['id_laporan'] ?? 0);
$activeMenu = 'dashboard';

$query = mysqli_query($conn, "
SELECT
    l.*,
    j.tanggal,
    s.nama_shift,
    s.jam_mulai,
    s.jam_selesai
FROM laporan l
JOIN jadwal_shift j ON l.id_jadwal = j.id_jadwal
JOIN shift s ON j.id_shift = s.id_shift
WHERE l.id_laporan = '$id_laporan'
LIMIT 1
");

$laporan = mysqli_fetch_assoc($query);
$laporanAktif = $laporan !== null;

if (!$laporanAktif) {
  $laporan = [
    'tanggal' => date('Y-m-d'),
    'nama_shift' => 'Belum ada shift',
    'jam_mulai' => '00:00:00',
    'jam_selesai' => '00:00:00',
    'status' => 'draft',
  ];
}

$anggota = mysqli_query($conn, "
SELECT u.nama
FROM anggota_shift a
JOIN users u ON a.id_satpam = u.id_user
WHERE a.id_laporan = '$id_laporan'
ORDER BY u.nama
");

$jumlahAnggota = mysqli_num_rows($anggota);
mysqli_data_seek($anggota, 0);

$hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$namaHari = $hariIndo[(int) date('w', strtotime($laporan['tanggal']))];
$tanggalFmt = formatTanggal($laporan['tanggal']);
$jamMulai = str_replace(':', '.', substr($laporan['jam_mulai'], 0, 5));
$jamSelesai = str_replace(':', '.', substr($laporan['jam_selesai'], 0, 5));

$jumlahInventaris = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventaris WHERE id_laporan = {$id_laporan}"))['total'];
$jumlahUraian = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM uraian_kegiatan WHERE id_laporan = {$id_laporan}"))['total'];
$inventarisSelesai = $jumlahInventaris > 0;
$uraianSelesai = $jumlahUraian > 0;
$progressInventaris = $inventarisSelesai ? 100 : 0;
$progressUraian = $uraianSelesai ? 100 : 0;

switch ($laporan['status']) {
  case 'menunggu_validasi':
    $statusBadge = 'warning';
    $statusLabel = 'Menunggu';
    $statusSub = 'Sudah Dikirim';
    break;
  case 'tervalidasi':
    $statusBadge = 'success';
    $statusLabel = 'Tervalidasi';
    $statusSub = 'Disetujui';
    break;
  default:
    $statusBadge = 'draft';
    $statusLabel = 'Draft';
    $statusSub = 'Belum Dikirim';
    break;
}

$title = "Dashboard Satpam";
$base_url = "../";

include "../includes/header.php";
?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/sidebar.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/dashboard.css">
<?php
include "../includes/satpam_navbar.php";
include "../includes/satpam_sidebar.php";
?>

<div class="main-content satpam-dashboard">

  <div class="dashboard-header row align-items-start mb-4">
    <div class="col-lg-8">
      <h1 class="dashboard-title">DASHBOARD</h1>
      <p class="dashboard-sub">
        Selamat Datang, <strong><?= htmlspecialchars($nama) ?></strong> 👋
      </p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
      <button class="btn btn-tambah" data-bs-toggle="modal" data-bs-target="#modalTambahSatpam">
        <i class="bi bi-plus-lg"></i> Tambah Nama Satpam
      </button>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card card-menu" onclick="location.href='buku_mutasi/inventaris.php'">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="menu-icon-wrap">
            <i class="bi bi-box-seam"></i>
          </div>
          <div class="flex-grow-1">
            <div class="menu-title">INPUT INVENTARIS</div>
            <div class="menu-desc">Catat inventaris yang digunakan</div>
          </div>
          <div class="menu-arrow">
            <i class="bi bi-chevron-right"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-menu" onclick="location.href='buku_mutasi/uraian.php'">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="menu-icon-wrap">
            <i class="bi bi-clipboard2-check"></i>
          </div>
          <div class="flex-grow-1">
            <div class="menu-title">INPUT URAIAN KEGIATAN</div>
            <div class="menu-desc">Catat kegiatan dan lampiran</div>
          </div>
          <div class="menu-arrow">
            <i class="bi bi-chevron-right"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <h5 class="section-title mb-3">Informasi Shift</h5>
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="info-card">
        <div class="info-icon"><i class="bi bi-calendar3"></i></div>
        <div class="info-label">Tanggal</div>
        <div class="info-value"><?= $tanggalFmt ?></div>
        <div class="info-sub"><?= $namaHari ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="info-card">
        <div class="info-icon"><i class="bi bi-clock"></i></div>
        <div class="info-label">Shift</div>
        <div class="info-value"><?= htmlspecialchars($laporan['nama_shift']) ?></div>
        <div class="info-sub"><?= $jamMulai ?> - <?= $jamSelesai ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="info-card">
        <div class="info-icon"><i class="bi bi-people"></i></div>
        <div class="info-label">Anggota Shift</div>
        <div class="info-value"><?= $jumlahAnggota ?> Orang</div>
        <button type="button" class="btn btn-link info-link p-0" data-bs-toggle="modal" data-bs-target="#modalAnggotaShift">
          Lihat Detail
        </button>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="info-card">
        <div class="info-icon"><i class="bi bi-file-earmark-text"></i></div>
        <div class="info-label">Status Laporan</div>
        <span class="badge-status badge-<?= $statusBadge ?>"><?= $statusLabel ?></span>
        <div class="info-sub"><?= $statusSub ?></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card progress-card">
        <div class="card-body">
          <h5 class="section-title mb-4">Progress Laporan</h5>

          <div class="progress-item">
            <div class="d-flex align-items-start gap-3 mb-2">
              <div class="progress-icon"><i class="bi bi-box-seam"></i></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <div class="progress-label">Inventaris</div>
                    <div class="progress-desc">Data inventaris tercatat</div>
                  </div>
                  <span class="badge-selesai <?= $inventarisSelesai ? 'done' : 'pending' ?>">
                    <?= $inventarisSelesai ? 'Selesai' : 'Belum' ?>
                  </span>
                </div>
                <div class="progress mt-2">
                  <div class="progress-bar" style="width: <?= $progressInventaris ?>%"></div>
                </div>
                <div class="progress-percent"><?= $progressInventaris ?>%</div>
              </div>
            </div>
          </div>

          <div class="progress-item mt-4">
            <div class="d-flex align-items-start gap-3 mb-2">
              <div class="progress-icon"><i class="bi bi-clipboard2-check"></i></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <div class="progress-label">Uraian Kegiatan</div>
                    <div class="progress-desc">Uraian kegiatan tercatat</div>
                  </div>
                  <span class="badge-selesai <?= $uraianSelesai ? 'done' : 'pending' ?>">
                    <?= $uraianSelesai ? 'Selesai' : 'Belum' ?>
                  </span>
                </div>
                <div class="progress mt-2">
                  <div class="progress-bar" style="width: <?= $progressUraian ?>%"></div>
                </div>
                <div class="progress-percent"><?= $progressUraian ?>%</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="dashboard-footer text-center mt-5">
    &copy; 2026 BNN Tulungagung - By PKL UNP Kediri
  </footer>

</div>

<div class="modal fade" id="modalTambahSatpam" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="tambah_anggota.php" method="POST">
        <?= csrf_input() ?>
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-person-plus"></i> Tambah Nama Satpam
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <select class="form-select" name="id_satpam" required>
              <option value="">Pilih Nama</option>
              <?php
              $q = mysqli_query($conn, "
                                SELECT id_user, nama
                                FROM users
                                WHERE role='satpam'
                                ORDER BY nama
                            ");
              while ($row = mysqli_fetch_assoc($q)) {
              ?>
                <option value="<?= $row['id_user'] ?>"><?= htmlspecialchars($row['nama']) ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Shift</label>
            <select class="form-select" name="id_shift" required>
              <option value="">Pilih Shift</option>
              <?php
              $q = mysqli_query($conn, "
                                SELECT id_shift, nama_shift
                                FROM shift
                                ORDER BY id_shift
                            ");
              while ($row = mysqli_fetch_assoc($q)) {
              ?>
                <option value="<?= $row['id_shift'] ?>"><?= htmlspecialchars($row['nama_shift']) ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary w-100">Tambah</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAnggotaShift" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-people"></i> Anggota Shift
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if ($jumlahAnggota > 0) { ?>
          <ul class="list-unstyled mb-0 anggota-list">
            <?php while ($row = mysqli_fetch_assoc($anggota)) { ?>
              <li>
                <i class="bi bi-person-check-fill"></i>
                <?= htmlspecialchars($row['nama']) ?>
              </li>
            <?php } ?>
          </ul>
        <?php } else { ?>
          <p class="text-muted mb-0">Belum ada anggota shift terdaftar.</p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
