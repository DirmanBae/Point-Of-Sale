<?php
// user.php
session_start();
require_once 'koneksi.php';

// Cek autentikasi login & otoritas (Hanya Manajer)
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'Manajer') {
    echo "<script>alert('Akses ditolak! Halaman ini hanya untuk Manajer.'); window.location.href='dashboard.php';</script>";
    exit;
}

// -------------------------------------------------------------
// 1. TAMBAH USER BARU
// -------------------------------------------------------------
if (isset($_POST['tambah_user'])) {
    $username     = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password     = trim($_POST['password']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $role         = mysqli_real_escape_string($koneksi, trim($_POST['role']));

    // Cek ketersediaan username
    $cek_user = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan! Gunakan username lain.');</script>";
    } else {
        // Enkripsi kata sandi
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO tb_user (username, password, nama_lengkap, role) 
                  VALUES ('$username', '$password_hashed', '$nama_lengkap', '$role')";

        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Akun pengguna berhasil ditambahkan!'); window.location.href='user.php';</script>";
        } else {
            echo "<script>alert('Gagal menambah pengguna: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// -------------------------------------------------------------
// 2. EDIT USER
// -------------------------------------------------------------
if (isset($_POST['edit_user'])) {
    $id_user      = intval($_POST['id_user']);
    $username     = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $role         = mysqli_real_escape_string($koneksi, trim($_POST['role']));
    $password     = trim($_POST['password']);

    // Cek jika password diubah atau tidak
    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE tb_user SET 
                  username = '$username',
                  password = '$password_hashed',
                  nama_lengkap = '$nama_lengkap',
                  role = '$role'
                  WHERE id_user = $id_user";
    } else {
        $query = "UPDATE tb_user SET 
                  username = '$username',
                  nama_lengkap = '$nama_lengkap',
                  role = '$role'
                  WHERE id_user = $id_user";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data pengguna berhasil diperbarui!'); window.location.href='user.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui pengguna!');</script>";
    }
}

// -------------------------------------------------------------
// 3. HAPUS USER
// -------------------------------------------------------------
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);

    // Mencegah manajer menghapus akunnya sendiri yang sedang digunakan
    if ($id_hapus == $_SESSION['id_user']) {
        echo "<script>alert('Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!'); window.location.href='user.php';</script>";
    } else {
        // Cek apakah user pernah memproses transaksi
        $cek_trx = mysqli_query($koneksi, "SELECT id_transaksi FROM tb_transaksi WHERE id_user = $id_hapus");
        if (mysqli_num_rows($cek_trx) > 0) {
            echo "<script>alert('Akun tidak dapat dihapus karena memiliki riwayat transaksi! Disarankan untuk mengedit data pengguna.'); window.location.href='user.php';</script>";
        } else {
            mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user = $id_hapus");
            echo "<script>alert('Akun pengguna berhasil dihapus!'); window.location.href='user.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Staf User - Toko Olahraga ALTERA</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #0f172a; color: #fff; }
        .sidebar .nav-link { color: #94a3b8; padding: 0.8rem 1rem; border-radius: 8px; margin-bottom: 0.3rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #ffffff; background-color: #1e293b; }
        .sidebar .nav-link i { margin-right: 10px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR NAVIGASI -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
            <div class="row">
                <?php include 'sidebar.php'; ?>
            </div>
        </nav>

        <!-- KONTEN UTAMA KELOLA USER -->
        <main class="col-md-9 ms-sm-auto col-lg-10 ps-md-3 pe-md-4 py-3">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-people me-2"></i>Kelola Akun Staf & Pengguna</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                    <i class="bi bi-person-plus me-1"></i> Tambah Pengguna Baru
                </button>
            </div>

            <!-- TABEL DAFTAR PENGGUNA -->
            <div class="card border-0 shadow-sm p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Hak Akses (Role)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q_user = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user DESC");
                            if (mysqli_num_rows($q_user) > 0) :
                                while ($row = mysqli_fetch_assoc($q_user)) :
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><code><?= htmlspecialchars($row['username']); ?></code></td>
                                <td>
                                    <?php if ($row['role'] === 'Manajer') : ?>
                                        <span class="badge bg-primary"><i class="bi bi-shield-lock me-1"></i>Manajer</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary"><i class="bi bi-person-badge me-1"></i>Kasir</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- Tombol Edit Modal -->
                                    <button class="btn btn-sm btn-warning me-1 text-white" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= $row['id_user']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <!-- Tombol Hapus -->
                                    <?php if ($row['id_user'] != $_SESSION['id_user']) : ?>
                                        <a href="user.php?hapus=<?= $row['id_user']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus akun staf ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- MODAL EDIT USER -->
                            <div class="modal fade" id="modalEditUser<?= $row['id_user']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="" method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Akun Pengguna</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Lengkap</label>
                                                    <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($row['nama_lengkap']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Username</label>
                                                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Kata Sandi Baru <small class="text-muted">(Kosongkan jika tidak ingin diubah)</small></label>
                                                    <input type="password" name="password" class="form-control" placeholder="******">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Hak Akses (Role)</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="Kasir" <?= ($row['role'] === 'Kasir') ? 'selected' : ''; ?>>Kasir</option>
                                                        <option value="Manajer" <?= ($row['role'] === 'Manajer') ? 'selected' : ''; ?>>Manajer</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit_user" class="btn btn-warning text-white">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada data staf/pengguna.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL TAMBAH USER BARU -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Akun Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap Staf</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: kasir1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hak Akses (Role)</label>
                        <select name="role" class="form-select" required>
                            <option value="Kasir">Kasir</option>
                            <option value="Manajer">Manajer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_user" class="btn btn-primary">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>