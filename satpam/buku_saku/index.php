<?php
require_once "../../config/database.php";


$title = "Buku Saku";
$base_url = "../../";

include "../../includes/header.php";
?>

<div class="container py-5">

<div class="text-center mb-5">

<h2 class="fw-bold">

📚 Buku Saku Satpam

</h2>

<p class="text-muted">

Silakan pilih jenis Buku Saku yang ingin dipelajari.

</p>

</div>

<div class="row justify-content-center">

<div class="col-lg-5 col-md-6 mb-4">

<div class="card shadow border-0 h-100">

<div class="card-body text-center p-5">

<div class="mb-4">

<i class="bi bi-file-earmark-pdf-fill text-danger"
style="font-size:80px;"></i>

</div>

<h3 class="fw-bold">

Buku PDF

</h3>

<p class="text-muted">

Berisi SOP, Buku Pedoman, Panduan Kerja, serta dokumen resmi Satpam.

</p>

<a
href="pdf.php"
class="btn btn-danger mt-3">

<i class="bi bi-arrow-right-circle"></i>

Masuk

</a>

</div>

</div>

</div>

<div class="col-lg-5 col-md-6 mb-4">

<div class="card shadow border-0 h-100">

<div class="card-body text-center p-5">

<div class="mb-4">

<i class="bi bi-journal-bookmark-fill text-primary"
style="font-size:80px;"></i>

</div>

<h3 class="fw-bold">

Materi Buku Saku

</h3>

<p class="text-muted">

Berisi materi pengetahuan dasar, SOP, tugas satpam, sandi komunikasi dan referensi.

</p>

<a
href="materi.php"
class="btn btn-primary mt-3">

<i class="bi bi-arrow-right-circle"></i>

Masuk

</a>

</div>

</div>

</div>

</div>

</div>

</div>

<?php include "../../includes/footer.php"; ?>