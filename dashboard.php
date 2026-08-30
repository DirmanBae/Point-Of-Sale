<?php
// dashboard.php
session_start();
require_once 'koneksi.php';

// Pengecekan Keamanan Sesi (Harus login terlebih dahulu)
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil data sesi pengguna
$id_user      = $_SESSION['id_user'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role         = $_SESSION['role'];

// -------------------------------------------------------------
// QUERY DATA REKAPITULASI KESELURUHAN (TOTAL DARI AWAL SAMPAI AKHIR)
// -------------------------------------------------------------

// 1. Total Omzet Keseluruhan
$q_omzet = mysqli_query($koneksi, "SELECT SUM(total_bayar) AS total FROM tb_transaksi");
$d_omzet = mysqli_fetch_assoc($q_omzet);
$omzet_keseluruhan = $d_omzet['total'] ?? 0;

// 2. Total Transaksi Keseluruhan
$q_trx = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_transaksi");
$d_trx = mysqli_fetch_assoc($q_trx);
$total_trx_keseluruhan = $d_trx['total'] ?? 0;

// 3. Total Jenis Produk Tersedia
$q_prod = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_produk");
$d_prod = mysqli_fetch_assoc($q_prod);
$total_produk = $d_prod['total'] ?? 0;

// 4. Peringatan Stok Produk Menipis (Stok <= 5)
$q_stok_kritis = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_produk WHERE stok <= 5");
$d_stok_kritis = mysqli_fetch_assoc($q_stok_kritis);
$stok_kritis = $d_stok_kritis['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko Olahraga ALTERA</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #0f172a;
            color: #fff;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.3rem;
            font-weight: 500;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #1e293b;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
        .bg-custom-dark { background-color: #0f172a; color: white; }
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

        <!-- KONTEN UTAMA DASHBOARD -->
        <main class="col-md-9 ms-sm-auto col-lg-10 ps-md-3 pe-md-4 py-3">
            
            <!-- HEADER TOP BAR -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
                <div>
                    <h2 class="h4 fw-bold">Selamat Datang, <?= htmlspecialchars($nama_lengkap); ?>!</h2>
                    <p class="text-muted mb-0">Anda terautentikasi sebagai <span class="badge bg-primary"><?= htmlspecialchars($role); ?></span></p>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark border p-2">
                        <i class="bi bi-calendar3 me-1"></i> <?= date('d F Y'); ?>
                    </span>
                </div>
            </div>

            <!-- TAMPILAN WIDGET STATISTIK KESELURUHAN (KHUSUS MANAJER) -->
            <?php if ($role === 'Manajer') : ?>
            <div class="row g-3 mb-4">
                <!-- Card 1: Total Omzet Keseluruhan -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm bg-success text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 fw-bold">TOTAL OMZET KESELURUHAN</small>
                                <h4 class="fw-bold mb-0">Rp <?= number_format($omzet_keseluruhan, 0, ',', '.'); ?></h4>
                            </div>
                            <div class="fs-1"><i class="bi bi-cash-stack"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Total Transaksi Keseluruhan -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm bg-primary text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 fw-bold">TOTAL TRANSAKSI</small>
                                <h4 class="fw-bold mb-0"><?= $total_trx_keseluruhan; ?> Transaksi</h4>
                            </div>
                            <div class="fs-1"><i class="bi bi-receipt"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Total Produk -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm bg-custom-dark text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 fw-bold">KATALOG PRODUK</small>
                                <h4 class="fw-bold mb-0"><?= $total_produk; ?> Item</h4>
                            </div>
                            <div class="fs-1"><i class="bi bi-box-seam"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Peringatan Stok Kritis -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm bg-warning text-dark p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-dark fw-bold">STOK MENIPIS (<=5)</small>
                                <h4 class="fw-bold mb-0"><?= $stok_kritis; ?> Produk</h4>
                            </div>
                            <div class="fs-1"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- KONTEN UNTUK KASIR / PANEL CEPAT -->
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Transaksi Terbaru</h5>
                            <a href="riwayat_kasir.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Nota</th>
                                        <th>Tanggal & Waktu</th>
                                        <th>Total Bayar</th>
                                        <th>Kasir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q_recent = mysqli_query($koneksi, "SELECT t.*, u.nama_lengkap 
                                                                        FROM tb_transaksi t 
                                                                        JOIN tb_user u ON t.id_user = u.id_user 
                                                                        ORDER BY t.id_transaksi DESC LIMIT 5");
                                    if (mysqli_num_rows($q_recent) > 0) :
                                        while ($r = mysqli_fetch_assoc($q_recent)) :
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= $r['no_nota']; ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($r['tgl_transaksi'])); ?> WIB</td>
                                        <td>Rp <?= number_format($r['total_bayar'], 0, ',', '.'); ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['nama_lengkap']); ?></span></td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada data transaksi.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 bg-white text-center">
                        <div class="mb-3 fs-1 text-primary">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <h5 class="fw-bold">Pelayanan Kasir (POS)</h5>
                        <p class="text-muted small">Mulai transaksi baru untuk mencatat belanjaan pelanggan dan cetak nota.</p>
                        <a href="transaksi.php" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>