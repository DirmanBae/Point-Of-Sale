<?php

session_start();
require_once 'koneksi.php';


if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';


if (isset($_POST['btn_login'])) {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        //cek username
        $query  = "SELECT * FROM tb_user WHERE username = '$username'";
        $result = mysqli_query($koneksi, $query);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $row['password']) || $password === $row['password']) {
                // Set variabel session
                $_SESSION['login']       = true;
                $_SESSION['id_user']     = $row['id_user'];
                $_SESSION['username']    = $row['username'];
                $_SESSION['nama_lengkap']= $row['nama_lengkap'];
                $_SESSION['role']        = $row['role'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Kata sandi yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    } else {
        $error = "Silakan isi Username dan Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Olahraga ALTERA</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #b9b9b9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-login {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        .brand-header {
            background: #1e293b;
            color: #fffb00;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        .brand-header h4 {
            font-weight: 700;
            margin-bottom: 0.25rem;
            letter-spacing: 1px;
        }
        .brand-header p {
            font-size: 0.875rem;
            color: #94a3b8;
            margin-bottom: 0;
            font-style: italic;
        }
        .btn-primary-custom {
            background-color: #0f172a;
            border-color: #0f172a;
            padding: 0.65rem;
            font-weight: 600;
        }
        .btn-primary-custom:hover {
            background-color: #334155;
            border-color: #334155;
        }
    </style>
</head>
<body>

<div class="container px-3">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card card-login">
                <!-- Header Kartu Login -->
                <div class="brand-header">
                    <img src="img/altera.png" alt="" width="250px">
                    <b>"Lengkapi Style Sportymu"</b>
                </div>
                
                <div class="card-body p-4">
                    <h5 class="text-center fw-bold mb-3 text-secondary">LOGIN SYSTEM</h5>
                    
                    <!-- Alert Pesan Error jika Login Gagal -->
                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Login -->
                    <form action="" method="POST">
                        <!-- Input Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required autocomplete="off">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan kata sandi" required>
                            </div>
                        </div>

                        <button type="submit" name="btn_login" class="btn btn-primary-custom text-white w-100 mb-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> LOGIN
                        </button>
                    </form>
                </div>

                <div class="card-footer bg-light text-center py-3 border-0" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <small class="text-muted">&copy; 2026 Toko Olahraga ALTERA. All Rights Reserved.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle dengan Popper CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>