<?php
require_once "config/database.php"; // sesuaikan dengan file koneksi Anda

$title = "Nomor Penting";
$base_url = "./";

include "includes/header.php";

$query = mysqli_query($conn,"
SELECT *
FROM nomor_penting
ORDER BY urutan ASC, instansi ASC
");

$total = mysqli_num_rows($query);
?>

<link rel="stylesheet" href="<?= $base_url ?>assets/css/nomor_penting.css">

<div class="container py-5">

    <div class="text-center mb-5">

        <h1 class="fw-bold text-primary">
            <i class="bi bi-telephone-fill"></i>
            Nomor Penting
        </h1>

        <p class="text-muted">
            Daftar nomor penting yang dapat dihubungi saat keadaan darurat.
        </p>

    </div>

    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card shadow border-0 statistik-card">

                <div class="card-body text-center">

                    <h6>Total Nomor</h6>

                    <h2><?= $total ?></h2>

                </div>

            </div>

        </div>

        <div class="col-md-8">

            <input
                type="text"
                id="searchNomor"
                class="form-control form-control-lg"
                placeholder="Cari instansi...">

        </div>

    </div>

    <div class="row">

<?php while($row=mysqli_fetch_assoc($query)){ ?>

<?php

$icon="bi-telephone-fill";

$nama=strtolower($row['instansi']);

if(str_contains($nama,"pol")){
    $icon="bi-shield-fill";
}

elseif(str_contains($nama,"rumah sakit") || str_contains($nama,"rs")){
    $icon="bi-hospital-fill";
}

elseif(str_contains($nama,"damkar")){
    $icon="bi-fire";
}

elseif(str_contains($nama,"pln")){
    $icon="bi-lightning-fill";
}

elseif(str_contains($nama,"pdam")){
    $icon="bi-droplet-fill";
}

elseif(str_contains($nama,"ambulance")){
    $icon="bi-truck";
}

elseif(str_contains($nama,"bnn")){
    $icon="bi-shield-lock-fill";
}

?>

<div class="col-lg-4 mb-4 nomor-card">

<div class="card h-100 shadow border-0">

<div class="card-body">

<div class="text-center mb-4">

<i class="bi <?= $icon ?> fs-1 text-primary"></i>

<h4 class="mt-3">

<?= $row['instansi']; ?>

</h4>

</div>

<h5 class="text-center text-success">

<?= $row['nomor_telepon']; ?>

</h5>

<p class="text-center text-muted">

<?= $row['keterangan']; ?>

</p>

<div class="d-grid">

<a

href="tel:<?= $row['nomor_telepon']; ?>"

class="btn btn-primary">

<i class="bi bi-telephone-fill"></i>

Hubungi

</a>

</div>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

<script>

document.getElementById("searchNomor")
.addEventListener("keyup",function(){

let value=this.value.toLowerCase();

document.querySelectorAll(".nomor-card")
.forEach(function(card){

card.style.display=

card.innerText.toLowerCase().includes(value)

?

""

:

"none";

});

});

</script>

<?php include "includes/footer.php"; ?>