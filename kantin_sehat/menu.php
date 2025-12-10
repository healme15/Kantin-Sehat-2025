<?php
error_reporting(0); // Matikan semua error
@session_start();   // Suppress error jika ada

include 'includes/config.php';

// CEK APAKAH USER SUDAH LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// AMBIL DATA PRODUK DARI DATABASE
$query = "SELECT * FROM products ORDER BY id";
$result = mysqli_query($conn, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menu Produk - Kantin Sehat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(135deg, rgba(115, 175, 111, 0.05) 0%, rgba(231, 222, 175, 0.05) 100%);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* HEADER */
        .header {
        display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
            margin-bottom: 30px;
            border-left: 5px solid #73AF6F;
            
            /* Sticky */
            position: sticky;
            top: 20px;
            z-index: 1000;
            
            /* Transisi untuk efek smooth */
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .header.scrolled {
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(115, 175, 111, 0.2);
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        /* Logo Styling untuk Menu Page */
.logo-container {
    display: flex;
    align-items: center;
}

.site-logo {
    height: 50px; /* Sesuaikan tinggi logo */
    width: auto;
    max-width: 250px; /* Batas maksimal lebar */
    transition: transform 0.3s ease;
}

.site-logo:hover {
    transform: scale(1.05);
}

/* Untuk screen readers */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .site-logo {
        height: 40px;
        max-width: 200px;
    }
}

@media (max-width: 480px) {
    .site-logo {
        height: 35px;
        max-width: 180px;
    }
}
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 24px;
            color: #73AF6F;
            background-color: #E7DEAF;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .cart-icon:hover {
            background-color: #73AF6F;
            color: white;
            transform: scale(1.1);
        }
        
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }
        
        .username {
            font-weight: 600;
            color: #2c3e50;
            background-color: #E7DEAF;
            padding: 8px 20px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        
        .username::before {
            content: "👤";
        }
        
        .logout-btn {
            background: linear-gradient(45deg, #73AF6F, #5a9c56);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.5s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(115, 175, 111, 0.3);
        }
        
        .logout-btn:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(115, 175, 111, 0.4);
            transition : 0.5s;
        }
        
        /* PRODUCT GRID */
        .products-title {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 32px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .products-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 4px;
            background: linear-gradient(to right, #73AF6F, #E7DEAF);
            border-radius: 2px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
            transition: all 0.3s;
            border: 1px solid rgba(115, 175, 111, 0.1);
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(115, 175, 111, 0.2);
        }
        
        .product-image {
            height: 180px;
            background: linear-gradient(135deg, #E7DEAF, #f5f1e0);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .product-image::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .product-img {
            max-width: 100%;
            max-height: 140px;
            object-fit: contain;
            position: relative;
            z-index: 1;
        }
        
        .product-info {
            padding: 25px;
        }
        
        .product-name {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        
        .product-type {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        
        .minuman { 
            background: rgba(52, 152, 219, 0.15); 
            color: #2980b9; 
            border-left: 4px solid #3498db;
        }
        .makanan { 
            background: rgba(46, 204, 113, 0.15); 
            color: #27ae60; 
            border-left: 4px solid #2ecc71;
        }
        .snack { 
            background: rgba(155, 89, 182, 0.15); 
            color: #8e44ad; 
            border-left: 4px solid #9b59b6;
        }
        
        .product-price {
            font-size: 26px;
            font-weight: bold;
            color: #73AF6F;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .product-price::before {
            content: "💰";
            font-size: 20px;
        }
        
        .product-size {
            color: #7f8c8d;
            margin-bottom: 12px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .product-size::before {
            content: "📏";
        }
        
        .product-stock {
            color: #3498db;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .product-stock::before {
            content: "📦";
        }
        
        .add-to-cart {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, #73AF6F, #5a9c56);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(115, 175, 111, 0.3);
        }
        
        .add-to-cart:hover {
            background: linear-gradient(45deg, #5a9c56, #488345);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(115, 175, 111, 0.4);
        }
        
        .add-to-cart:active {
            transform: translateY(0);
        }
        
        .cart-icon-btn {
            font-size: 18px;
        }
        
        /* FOOTER */
        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 25px;
            color: #7f8c8d;
            font-size: 14px;
            background-color: #E7DEAF;
            border-radius: 15px;
            border-top: 4px solid #73AF6F;
        }
        
        /* NOTIFICATION */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 18px 25px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 350px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .notification.success {
            background: linear-gradient(45deg, #73AF6F, #5a9c56);
            border-left: 5px solid #488345;
        }
        
        .notification.error {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            border-left: 5px solid #a93226;
        }
        
        .notification::before {
            font-size: 20px;
        }
        
        .notification.success::before {
            content: "✅";
        }
        
        .notification.error::before {
            content: "❌";
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .user-info {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .products-title {
                font-size: 28px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .product-card {
                margin: 0 10px;
            }
        }

        /* Untuk gambar produk asli */
.product-img-real {
    width: 100%;
    height: 180px;
    object-fit: contain; /* Kontain agar gambar utuh terlihat */
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px 12px 0 0;
    transition: transform 0.3s ease;
}

.product-img-real:hover {
    transform: scale(1.03);
}

/* Container untuk memastikan gambar proporsional */
.product-image {
    height: 180px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px 12px 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

/* Fallback placeholder */
.product-img-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: bold;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Warna placeholder berdasarkan jenis */
.product-img-placeholder.minuman {
    background: linear-gradient(135deg, #3498db, #2980b9);
}

.product-img-placeholder.makanan {
    background: linear-gradient(135deg, #73AF6F, #5a9c56);
}

.product-img-placeholder.snack {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
}
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
    <!-- LOGO KANTIN SEHAT -->
    <div class="logo-container">
        <img src="assets/images/logo.png" alt="Kantin Sehat" class="site-logo">
    </div>
    
    <div class="user-info">
        <div class="cart-icon" onclick="goToCart()">
            🛒
            <div class="cart-count" id="cartCount">0</div>
        </div>
        <div class="username"> <?php echo $_SESSION['username']; ?></div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>
        
        <!-- JUDUL -->
        <h1 class="products-title">Menu Produk Kantin</h1>
        
        <!-- PRODUK GRID -->
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
            <div class="product-image">
                <?php
                $productName = $product['nama'];
                $initials = strtoupper(substr($productName, 0, 2));
                $type = strtolower($product['jenis']);
                
                // Mapping manual nama produk ke file gambar
                // PERHATIKAN: 'Pop Mie Mini' (dari database) → 'popmie-mini.png' (file gambar)
                $imageMapping = [
                    'Aqua' => 'aqua.png',
                    'Beng-Beng Wafer' => 'beng-beng-wafer.png',  // Perhatikan dash di database
                    'Milo UHT' => 'milo-uht.png',
                    'Pop Mie Mini' => 'popmie-mini.png',
                    'Sari Roti Tawar Kupas' => 'sari-roti-tawar-kupas.png',
                ];
                
                // Cari di mapping
                if (isset($imageMapping[$productName])) {
                    $imageName = $imageMapping[$productName];
                } else {
                    // Fallback ke format otomatis
                    $imageName = strtolower(str_replace(' ', '-', $productName)) . '.png';
                }
                
                $imagePath = "assets/images/products/" . $imageName;
                $hasImage = file_exists($imagePath);
                ?>
                
                <?php if ($hasImage): ?>
                    <img src="<?php echo $imagePath; ?>" 
                        alt="<?php echo htmlspecialchars($productName); ?>"
                        class="product-img-real"
                        loading="lazy">
                <?php else: ?>
                    <div class="product-img-placeholder product-<?php echo $type; ?>">
                        <?php echo $initials; ?>
                        <small style="font-size: 12px; display: block; margin-top: 5px;">
                            <?php echo $productName; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
                
                <div class="product-info">
                    <h3 class="product-name"><?php echo $product['nama']; ?></h3>
                    <span class="product-type <?php echo strtolower($product['jenis']); ?>">
                        <?php echo $product['jenis']; ?>
                    </span>
                    
                    <div class="product-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></div>
                    <div class="product-size"><?php echo $product['ukuran']; ?></div>
                    <div class="product-stock">Stok: <?php echo $product['stok']; ?></div>
                    
                    <button class="add-to-cart" onclick="addToCart(
                        <?php echo $product['id']; ?>,
                        '<?php echo addslashes($product['nama']); ?>',
                        <?php echo $product['harga']; ?>,
                        <?php echo $product['stok']; ?>
                    )">
                        <span class="cart-icon-btn">🛒</span> Pilih Produk
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- FOOTER -->
        <div class="footer">
            2025 Kantin Sehat - Atailah Hilmy
        </div>
    </div>
    
    <!-- NOTIFICATION -->
    <div class="notification" id="notification"></div>
    
    <script>
        // KERANJANG DI LOCALSTORAGE
        let cart = JSON.parse(localStorage.getItem('kantin_cart')) || [];
        
        // UPDATE CART COUNT
        function updateCartCount() {
            const total = cart.reduce((sum, item) => sum + item.qty, 0);
            document.getElementById('cartCount').textContent = total;
        }
        
        // TAMBAH KE KERANJANG
        function addToCart(id, nama, harga, stok) {
            // Cek apakah produk sudah ada di keranjang
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                // Cek stok
                if (existingItem.qty >= stok) {
                    showNotification('Stok tidak mencukupi!', 'error');
                    return;
                }
                existingItem.qty += 1;
                existingItem.subtotal = existingItem.qty * harga;
            } else {
                // Tambah item baru
                cart.push({
                    id: id,
                    nama: nama,
                    harga: harga,
                    qty: 1,
                    subtotal: harga
                });
            }
            
            // Simpan ke localStorage
            localStorage.setItem('kantin_cart', JSON.stringify(cart));
            
            // Update counter
            updateCartCount();
            
            // Tampilkan notifikasi
            showNotification(`"${nama}" ditambahkan ke keranjang!`, 'success');
        }
        
        // KE HALAMAN KERANJANG
        function goToCart() {
            if (cart.length === 0) {
                showNotification('Keranjang masih kosong!', 'error');
                return;
            }
            window.location.href = 'cart.php';
        }
        
        // NOTIFIKASI
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        // INITIAL LOAD
        updateCartCount();

        function addToCart(id, nama, harga, stok) {
            // Cek stok sebelum tambah ke keranjang
            if (stok <= 0) {
                showNotification(`❌ ${nama} stok habis!`, 'error');
                return;
            }
            
            // Cari item di keranjang
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                // Jika sudah ada, cek stok lagi
                if (existingItem.qty >= stok) {
                    showNotification(`❌ Stok ${nama} hanya ${stok}!`, 'error');
                    return;
                }
                existingItem.qty += 1;
                existingItem.subtotal = existingItem.qty * harga;
            } else {
                // Item baru
                cart.push({
                    id: id,
                    nama: nama,
                    harga: harga,
                    qty: 1,
                    subtotal: harga
                });
            }
            
            localStorage.setItem('kantin_cart', JSON.stringify(cart));
            updateCartCount();
            showNotification(`✅ "${nama}" ditambahkan ke keranjang!`, 'success');
        }
        // Efek scroll untuk header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>