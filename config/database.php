<?php
// File: config/database.php

$host = "localhost";
$user = "root"; // Default username XAMPP
$pass = "";     // Default password XAMPP
$db   = "ppdb2";

// Membuat koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
