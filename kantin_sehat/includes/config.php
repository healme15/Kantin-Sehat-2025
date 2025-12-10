<?php
// Koneksi database
session_start();
$host = "localhost";
$user = "root";
$password = "";
$database = "kantin_sehat";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');
?>