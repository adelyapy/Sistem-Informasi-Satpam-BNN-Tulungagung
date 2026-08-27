<?php

/** Menyelaraskan pilihan dan jam shift pada database yang sudah berjalan. */
function ensureShiftDobel(mysqli $conn): bool
{
  $definisiShift = [
    ['Shift 1', '07:30:00', '19:30:00'],
    ['Shift 2', '19:30:00', '07:30:00'],
    ['Shift 1 & 2', '07:30:00', '07:30:00'],
  ];

  foreach ($definisiShift as [$namaShift, $jamMulai, $jamSelesai]) {
    $cek = mysqli_prepare($conn, 'SELECT id_shift FROM shift WHERE nama_shift = ? LIMIT 1');
    mysqli_stmt_bind_param($cek, 's', $namaShift);
    mysqli_stmt_execute($cek);
    $dataShift = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));

    if ($dataShift) {
      $idShift = (int) $dataShift['id_shift'];
      $ubah = mysqli_prepare($conn, 'UPDATE shift SET jam_mulai = ?, jam_selesai = ? WHERE id_shift = ?');
      mysqli_stmt_bind_param($ubah, 'ssi', $jamMulai, $jamSelesai, $idShift);
      if (!mysqli_stmt_execute($ubah)) {
        return false;
      }
      continue;
    }

    $tambah = mysqli_prepare($conn, 'INSERT INTO shift (nama_shift, jam_mulai, jam_selesai) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($tambah, 'sss', $namaShift, $jamMulai, $jamSelesai);
    if (!mysqli_stmt_execute($tambah) && mysqli_errno($conn) !== 1062) {
      return false;
    }
  }

  return true;
}

/**
 * Menentukan apakah waktu saat ini berada pada rentang shift tertentu.
 * Shift yang melewati tengah malam, misalnya 19:30 - 07:30, ditangani
 * sebagai dua rentang waktu yang berkesinambungan.
 */
function shiftSedangBerlangsung(array $shift, ?string $waktu = null): bool
{
  $sekarang = $waktu ?? (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('H:i:s');
  $mulai = (string) ($shift['jam_mulai'] ?? '');
  $selesai = (string) ($shift['jam_selesai'] ?? '');

  if ($mulai === '' || $selesai === '') {
    return false;
  }

  // Shift dobel 07:30 - 07:30 berdurasi satu hari penuh. Pilihan ini
  // tetap tersedia bersama shift aktif agar satpam dapat ditugaskan dua shift.
  if ($mulai === $selesai) {
    return ($shift['nama_shift'] ?? '') === 'Shift 1 & 2';
  }

  if ($mulai < $selesai) {
    return $sekarang >= $mulai && $sekarang < $selesai;
  }

  return $sekarang >= $mulai || $sekarang < $selesai;
}

/**
 * Menentukan pilihan shift yang diperbolehkan ketika satpam login.
 * Shift dobel hanya dapat dimulai pada rentang Shift 1 agar berakhir
 * pada hari yang sama. Saat Shift 2 berlangsung, satpam hanya memilih Shift 2.
 */
function shiftDapatDipilihSaatLogin(array $shift, ?string $waktu = null): bool
{
  $sekarang = $waktu ?? (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('H:i:s');

  if (($shift['nama_shift'] ?? '') === 'Shift 1 & 2') {
    return $sekarang >= '07:30:00' && $sekarang < '19:30:00';
  }

  return shiftSedangBerlangsung($shift, $sekarang);
}
