<?php
// transaksi.php
session_start();
require_once 'koneksi.php';

// Cek autentikasi login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Inisialisasi keranjang belanja menggunakan session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ------------------------------------------------------------------
// 1. TAMBAH BARANG KE KERANJANG
// ------------------------------------------------------------------
if (isset($_POST['add_to_cart'])) {
    $id_produk = intval($_POST['id_produk']);
    $qty       = intval($_POST['qty']);

    if ($id_produk > 0 && $qty > 0) {
        $q = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id_produk = $id_produk");
        if (mysqli_num_rows($q) > 0) {
            $item = mysqli_fetch_assoc($q);

            // Cek ketersediaan stok
            if ($qty <= $item['stok']) {
                $found = false;
                foreach ($_SESSION['cart'] as &$cart_item) {
                    if ($cart_item['id_produk'] == $id_produk) {
                        $cart_item['qty'] += $qty;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $_SESSION['cart'][] = [
                        'id_produk'   => $item['id_produk'],
                        'kode_produk' => $item['kode_produk'],
                        'nama_produk' => $item['nama_produk'],
                        'harga_jual'  => $item['harga_jual'],
                        'qty'         => $qty
                    ];
                }
            } else {
                echo "<script>alert('Stok tidak mencukupi! Stok tersedia: {$item['stok']}');</script>";
            }
        }
    }
}

// ------------------------------------------------------------------
// 2. HAPUS ITEM DARI KERANJANG
// ------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $index = intval($_GET['index']);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reset index array
    }
    header("Location: transaksi.php");
    exit;
}

// ------------------------------------------------------------------
// 3. KOSONGKAN KERANJANG
// ------------------------------------------------------------------
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: transaksi.php");
    exit;
}

// ------------------------------------------------------------------
// 4. PROSES SIMPAN TRANSAKSI
// ------------------------------------------------------------------
if (isset($_POST['process_checkout'])) {
    $nominal_uang = intval($_POST['nominal_uang']);
    $total_bayar  = 0;

    foreach ($_SESSION['cart'] as $c) {
        $total_bayar += ($c['harga_jual'] * $c['qty']);
    }

    if (!empty($_SESSION['cart']) && $nominal_uang >= $total_bayar) {
        $kembalian = $nominal_uang - $total_bayar;
        $no_nota   = 'TRX-' . date('YmdHis') . rand(10, 99);
        $tgl       = date('Y-m-d H:i:s');

        // Simpan ke Header Transaksi
        $q_trx = "INSERT INTO tb_transaksi (no_nota, tgl_transaksi, total_bayar, nominal_uang, kembalian, id_user) 
                  VALUES ('$no_nota', '$tgl', $total_bayar, $nominal_uang, $kembalian, $id_user)";
        
        if (mysqli_query($koneksi, $q_trx)) {
            $id_transaksi_baru = mysqli_insert_id($koneksi);

            // Simpan ke Detail Transaksi & Potong Stok
            foreach ($_SESSION['cart'] as $c) {
                $id_prod  = $c['id_produk'];
                $harga    = $c['harga_jual'];
                $qty      = $c['qty'];
                $subtotal = $harga * $qty;

                mysqli_query($koneksi, "INSERT INTO tb_detail_transaksi (id_transaksi, id_produk, harga_satuan, jumlah, subtotal) 
                                        VALUES ($id_transaksi_baru, $id_prod, $harga, $qty, $subtotal)");

                // Potong Stok Produk
                mysqli_query($koneksi, "UPDATE tb_produk SET stok = stok - $qty WHERE id_produk = $id_prod");
            }

            // Kosongkan keranjang & alihkan ke cetak nota
            $_SESSION['cart'] = [];
            echo "<script>
                    alert('Transaksi Berhasil Disimpan!');
                    window.open('cetak_nota.php?id=$id_transaksi_baru', '_blank');
                    window.location.href = 'transaksi.php';
                  </script>";
            exit;
        }
    } else {
        echo "<script>alert('Gagal memproses transaksi! Pastikan keranjang tidak kosong dan jumlah uang tunai mencukupi.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Kasir (POS) - Toko Olahraga ALTERA</title>
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
        .total-display { background: #0f172a; color: #22c55e; border-radius: 8px; padding: 1.5rem; text-align: right; }
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

        <!-- KONTEN UTAMA POS -->
        <main class="col-md-9 ms-sm-auto col-lg-10 ps-md-3 pe-md-4 py-3">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-calculator me-2"></i>Point of Sale (Kasir)</h2>
                <span class="badge bg-secondary p-2">Operator: <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            </div>

            <div class="row g-4">
                <!-- PANEL KIRI: PILIH BARANG -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm p-3 mb-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-search me-1"></i>Pilih Produk</h6>
                        
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Cari & Pilih Produk</label>
                                <select name="id_produk" class="form-select" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php
                                    $q_prod = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE stok > 0 ORDER BY nama_produk ASC");
                                    while ($p = mysqli_fetch_assoc($q_prod)) :
                                    ?>
                                        <option value="<?= $p['id_produk']; ?>">
                                            <?= $p['kode_produk']; ?> - <?= $p['nama_produk']; ?> (Stok: <?= $p['stok']; ?>) - Rp <?= number_format($p['harga_jual'], 0, ',', '.'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted">Jumlah (Qty)</label>
                                <input type="number" name="qty" class="form-control" value="1" min="1" required>
                            </div>

                            <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg me-1"></i> Tambahkan ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>

                <!-- PANEL KANAN: DAFTAR BELANJA & PEMBAYARAN -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm p-3 mb-4">
                        
                        <!-- TABEL KERANJANG -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-cart3 me-1"></i>Item Belanjaan</h6>
                            <?php if (!empty($_SESSION['cart'])) : ?>
                                <form action="" method="POST" class="d-inline">
                                    <button type="submit" name="clear_cart" class="btn btn-outline-danger btn-sm" onclick="return confirm('Kosongkan keranjang?')">
                                        <i class="bi bi-trash"></i> Kosongkan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="table-responsive mb-3" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga Satuan</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $grand_total = 0;
                                    if (!empty($_SESSION['cart'])) :
                                        foreach ($_SESSION['cart'] as $index => $item) :
                                            $subtotal = $item['harga_jual'] * $item['qty'];
                                            $grand_total += $subtotal;
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="d-block"><?= htmlspecialchars($item['nama_produk']); ?></strong>
                                            <small class="text-muted"><?= $item['kode_produk']; ?></small>
                                        </td>
                                        <td>Rp <?= number_format($item['harga_jual'], 0, ',', '.'); ?></td>
                                        <td><?= $item['qty']; ?></td>
                                        <td class="fw-bold">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <a href="transaksi.php?action=delete&index=<?= $index; ?>" class="text-danger">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Keranjang belanja masih kosong.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- DISPLAY GRAND TOTAL -->
                        <div class="total-display mb-3">
                            <small class="text-white-50 d-block fw-bold mb-1">TOTAL BELANJA</small>
                            <h2 class="fw-bold mb-0" id="text_total">Rp <?= number_format($grand_total, 0, ',', '.'); ?></h2>
                        </div>

                        <!-- FORM HITUNG PEMBAYARAN -->
                        <form action="" method="POST">
                            <input type="hidden" id="grand_total_val" value="<?= $grand_total; ?>">
                            
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Uang Tunai (Rp)</label>
                                    <input type="number" name="nominal_uang" id="nominal_uang" class="form-control form-control-lg" placeholder="0" required min="<?= $grand_total; ?>" oninput="hitungKembalian()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kembalian (Rp)</label>
                                    <input type="text" id="kembalian_text" class="form-control form-control-lg bg-light text-danger fw-bold" readonly value="Rp 0">
                                </div>
                            </div>

                            <button type="submit" name="process_checkout" class="btn btn-success btn-lg w-100 shadow-sm" <?= empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                                <i class="bi bi-printer me-1"></i> Memproses Transaksi & Cetak Nota
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- JAVASCRIPT REKAP PEMBAYARAN -->
<script>
function hitungKembalian() {
    const grandTotal = parseInt(document.getElementById('grand_total_val').value) || 0;
    const nominalUang = parseInt(document.getElementById('nominal_uang').value) || 0;
    const kembalianText = document.getElementById('kembalian_text');

    if (nominalUang >= grandTotal) {
        const kembalian = nominalUang - grandTotal;
        kembalianText.value = 'Rp ' + kembalian.toLocaleString('id-ID');
    } else {
        kembalianText.value = 'Uang kurang!';
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>