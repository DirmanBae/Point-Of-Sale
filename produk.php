<?php
// produk.php
session_start();
require_once 'koneksi.php';

// -------------------------------------------------------------
// FUNGSI GENERATE KODE PRODUK OTOMATIS (Format: ALT0001)
// -------------------------------------------------------------
function generateKodeProduk($koneksi) {
    // Ambil angka terbesar dari kode produk yang diawali 'ALT'
    $query = "SELECT MAX(CAST(SUBSTRING(kode_produk, 4) AS UNSIGNED)) AS max_id 
              FROM tb_produk 
              WHERE kode_produk LIKE 'ALT%'";
    $result = mysqli_query($koneksi, $query);
    $data   = mysqli_fetch_assoc($result);

    $next_number = ($data['max_id']) ? $data['max_id'] + 1 : 1;

    // Format string: ALT diikuti 4 digit angka dengan leading zeros
    return 'ALT' . sprintf('%04d', $next_number);
}

// Panggil fungsi untuk mendapatkan kode otomatis berikutnya
$kode_otomatis = generateKodeProduk($koneksi);

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
// 1. TAMBAH PRODUK BARU
// -------------------------------------------------------------
if (isset($_POST['tambah_produk'])) {
    $kode_produk = mysqli_real_escape_string($koneksi, trim($_POST['kode_produk']));
    $nama_produk = mysqli_real_escape_string($koneksi, trim($_POST['nama_produk']));
    $id_kategori = intval($_POST['id_kategori']);
    $harga_beli  = intval($_POST['harga_beli']);
    $harga_jual  = intval($_POST['harga_jual']);
    $stok        = intval($_POST['stok']);

    $query = "INSERT INTO tb_produk (kode_produk, nama_produk, id_kategori, harga_beli, harga_jual, stok) 
              VALUES ('$kode_produk', '$nama_produk', $id_kategori, $harga_beli, $harga_jual, $stok)";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Produk berhasil ditambahkan!'); window.location.href='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah produk: " . mysqli_error($koneksi) . "');</script>";
    }
}

// -------------------------------------------------------------
// 2. EDIT PRODUK
// -------------------------------------------------------------
if (isset($_POST['edit_produk'])) {
    $id_produk   = intval($_POST['id_produk']);
    $kode_produk = mysqli_real_escape_string($koneksi, trim($_POST['kode_produk']));
    $nama_produk = mysqli_real_escape_string($koneksi, trim($_POST['nama_produk']));
    $id_kategori = intval($_POST['id_kategori']);
    $harga_beli  = intval($_POST['harga_beli']);
    $harga_jual  = intval($_POST['harga_jual']);
    $stok        = intval($_POST['stok']);

    $query = "UPDATE tb_produk SET 
              kode_produk = '$kode_produk',
              nama_produk = '$nama_produk',
              id_kategori = $id_kategori,
              harga_beli = $harga_beli,
              harga_jual = $harga_jual,
              stok = $stok
              WHERE id_produk = $id_produk";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data produk berhasil diperbarui!'); window.location.href='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui produk!');</script>";
    }
}

// -------------------------------------------------------------
// 3. HAPUS PRODUK
// -------------------------------------------------------------
if (isset($_GET['hapus'])) {
    $id_produk = intval($_GET['hapus']);
    
    // Cek apakah produk pernah digunakan dalam transaksi
    $cek_trx = mysqli_query($koneksi, "SELECT id_detail FROM tb_detail_transaksi WHERE id_produk = $id_produk");
    if (mysqli_num_rows($cek_trx) > 0) {
        echo "<script>alert('Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi!'); window.location.href='produk.php';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id_produk = $id_produk");
        echo "<script>alert('Produk berhasil dihapus!'); window.location.href='produk.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Produk - Toko Olahraga ALTERA</title>
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

        <!-- KONTEN UTAMA PRODUK -->
        <main class="col-md-9 ms-sm-auto col-lg-10 ps-md-3 pe-md-4 py-3">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Master Data Produk</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
                </button>
            </div>

            <!-- TABEL DAFTAR PRODUK -->
            <div class="card border-0 shadow-sm p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q_produk = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori 
                                                                FROM tb_produk p 
                                                                LEFT JOIN tb_kategori k ON p.id_kategori = k.id_kategori 
                                                                ORDER BY p.id_produk DESC");
                            if (mysqli_num_rows($q_produk) > 0) :
                                while ($row = mysqli_fetch_assoc($q_produk)) :
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_produk']); ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_produk']); ?></td>
                                <td><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum'); ?></td>
                                <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                <td class="text-success fw-bold">Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($row['stok'] <= 5) : ?>
                                        <span class="badge bg-danger"><?= $row['stok']; ?> (Menipis)</span>
                                    <?php else : ?>
                                        <span class="badge bg-success"><?= $row['stok']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- Tombol Edit Modal -->
                                    <button class="btn btn-sm btn-warning me-1 text-white" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_produk']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <!-- Tombol Hapus -->
                                    <a href="produk.php?hapus=<?= $row['id_produk']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- MODAL EDIT PRODUK -->
                            <div class="modal fade" id="modalEdit<?= $row['id_produk']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="" method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Data Produk</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Kode Produk</label>
                                                    <input type="text" name="kode_produk" class="form-control" value="<?= htmlspecialchars($row['kode_produk']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Produk</label>
                                                    <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($row['nama_produk']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Kategori</label>
                                                    <select name="id_kategori" class="form-select" required>
                                                        <?php
                                                        $q_kat = mysqli_query($koneksi, "SELECT * FROM tb_kategori");
                                                        while ($kat = mysqli_fetch_assoc($q_kat)) :
                                                            $selected = ($kat['id_kategori'] == $row['id_kategori']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $kat['id_kategori']; ?>" <?= $selected; ?>><?= $kat['nama_kategori']; ?></option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Harga Beli (Rp)</label>
                                                        <input type="number" name="harga_beli" class="form-control" value="<?= $row['harga_beli']; ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Harga Jual (Rp)</label>
                                                        <input type="number" name="harga_jual" class="form-control" value="<?= $row['harga_jual']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Jumlah Stok</label>
                                                    <input type="number" name="stok" class="form-control" value="<?= $row['stok']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit_produk" class="btn btn-warning text-white">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data produk tersedia.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL TAMBAH PRODUK BARU -->
<div class="modal fade" id="modalTambahProduk" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Kode Produk (Otomatis)</label>
                                <input type="text" name="kode_produk" class="form-control bg-light fw-bold text-primary" value="<?= $kode_otomatis; ?>" readonly required>
                                <small class="text-muted">*Kode produk dibuat otomatis oleh sistem.</small>
                            </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Sepatu Voli Mizu 42" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $q_kat2 = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nama_kategori ASC");
                            while ($k2 = mysqli_fetch_assoc($q_kat2)) :
                            ?>
                                <option value="<?= $k2['id_kategori']; ?>"><?= $k2['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Harga Beli (Rp)</label>
                            <input type="number" name="harga_beli" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Jual (Rp)</label>
                            <input type="number" name="harga_jual" class="form-control" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Stok</label>
                        <input type="number" name="stok" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_produk" class="btn btn-primary">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>