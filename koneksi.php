<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "altera";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi basis data gagal: ".mysqli_connect_error());
};
?>