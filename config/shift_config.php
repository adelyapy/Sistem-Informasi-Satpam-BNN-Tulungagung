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
