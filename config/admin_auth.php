<?php

require_once "session.php";
require_once "database.php";

if (
    !isset($_SESSION['login']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}