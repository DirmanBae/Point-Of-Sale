<?php
// laporan.php
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
// FILTER RENTANG TANGGAL (JIKA TIDAK DIISI, TAMPILKAN SEMUA DATA)
// -------------------------------------------------------------
$tgl_mulai   = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

// Query Dasar: Mengambil semua data transaksi dari awal hingga akhir
$query = "SELECT t.*, u.nama_lengkap 
          FROM tb_transaksi t 
          JOIN tb_user u ON t.id_user = u.id_user";

// Jika Manajer mengisi filter tanggal
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $query .= " WHERE DATE(t.tgl_transaksi) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}

$query .= " ORDER BY t.tgl_transaksi DESC";
$q_laporan = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Toko Olahraga ALTERA</title>
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
        @media print {
            .sidebar, .btn-print, .card-filter { display: none !important; }
            main { width: 100% !important; margin: 0 !important; }
        }
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

        <!-- KONTEN UTAMA LAPORAN -->
        <main class="col-md-9 ms-sm-auto col-lg-10 ps-md-3 pe-md-4 py-3">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan Penjualan & Omzet</h2>
                <button onclick="window.print()" class="btn btn-secondary btn-print">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan
                </button>
            </div>

            <!-- CARD FILTER RENTANG TANGGAL -->
            <div class="card border-0 shadow-sm p-3 mb-4 card-filter">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai); ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i> Filter
                        </button>
                        <a href="laporan.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- REKAP KARTU TOTAL OMZET -->
            <?php
            $grand_total_omzet = 0;
            $data_laporan = [];
            
            while ($row = mysqli_fetch_assoc($q_laporan)) {
                $grand_total_omzet += $row['total_bayar'];
                $data_laporan[] = $row;
            }
            ?>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-primary text-white p-3">
                        <small class="text-white-50 fw-bold">PERIODE LAPORAN</small>
                        <h5 class="fw-bold mb-0">
                            <?php if (!empty($tgl_mulai) && !empty($tgl_selesai)) : ?>
                                <?= date('d/m/Y', strtotime($tgl_mulai)); ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)); ?>
                            <?php else : ?>
                                Semua Periode Transaksi (Keseluruhan)
                            <?php endif; ?>
                        </h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-success text-white p-3">
                        <small class="text-white-50 fw-bold">TOTAL OMZET PENDAPATAN</small>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($grand_total_omzet, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>

            <!-- TABEL DAFTAR TRANSAKSI -->
            <div class="card border-0 shadow-sm p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>No. Nota</th>
                                <th>Waktu Transaksi</th>
                                <th>Kasir</th>
                                <th>Nominal Tunai</th>
                                <th>Kembalian</th>
                                <th>Total Bayar (Omzet)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($data_laporan)) :
                                $no = 1;
                                foreach ($data_laporan as $trx) :
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><span class="badge bg-dark"><?= htmlspecialchars($trx['no_nota']); ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($trx['tgl_transaksi'])); ?> WIB</td>
                                <td><?= htmlspecialchars($trx['nama_lengkap']); ?></td>
                                <td>Rp <?= number_format($trx['nominal_uang'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($trx['kembalian'], 0, ',', '.'); ?></td>
                                <td class="fw-bold text-success">Rp <?= number_format($trx['total_bayar'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Tidak ada data transaksi yang ditemukan.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="6" class="text-end">TOTAL KESELURUHAN OMZET:</td>
                                <td class="text-success fs-6">Rp <?= number_format($grand_total_omzet, 0, ',', '.'); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>