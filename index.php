<?php
$title = "Buku Mutasi Satpam";
$base_url = "./";
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/landing.css">

<div class="container-app bg-wave">

    <!-- HERO -->
    <section class="hero-section">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-8">

                    <div class="hero-content text-center fade-up">

                        <img src="assets/img/logo-bnn.png"
                            alt="Logo BNN"
                            class="logo-bnn">

                        <h1 class="title">
                            BUKU MUTASI SATPAM
                        </h1>

                        <h5 class="subtitle">
                            BADAN NARKOTIKA NASIONAL
                        </h5>

                        <h6 class="subtitle">
                            KABUPATEN TULUNGAGUNG
                        </h6>

                        <p class="description">

                            Sistem Informasi Digital
                            untuk pengelolaan Buku Mutasi Satpam,
                            Inventaris, Uraian Kegiatan,
                            serta Validasi Laporan.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <!-- MENU -->

    <section class="menu-section">

        <div class="container">

            <div class="row g-4 justify-content-center">

                <!-- Buku Saku -->

                <div class="col-lg-4 col-md-6">

                    <a href="satpam/buku_saku/index.php">

                        <div class="card-app landing-card h-100">

                            <div class="card-body">

                                <div class="icon-circle">

                                    📘

                                </div>

                                <h4>

                                    Buku Saku Satpam

                                </h4>

                                <p>

                                    Lihat SOP, buku saku,
                                    dan panduan kerja satpam.

                                </p>

                                <button class="btn btn-primary-app mt-4">

                                    Buka

                                </button>

                            </div>

                        </div>

                    </a>

                </div>

                <!-- Buku Mutasi -->

                <div class="col-lg-4 col-md-6">

                    <a href="login.php">

                        <div class="card-app landing-card h-100">

                            <div class="card-body">

                                <div class="icon-circle">

                                    📋

                                </div>

                                <h4>

                                    Buku Mutasi Satpam

                                </h4>

                                <p>

                                    Masuk ke sistem
                                    pengelolaan Buku Mutasi.

                                </p>

                                <button class="btn btn-primary-app mt-4">

                                    Masuk

                                </button>

                            </div>

                        </div>

                    </a>

                </div>

                <!-- Nomor Penting -->

                <div class="col-lg-4 col-md-6">

                    <a href="nomor_penting.php">

                        <div class="card-app landing-card h-100">

                            <div class="card-body">

                                <div class="icon-circle">

                                    ☎

                                </div>

                                <h4>

                                    Nomor Penting

                                </h4>

                                <p>

                                    Daftar nomor darurat,
                                    instansi dan layanan penting.

                                </p>

                                <button class="btn btn-primary-app mt-4">

                                    Lihat

                                </button>

                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

    </section>

    <!-- FOOTER -->

    <footer class="landing-footer">

        <div class="container">

            <p>

                © 2026 Badan Narkotika Nasional Kabupaten Tulungagung

            </p>

            <small>

                Dikembangkan oleh Mahasiswa PKL
                Universitas Nusantara PGRI Kediri 2026

            </small>

        </div>

    </footer>

</div>

<?php include 'includes/footer.php'; ?>