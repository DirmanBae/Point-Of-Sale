<?php
// kategori.php
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
// 1. TAMBAH KATEGORI BARU
// -------------------------------------------------------------
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));

    if (!empty($nama_kategori)) {
        $query = "INSERT INTO tb_kategori (nama_kategori) VALUES ('$nama_kategori')";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Kategori berhasil ditambahkan!'); window.location.href='kategori.php';</script>";
        } else {
            echo "<script>alert('Gagal menambah kategori: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// -------------------------------------------------------------
// 2. EDIT KATEGORI
// -------------------------------------------------------------
if (isset($_POST['edit_kategori'])) {
    $id_kategori   = intval($_POST['id_kategori']);
    $nama_kategori = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));

    if (!empty($nama_kategori)) {
        $query = "UPDATE tb_kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = $id_kategori";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Kategori berhasil diperbarui!'); window.location.href='kategori.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui kategori!');</script>";
        }
    }
}

// -------------------------------------------------------------
// 3. HAPUS KATEGORI
// -------------------------------------------------------------
if (isset($_GET['hapus'])) {
    $id_kategori = intval($_GET['hapus']);

    // Cek apakah kategori masih digunakan oleh produk
    $cek_produk = mysqli_query($koneksi, "SELECT id_produk FROM tb_produk WHERE id_kategori = $id_kategori");
    if (mysqli_num_rows($cek_produk) > 0) {
        echo "<script>alert('Kategori tidak dapat dihapus karena masih digunakan oleh beberapa produk!'); window.location.href='kategori.php';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM tb_kategori WHERE id_kategori = $id_kategori");
        echo "<script>alert('Kategori berhasil dihapus!'); window.location.href='kategori.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Produk - Toko Olahraga ALTERA</title>
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

        <!-- KONTEN UTAMA KATEGORI -->
        <main class="col-md-9 ms-sm-auto col-lg-10 ps-md-3 pe-md-4 py-3">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-tags me-2"></i>Kategori Produk</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
                </button>
            </div>

            <!-- TABEL DAFTAR KATEGORI -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Kategori</th>
                                        <th>Jumlah Produk</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $q_kat = mysqli_query($koneksi, "SELECT k.*, COUNT(p.id_produk) AS total_produk 
                                                                    FROM tb_kategori k 
                                                                    LEFT JOIN tb_produk p ON k.id_kategori = p.id_kategori 
                                                                    GROUP BY k.id_kategori 
                                                                    ORDER BY k.nama_kategori ASC");
                                    if (mysqli_num_rows($q_kat) > 0) :
                                        while ($row = mysqli_fetch_assoc($q_kat)) :
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['nama_kategori']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?= $row['total_produk']; ?> Item</span></td>
                                        <td class="text-center">
                                            <!-- Tombol Edit Modal -->
                                            <button class="btn btn-sm btn-warning me-1 text-white" data-bs-toggle="modal" data-bs-target="#modalEditKategori<?= $row['id_kategori']; ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <!-- Tombol Hapus -->
                                            <a href="kategori.php?hapus=<?= $row['id_kategori']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- MODAL EDIT KATEGORI -->
                                    <div class="modal fade" id="modalEditKategori<?= $row['id_kategori']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_kategori" value="<?= $row['id_kategori']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nama Kategori</label>
                                                            <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($row['nama_kategori']); ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit_kategori" class="btn btn-warning text-white">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada kategori produk.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL TAMBAH KATEGORI BARU -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Sepatu Voli, Jersey, Bola" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_kategori" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>