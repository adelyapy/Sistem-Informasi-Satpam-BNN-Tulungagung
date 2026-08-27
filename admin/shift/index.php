<?php
require_once "../../config/admin_auth.php";
require_once "../../config/shift_config.php";

ensureShiftDobel($conn);

$title = "Jadwal Satpam";
$base_url = "../../";
$activeMenu = "jadwal_satpam";

$tanggal = $_GET['tanggal'] ?? '';
$idShift = (int) ($_GET['shift'] ?? 0);
$where = [];
if ($tanggal !== '') {
  $where[] = "js.tanggal='" . mysqli_real_escape_string($conn, $tanggal) . "'";
}
if ($idShift > 0) {
  $where[] = "js.id_shift=$idShift";
}
$filterSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$tanggalCetak = $tanggal !== '' ? $tanggal : date('Y-m-d');
$parameterCetak = ['tanggal' => $tanggalCetak];
if ($idShift > 0) {
  $parameterCetak['shift'] = $idShift;
}

$jadwal = mysqli_query($conn, "SELECT js.*, u.nama, u.kode_satpam, s.nama_shift, s.jam_mulai, s.jam_selesai
    FROM jadwal_shift js
    JOIN users u ON u.id_user=js.id_satpam
    JOIN shift s ON s.id_shift=js.id_shift
    $filterSql
    ORDER BY js.tanggal DESC, s.jam_mulai ASC, u.nama ASC");
$daftarShift = mysqli_query($conn, "SELECT * FROM shift ORDER BY jam_mulai ASC");

include "../../includes/header.php";
include "../../includes/admin_navbar.php";
include "../../includes/admin_sidebar.php";
?>

<main class="main-content">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Jadwal Satpam</h2>
        <p class="text-muted mb-0">Atur penugasan shift harian untuk setiap anggota satpam.</p>
      </div>
      <div class="d-flex flex-wrap gap-2 justify-content-end">
        <div class="btn-group" role="group" aria-label="Pilihan cetak jadwal shift">
          <a href="cetak.php?<?= htmlspecialchars(http_build_query(array_merge($parameterCetak, ['periode' => 'harian']))) ?>" class="btn btn-outline-primary"><i class="bi bi-printer me-1"></i>Cetak Harian</a>
          <a href="cetak.php?<?= htmlspecialchars(http_build_query(array_merge($parameterCetak, ['periode' => 'mingguan']))) ?>" class="btn btn-outline-primary">Mingguan</a>
          <a href="cetak.php?<?= htmlspecialchars(http_build_query(array_merge($parameterCetak, ['periode' => 'bulanan']))) ?>" class="btn btn-outline-primary">Bulanan</a>
        </div>
        <a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Tambah Jadwal</a>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body">
        <form class="row g-3 align-items-end" method="get">
          <div class="col-md-4">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Shift</label>
            <select name="shift" class="form-select">
              <option value="">Semua Shift</option>
              <?php mysqli_data_seek($daftarShift, 0);
              while ($shift = mysqli_fetch_assoc($daftarShift)): ?>
                <option value="<?= $shift['id_shift'] ?>" <?= $idShift === (int) $shift['id_shift'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($shift['nama_shift']) ?> (<?= substr($shift['jam_mulai'], 0, 5) ?> - <?= substr($shift['jam_selesai'], 0, 5) ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-search me-2"></i>Tampilkan</button>
            <a href="index.php" class="btn btn-outline-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Satpam</th>
                <th>Shift</th>
                <th>Status</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($jadwal) > 0): $no = 1;
                while ($row = mysqli_fetch_assoc($jadwal)): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td class="fw-semibold"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></div><small class="text-muted"><?= htmlspecialchars($row['kode_satpam']) ?></small>
                    </td>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($row['nama_shift']) ?></div><small class="text-muted"><?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?></small>
                    </td>
                    <td><span class="badge <?= $row['status'] === 'bertugas' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= htmlspecialchars(ucfirst($row['status'])) ?></span></td>
                    <td class="text-center"><a href="edit.php?id=<?= $row['id_jadwal'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a> <a href="hapus.php?id=<?= $row['id_jadwal'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus jadwal ini?')"><i class="bi bi-trash"></i></a></td>
                  </tr>
                <?php endwhile;
              else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Belum ada jadwal yang sesuai.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include "../../includes/footer.php"; ?>
