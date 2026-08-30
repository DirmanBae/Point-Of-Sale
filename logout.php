<?php
// logout.php

// 1. Inisialisasi sesi
session_start();

// 2. Kosongkan seluruh variabel sesi
$_SESSION = array();

// 3. Hapus cookie sesi dari browser jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan seluruh data sesi
session_destroy();

// 5. Alihkan kembali pengguna ke halaman login
header("Location: index.php");
exit;
?>