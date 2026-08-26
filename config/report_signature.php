<?php

/** Menyimpan snapshot tanda tangan Kepala pada laporan yang telah divalidasi. */
function ensureLaporanTtdKepalaColumn(mysqli $conn): bool
{
  static $checked = false;
  if ($checked) {
    return true;
  }
  $checked = true;
  $result = mysqli_query($conn, "SHOW COLUMNS FROM laporan LIKE 'ttd_kepala'");
  if ($result && mysqli_num_rows($result) > 0) {
    return true;
  }
  try {
    return (bool) mysqli_query($conn, "ALTER TABLE laporan ADD COLUMN ttd_kepala VARCHAR(255) NULL AFTER validated_at");
  } catch (mysqli_sql_exception) {
    return false;
  }
}

/** Menyimpan snapshot tanda tangan Satpam ketika laporan difinalisasi. */
function ensureLaporanTtdSatpamColumn(mysqli $conn): bool
{
  if (!ensureLaporanTtdKepalaColumn($conn)) {
    return false;
  }

  $result = mysqli_query($conn, "SHOW COLUMNS FROM laporan LIKE 'ttd_satpam'");
  if ($result && mysqli_num_rows($result) > 0) {
    return true;
  }

  try {
    return (bool) mysqli_query($conn, "ALTER TABLE laporan ADD COLUMN ttd_satpam VARCHAR(255) NULL AFTER ttd_kepala");
  } catch (mysqli_sql_exception) {
    return false;
  }
}

/**
 * Menyimpan snapshot tanda tangan setiap anggota shift ketika laporan
 * difinalisasi. Kolom laporan.ttd_satpam dipertahankan untuk kompatibilitas
 * laporan lama yang hanya memiliki satu tanda tangan satpam.
 */
function ensureAnggotaShiftTtdColumn(mysqli $conn): bool
{
  static $checked = false;
  if ($checked) {
    return true;
  }
  $checked = true;

  $result = mysqli_query($conn, "SHOW COLUMNS FROM anggota_shift LIKE 'ttd_satpam'");
  if ($result && mysqli_num_rows($result) > 0) {
    return true;
  }

  try {
    return (bool) mysqli_query($conn, "ALTER TABLE anggota_shift ADD COLUMN ttd_satpam VARCHAR(255) NULL AFTER login_at");
  } catch (mysqli_sql_exception) {
    return false;
  }
}
