<?php
// cetak_nota.php
session_start();
require_once 'koneksi.php';

// Pengecekan Keamanan Sesi
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_transaksi = intval($_GET['id'] ?? 0);

// Ambil Header Transaksi & Nama Kasir
$query_header = "SELECT t.*, u.nama_lengkap 
                FROM tb_transaksi t 
                JOIN tb_user u ON t.id_user = u.id_user 
                WHERE t.id_transaksi = $id_transaksi";
$res_header   = mysqli_query($koneksi, $query_header);
$header       = mysqli_fetch_assoc($res_header);

if (!$header) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.close();</script>";
    exit;
}

// Ambil Detail Barang yang Dibeli
$query_detail = "SELECT d.*, p.nama_produk, p.kode_produk 
                FROM tb_detail_transaksi d 
                JOIN tb_produk p ON d.id_produk = p.id_produk 
                WHERE d.id_transaksi = $id_transaksi";
$res_detail   = mysqli_query($koneksi, $query_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Transaksi - <?= htmlspecialchars($header['no_nota']); ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 280px; /* Ukuran standar kertas kasir 58mm/80mm */
            background-color: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 2px 0; }
        .no-print { margin-top: 15px; text-align: center; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Header Nota -->
    <div class="text-center">
        <h3 style="margin: 0; font-weight: bold;">ALTERA SPORT</h3>
        <p style="margin: 2px 0; font-size: 10px;">Dsn. Kliwon, Kertawinangun, Cidahu, Kuningan</p>
        <p style="margin: 2px 0; font-size: 10px; font-style: italic;">"Lengkapi Style Sportymu"</p>
    </div>

    <div class="line"></div>

    <!-- Info Transaksi -->
    <table>
        <tr>
            <td>No. Nota</td>
            <td>: <?= htmlspecialchars($header['no_nota']); ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: <?= date('d/m/Y H:i', strtotime($header['tgl_transaksi'])); ?></td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: <?= htmlspecialchars($header['nama_lengkap']); ?></td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- Tabel Daftar Barang -->
    <table>
        <?php while ($item = mysqli_fetch_assoc($res_detail)) : ?>
        <tr>
            <td colspan="2"><strong><?= htmlspecialchars($item['nama_produk']); ?></strong></td>
        </tr>
        <tr>
            <td><?= $item['jumlah']; ?> x @<?= number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
            <td class="text-right">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="line"></div>

    <!-- Rekapitulasi Pembayaran -->
    <table>
        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="text-right"><strong>Rp <?= number_format($header['total_bayar'], 0, ',', '.'); ?></strong></td>
        </tr>
        <tr>
            <td>Tunai</td>
            <td class="text-right">Rp <?= number_format($header['nominal_uang'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">Rp <?= number_format($header['kembalian'], 0, ',', '.'); ?></td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="text-center" style="margin-top: 10px;">
        <p style="margin: 2px 0;">Terima Kasih Telah Berbelanja!</p>
        <p style="margin: 2px 0; font-size: 10px;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>

</body>
</html>