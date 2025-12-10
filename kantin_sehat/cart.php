<?php
error_reporting(0); // Matikan semua error
session_start();
include 'includes/config.php';

// CEK APAKAH USER SUDAH LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang - Kantin Sehat</title>
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
            max-width: 1000px;
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
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo span {
            color: #73AF6F;
        }
        
        .logo::before {
            content: "🛒";
            font-size: 32px;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .btn-back {
            background: linear-gradient(45deg, #73AF6F, green);
            color: white;
        }
        
        .btn-back:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-logout {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .btn-logout:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.3);
        }
        
        /* KERANJANG */
        .cart-title {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .cart-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 4px;
            background: linear-gradient(to right, #73AF6F, #E7DEAF);
            border-radius: 2px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
            border: 2px dashed #E7DEAF;
        }
        
        .empty-cart-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: #E7DEAF;
        }
        
        .empty-cart h3 {
            color: #7f8c8d;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        /* TABLE KERANJANG */
        .cart-table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
            margin-bottom: 30px;
            border: 1px solid rgba(115, 175, 111, 0.1);
        }
        
        .cart-table thead {
            background: linear-gradient(45deg, #73AF6F, #5a9c56);
            color: white;
        }
        
        .cart-table th {
            padding: 20px;
            text-align: left;
            font-weight: 600;
            font-size: 16px;
        }
        
        .cart-table td {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .cart-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #E7DEAF, #f5f1e0);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #73AF6F;
            font-size: 18px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        
        .product-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .product-price {
            color: #73AF6F;
            font-size: 14px;
            font-weight: 600;
        }
        
        /* QUANTITY CONTROLS */
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .qty-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: #E7DEAF;
            color: #73AF6F;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .qty-btn:hover {
            background: #73AF6F;
            color: white;
            transform: scale(1.1);
        }
        
        .qty-input {
            width: 60px;
            text-align: center;
            padding: 10px;
            border: 2px solid #E7DEAF;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            color: #2c3e50;
        }
        
        .qty-input:focus {
            outline: none;
            border-color: #73AF6F;
            box-shadow: 0 0 0 3px rgba(115, 175, 111, 0.2);
        }
        
        /* DELETE BUTTON */
        .delete-btn {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.2);
        }
        
        .delete-btn:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(231, 76, 60, 0.3);
        }
        
        /* SUBTOTAL & TOTAL */
        .subtotal, .total-price {
            font-weight: bold;
            font-size: 18px;
            color: #73AF6F;
        }
        
        /* SUMMARY */
        .cart-summary {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
            border: 1px solid rgba(115, 175, 111, 0.1);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .summary-label {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .summary-value {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .grand-total {
            font-size: 24px;
            color: #73AF6F;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #E7DEAF;
        }
        
        .grand-total .summary-label {
            font-size: 24px;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .grand-total .summary-value {
            font-size: 24px;
            color: #73AF6F;
            font-weight: 800;
        }
        
        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px;
        }
        
        .btn-clear {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            padding: 18px 30px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
        }
        
        .btn-clear:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.4);
        }
        
        .btn-checkout {
            background: linear-gradient(45deg, #73AF6F, #5a9c56);
            color: white;
            padding: 18px 40px;
            border-radius: 12px;
            border: none;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(115, 175, 111, 0.3);
        }
        
        .btn-checkout:hover {
            background: linear-gradient(45deg, #5a9c56, #488345);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(115, 175, 111, 0.4);
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
        
        .notification.info {
            background: linear-gradient(45deg, #3498db, #2980b9);
            border-left: 5px solid #1c6ea4;
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
        
        .notification.info::before {
            content: "ℹ️";
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .cart-table {
                font-size: 14px;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 12px 8px;
            }
            
            .product-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .product-img {
                width: 50px;
                height: 50px;
                font-size: 16px;
            }
            
            .quantity-controls {
                justify-content: center;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .cart-title {
                font-size: 26px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .cart-table {
                overflow-x: auto;
                display: block;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .cart-title {
                font-size: 22px;
            }
        }
        /* ===== LOGO STYLING ===== */
.logo-container {
    display: flex;
    align-items: center;
}

.site-logo {
    height: 50px;
    width: auto;
    max-width: 250px;
    transition: all 0.3s ease;
}

.site-logo:hover {
    transform: scale(1.05);
    opacity: 0.9;
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
    
    <div class="nav-buttons">
        <a href="menu.php" class="btn btn-back">⬅ Kembali ke Menu</a>
        <a href="logout.php" class="btn btn-logout">🚪 Logout</a>
    </div>
</div>
        
        <!-- JUDUL -->
        <h1 class="cart-title">🛒 Keranjang Belanja</h1>
        
        <!-- KERANJANG KOSONG -->
        <div id="emptyCart" class="empty-cart" style="display: none;">
            <div class="empty-cart-icon">🛒</div>
            <h3>Keranjang belanja Anda masih kosong</h3>
            <a href="menu.php" class="btn btn-back" style="padding: 12px 30px;">
                🛍️ Mulai Belanja
            </a>
        </div>
        
        <!-- TABEL KERANJANG -->
        <div id="cartContent">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th width="40%">Produk</th>
                        <th width="20%">Harga</th>
                        <th width="20%">Jumlah</th>
                        <th width="15%">Subtotal</th>
                        <th width="5%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="cartItems">
                    <!-- Item keranjang akan dimuat via JavaScript -->
                </tbody>
            </table>
            
            <!-- RINGKASAN -->
            <div class="cart-summary">
                <div class="summary-row">
                    <span class="summary-label">Total Item:</span>
                    <span class="summary-value" id="totalItems">0</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total Harga:</span>
                    <span class="summary-value" id="totalPrice">Rp 0</span>
                </div>
                <div class="summary-row grand-total">
                    <span class="summary-label">Grand Total:</span>
                    <span class="summary-value" id="grandTotal">Rp 0</span>
                </div>
                
                <!-- TOMBOL AKSI -->
                <div class="action-buttons">
                    <button class="btn-clear" onclick="clearCart()">
                        🗑️ Kosongkan Keranjang
                    </button>
                    <button class="btn-checkout" onclick="checkout()">
                        💳 Lanjut ke Pembayaran
                    </button>
                </div>
            </div>
        </div>
    
    <!-- NOTIFICATION -->
    <div class="notification" id="notification"></div>
    
    <script>
        // AMBIL DATA KERANJANG DARI LOCALSTORAGE
        let cart = JSON.parse(localStorage.getItem('kantin_cart')) || [];
        
        // LOAD KERANJANG SAAT HALAMAN DIBUKA
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
        });
        
        // LOAD DATA KERANJANG
        function loadCart() {
            const cartItems = document.getElementById('cartItems');
            const emptyCartDiv = document.getElementById('emptyCart');
            const cartContent = document.getElementById('cartContent');
            
            // CEK JIKA KERANJANG KOSONG
            if (cart.length === 0) {
                emptyCartDiv.style.display = 'block';
                cartContent.style.display = 'none';
                return;
            }
            
            emptyCartDiv.style.display = 'none';
            cartContent.style.display = 'block';
            
            // KOSONGKAN TABEL
            cartItems.innerHTML = '';
            
            let totalItems = 0;
            let totalPrice = 0;
            
            // TAMPILKAN SETIAP ITEM
            cart.forEach((item, index) => {
                totalItems += item.qty;
                totalPrice += item.subtotal;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="product-info">
                            <div class="product-img">${item.nama.substring(0, 2).toUpperCase()}</div>
                            <div>
                                <div class="product-name">${item.nama}</div>
                                <div class="product-price">Rp ${formatNumber(item.harga)}</div>
                            </div>
                        </div>
                    </td>
                    <td>Rp ${formatNumber(item.harga)}</td>
                    <td>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                            <input type="number" class="qty-input" value="${item.qty}" 
                                   min="1" max="99" 
                                   onchange="updateQtyManual(${index}, this.value)">
                            <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td class="subtotal">Rp ${formatNumber(item.subtotal)}</td>
                    <td>
                        <button class="delete-btn" onclick="removeItem(${index})">Hapus</button>
                    </td>
                `;
                cartItems.appendChild(row);
            });
            
            // UPDATE TOTAL
            document.getElementById('totalItems').textContent = totalItems;
            document.getElementById('totalPrice').textContent = `Rp ${formatNumber(totalPrice)}`;
            document.getElementById('grandTotal').textContent = `Rp ${formatNumber(totalPrice)}`;
        }
        
        // UPDATE QUANTITY
        function updateQty(index, change) {
            const newQty = cart[index].qty + change;
            
            if (newQty < 1) {
                removeItem(index);
                return;
            }
            
            // Cek stok (dummy check - di real app cek ke database)
            if (newQty > 99) {
                showNotification('Maksimal 99 item per produk', 'error');
                return;
            }
            
            cart[index].qty = newQty;
            cart[index].subtotal = cart[index].harga * newQty;
            
            saveAndReload();
            showNotification(`Jumlah ${cart[index].nama} diubah menjadi ${newQty}`, 'info');
        }
        
        // UPDATE QUANTITY MANUAL (input langsung)
        function updateQtyManual(index, value) {
            const newQty = parseInt(value);
            
            if (isNaN(newQty) || newQty < 1) {
                cart[index].qty = 1;
            } else if (newQty > 99) {
                cart[index].qty = 99;
                showNotification('Maksimal 99 item per produk', 'error');
            } else {
                cart[index].qty = newQty;
            }
            
            cart[index].subtotal = cart[index].harga * cart[index].qty;
            saveAndReload();
        }
        
        // HAPUS ITEM
        function removeItem(index) {
            if (confirm(`Hapus "${cart[index].nama}" dari keranjang?`)) {
                const itemName = cart[index].nama;
                cart.splice(index, 1);
                saveAndReload();
                showNotification(`"${itemName}" dihapus dari keranjang`, 'success');
            }
        }
        
        // KOSONGKAN KERANJANG
        function clearCart() {
            if (cart.length === 0) {
                showNotification('Keranjang sudah kosong', 'info');
                return;
            }
            
            if (confirm('Kosongkan seluruh keranjang?')) {
                cart = [];
                saveAndReload();
                showNotification('Keranjang dikosongkan', 'success');
            }
        }
        
        // CHECKOUT -> KE HALAMAN TRANSAKSI
        function checkout() {
            if (cart.length === 0) {
                showNotification('Keranjang masih kosong', 'error');
                return;
            }
            
            // Simpan cart ke sessionStorage untuk dibawa ke halaman transaksi
            sessionStorage.setItem('checkout_cart', JSON.stringify(cart));
            
            // Redirect ke halaman transaksi
            window.location.href = 'transaction.php';
        }
        
        // SIMPAN KE LOCALSTORAGE & RELOAD
        function saveAndReload() {
            localStorage.setItem('kantin_cart', JSON.stringify(cart));
            loadCart();
        }
        
        // FORMAT ANGKA (1000 -> 1.000)
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
    </script>
</body>
</html>