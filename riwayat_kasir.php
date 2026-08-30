<?php
// riwayat_kasir.php
session_start();
require_once 'koneksi.php';

// Pengecekan Keamanan Sesi
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// -------------------------------------------------------------
// DEFAULT FILTER TANGGAL (JIKA KASIR INGIN MEMFILTRASI)
// -------------------------------------------------------------
$tgl_mulai   = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

// Query Dasar: Ambil SEMUA riwayat transaksi milik kasir yang sedang login
$query = "SELECT * FROM tb_transaksi WHERE id_user = $id_user";

// Jika kasir memilih rentang tanggal, tambahkan kondisi WHERE
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $query .= " AND DATE(tgl_transaksi) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}

$query .= " ORDER BY id_transaksi DESC";
$q_riwayat = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Saya - Toko Olahraga ALTERA</title>
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
        <!-- SIDEBAR NAVIGASI KASIR -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
            <div class="row">
                <?php include 'sidebar.php'; ?>
            </div>
        </nav>

        <!-- KONTEN UTAMA RIWAYAT TRANSAKSI KASIR -->
        <main class="col-md-9 ms-sm-auto col-lg-10 ps-md-3 pe-md-4 py-3">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Semua Riwayat Transaksi Saya</h2>
                <span class="badge bg-secondary p-2">Staf Kasir: <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            </div>

            <!-- CARD FILTER RENTANG TANGGAL -->
            <div class="card border-0 shadow-sm p-3 mb-4">
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
                        <a href="riwayat_kasir.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABEL RIWAYAT TRANSAKSI KASIR -->
            <div class="card border-0 shadow-sm p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>No. Nota</th>
                                <th>Tanggal & Waktu</th>
                                <th>Total Belanja</th>
                                <th>Uang Tunai</th>
                                <th>Kembalian</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($q_riwayat) > 0) :
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($q_riwayat)) :
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($row['no_nota']); ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])); ?> WIB</td>
                                <td class="fw-bold">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row['nominal_uang'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row['kembalian'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <a href="cetak_nota.php?id=<?= $row['id_transaksi']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-printer me-1"></i> Cetak Struk
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat transaksi yang ditemukan.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>