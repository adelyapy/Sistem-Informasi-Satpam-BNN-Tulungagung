<?php
session_start();

require_once "session.php";
require_once "database.php";

if (
    !isset($_SESSION['login']) ||
    $_SESSION['role'] != 'kepala'
) {
    header("Location: ../login.php");
    exit;
}