<?php
// sidebar.php
$page_aktif = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? '';
?>

<!-- STYLE KHUSUS SIDEBAR (Presisi, Tanpa Wrap & Rapi) -->
<style>
    /* Mengatur lebar sidebar menjadi presisi tanpa membuang ruang kosong */
    .sidebar-custom {
        width: 210px !important;
        min-width: 210px !important;
        max-width: 210px !important;
        min-height: 100vh;
        background-color: #0f172a !important;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        padding: 0.75rem 0.6rem !important;
        flex-shrink: 0;
    }
    
    /* Header Brand */
    .sidebar-brand {
        padding: 0.5rem 0.25rem;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }
    .sidebar-brand h6 {
        font-size: 0.95rem;
        letter-spacing: 0.5px;
    }
    .sidebar-brand small {
        white-space: nowrap; /* Mencegah slogan tertekuk */
        font-size: 0.7rem;
    }
    
    /* Item Menu Link */
    .sidebar-custom .nav-link {
        color: #94a3b8;
        padding: 0.55rem 0.65rem;
        border-radius: 6px;
        margin-bottom: 0.2rem;
        font-size: 0.825rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        text-decoration: none;
        white-space: nowrap; /* Mencegah teks menu tertekuk menjadi 2 baris */
        transition: all 0.15s ease-in-out;
    }
    .sidebar-custom .nav-link:hover {
        color: #ffffff;
        background-color: #1e293b;
    }
    .sidebar-custom .nav-link.active {
        color: #ffffff !important;
        background-color: #1e293b !important;
        font-weight: 600;
    }
    .sidebar-custom .nav-link i {
        margin-right: 10px;
        font-size: 1rem;
        width: 18px;
        text-align: center;
        flex-shrink: 0;
    }
    
    /* Tombol Logout Rapi di Bawah */
    .sidebar-footer {
        margin-top: auto;
        padding-top: 0.75rem;
        padding-bottom: 0.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
    }
    .btn-logout {
        border-color: rgba(239, 68, 68, 0.4);
        color: #ef4444;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.45rem 0.5rem;
        border-radius: 6px;
        white-space: nowrap; /* Mencegah teks logout tertekuk */
    }
    .btn-logout:hover {
        background-color: #ef4444;
        border-color: #ef4444;
        color: #ffffff;
    }
</style>

<!-- NAVIGASI SIDEBAR -->
<nav class="sidebar-custom collapse d-md-block">
    
    <!-- Brand / Header Toko -->
    <div class="sidebar-brand text-center">
        <h6 class="fw-bold text-white mb-0">ALTERA SPORT</h6>
        <small class="text-secondary" style="font-style: italic;">Lengkapi Style Sportymu</small>
    </div>
    
    <!-- Daftar Menu Sederhana -->
    <ul class="nav flex-column flex-grow-1">
        
        <!-- MENU UMUM (KASIR & MANAJER) -->
        <li class="nav-item">
            <a class="nav-link <?= ($page_aktif == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= ($page_aktif == 'transaksi.php') ? 'active' : ''; ?>" href="transaksi.php">
                <i class="bi bi-cart-check"></i> Transaksi Kasir
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= ($page_aktif == 'riwayat_kasir.php') ? 'active' : ''; ?>" href="riwayat_kasir.php">
                <i class="bi bi-clock-history"></i> Riwayat Transaksi
            </a>
        </li>

        <!-- MENU KHUSUS MANAJER -->
        <?php if ($user_role === 'Manajer') : ?>
        
        <li class="nav-item">
            <a class="nav-link <?= ($page_aktif == 'produk.php') ? 'active' : ''; ?>" href="produk.php">
                <i class="bi bi-box-seam"></i> Data Produk
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= ($page_aktif == 'kategori.php') ? 'active' : ''; ?>" href="kategori.php">
                <i class="bi bi-tags"></i> Kategori Produk
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= ($page_aktif == 'laporan.php') ? 'active' : ''; ?>" href="laporan.php">
                <i class="bi bi-file-earmark-bar-graph"></i> Laporan Penjualan
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= ($page_aktif == 'user.php') ? 'active' : ''; ?>" href="user.php">
                <i class="bi bi-people"></i> Kelola Staf User
            </a>
        </li>
        
        <?php endif; ?>

    </ul>

    <!-- Area Logout -->
    <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline-danger btn-logout w-100 d-flex align-items-center justify-content-center" onclick="return confirm('Yakin ingin keluar dari sistem?')">
            <i class="bi bi-box-arrow-left me-1"></i> Keluar (Logout)
        </a>
    </div>

</nav>