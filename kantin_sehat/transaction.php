<?php
error_reporting(0); // Matikan semua error
session_start();
include 'includes/config.php';

// CEK APAKAH USER SUDAH LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = '';
$success = '';

// AMBIL DATA KERANJANG DARI SESSION (JavaScript akan kirim via form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    // Decode data keranjang dari JSON
    $cart_data = json_decode($_POST['cart_data'], true);
    $total = $_POST['total'];
    
    if (!empty($cart_data)) {
        // 1. SIMPAN TRANSAKSI KE TABLE transactions
        $query_transaction = "INSERT INTO transactions (user_id, total) VALUES ('$user_id', '$total')";
        
        if (mysqli_query($conn, $query_transaction)) {
            $transaction_id = mysqli_insert_id($conn);
            
            // 2. SIMPAN DETAIL ITEM KE TABLE transaction_details
            foreach ($cart_data as $item) {
                $product_id = $item['id'];
                $qty = $item['qty'];
                $subtotal = $item['subtotal'];
                
                $query_detail = "INSERT INTO transaction_details 
                                (transaction_id, product_id, qty, subtotal) 
                                VALUES ('$transaction_id', '$product_id', '$qty', '$subtotal')";
                mysqli_query($conn, $query_detail);
                
                // 3. UPDATE STOK PRODUK (optional)
                $query_update_stok = "UPDATE products SET stok = stok - $qty WHERE id = $product_id";
                mysqli_query($conn, $query_update_stok);
            }
            
            // 4. KOSONGKAN KERANJANG & REDIRECT KE STRUK
            $_SESSION['last_transaction_id'] = $transaction_id;
            $_SESSION['transaction_total'] = $total;
            header("Location: struk.php");
            exit();
            
        } else {
            $error = "Gagal menyimpan transaksi: " . mysqli_error($conn);
        }
    } else {
        $error = "Keranjang kosong!";
    }
}

// AMBIL DATA USER UNTUK DITAMPILKAN
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($conn, $query_user);
$user_data = mysqli_fetch_assoc($result_user);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Kantin Sehat</title>
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
        
        .logo img {
            height: 50px;
            width: auto;
            transition: all 0.3s ease;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }

        .logo-text span {
            color: #28a745; /* warna untuk "SEHAT" */
        }

        .logo img:hover {
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 25px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .user-badge {
            background-color: #E7DEAF;
            padding: 8px 20px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        
        .user-badge::before {
            content: "👤";
        }
        
        .user-id {
            background-color: #73AF6F;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
        }
        
        /* JUDUL */
        .page-title {
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
        
        .page-title::after {
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
        
        /* ERROR/SUCCESS MESSAGE */
        .message {
            padding: 18px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .error {
            background: linear-gradient(45deg, #fde8e8, #fad2d2);
            color: #c53030;
            border-left: 5px solid #e74c3c;
        }
        
        .success {
            background: linear-gradient(45deg, #e8f5e9, #d4edda);
            color: #155724;
            border-left: 5px solid #73AF6F;
        }
        
        /* LAYOUT DUA KOLOM */
        .checkout-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        /* KOLOM KIRI: RINGKASAN BELANJA */
        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
            border: 1px solid rgba(115, 175, 111, 0.1);
        }
        
        .summary-title {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #E7DEAF;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
        }
        
        .summary-title::before {
            content: "📋";
        }
        
        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 25px;
            padding-right: 10px;
        }
        
        .order-items::-webkit-scrollbar {
            width: 6px;
        }
        
        .order-items::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .order-items::-webkit-scrollbar-thumb {
            background: #73AF6F;
            border-radius: 10px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .order-item:hover {
            background-color: #f9f9f9;
            transform: translateX(5px);
        }
        
        .item-name {
            color: #2c3e50;
            font-weight: 600;
            font-size: 16px;
        }
        
        .item-qty {
            color: #73AF6F;
            font-size: 14px;
            font-weight: 500;
            margin-top: 5px;
        }
        
        .item-price {
            font-weight: 700;
            color: #73AF6F;
            font-size: 16px;
        }
        
        /* TOTAL SECTION */
        .total-section {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #E7DEAF;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .total-row .label {
            color: #2c3e50;
        }
        
        .total-row .value {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .grand-total {
            font-size: 26px;
            font-weight: 800;
            color: #73AF6F;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 3px solid #E7DEAF;
        }
        
        /* KOLOM KANAN: DATA TRANSAKSI */
        .transaction-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
            border: 1px solid rgba(115, 175, 111, 0.1);
        }
        
        .form-title {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #E7DEAF;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
        }
        
        .form-title::before {
            content: "📝";
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 16px;
        }
        
        .form-input, .form-disabled, textarea.form-input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #E7DEAF;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: white;
        }
        
        .form-input:focus, textarea.form-input:focus {
            outline: none;
            border-color: #73AF6F;
            box-shadow: 0 0 0 3px rgba(115, 175, 111, 0.2);
        }
        
        .form-disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #ddd;
        }
        
        textarea.form-input {
            resize: vertical;
            min-height: 100px;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .payment-method {
            padding: 20px;
            border: 2px solid #E7DEAF;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .payment-method:hover {
            border-color: #73AF6F;
            background: #f9f9f9;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.1);
        }
        
        .payment-method.selected {
            border-color: #73AF6F;
            background: linear-gradient(45deg, #f8f9fa, #E7DEAF);
            box-shadow: 0 5px 15px rgba(115, 175, 111, 0.2);
        }
        
        .payment-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .payment-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }
        
        /* TOMBOL ACTION */
        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 35px;
        }
        
        .btn {
            flex: 1;
            padding: 18px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .btn-cancel {
            background: white;
            color: #c0392b;
            border: 2px solid #c0392b;
        }
        
        .btn-cancel:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            border-color: #a93226;
        }
        
        .btn-submit {
            background: linear-gradient(45deg, #73AF6F, #5a9c56);
            color: white;
        }
        
        .btn-submit:hover {
            background: linear-gradient(45deg, #5a9c56, #488345);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(115, 175, 111, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
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
        
        /* LOADING OVERLAY */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 9999;
            display: none;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #E7DEAF;
            border-top: 5px solid #73AF6F;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 25px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-overlay h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .loading-overlay p {
            color: #ddd;
            font-size: 16px;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .checkout-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .user-info {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-title {
                font-size: 26px;
            }
            
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .order-summary,
            .transaction-form {
                padding: 20px;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 22px;
            }
            
            .grand-total {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
        <div class="logo">
            <img src="assets/images/logo.png" alt="Kantin Sehat">
        </div>
            <div class="user-info">
                <div class="user-badge"><?php echo $username; ?></div>
                <div class="user-id">ID: <?php echo $user_id; ?></div>
            </div>
        </div>
        
        <!-- JUDUL -->
        <h1 class="page-title">💳 Checkout & Pembayaran</h1>
        
        <!-- ERROR/SUCCESS MESSAGE -->
        <?php if ($error): ?>
            <div class="message error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- LAYOUT DUA KOLOM -->
        <div class="checkout-layout">
            <!-- KOLOM KIRI: RINGKASAN BELANJA -->
            <div class="order-summary">
                <h2 class="summary-title">Ringkasan Pesanan</h2>
                
                <div class="order-items" id="orderItems">
                    <!-- Item akan diisi JavaScript -->
                </div>
                
                <div class="total-section">
                    <div class="total-row">
                        <span class="label">Total Item:</span>
                        <span class="value" id="totalItems">0</span>
                    </div>
                    <div class="total-row">
                        <span class="label">Total Harga:</span>
                        <span class="value" id="totalPrice">Rp 0</span>
                    </div>
                    <div class="grand-total total-row">
                        <span class="label">Grand Total:</span>
                        <span class="value" id="grandTotal">Rp 0</span>
                    </div>
                </div>
            </div>
            
            <!-- KOLOM KANAN: FORM TRANSAKSI -->
            <div class="transaction-form">
                <h2 class="form-title">Data Transaksi</h2>
                
                <form method="POST" id="checkoutForm">
                    <!-- Data keranjang akan dikirim via hidden input -->
                    <input type="hidden" name="cart_data" id="cartData">
                    <input type="hidden" name="total" id="totalInput">
                    
                    <div class="form-group">
                        <label class="form-label">Nama Pelanggan</label>
                        <input type="text" class="form-disabled" value="<?php echo $username; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="text" class="form-disabled" value="<?php echo date('d/m/Y H:i:s'); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Metode Pembayaran</label>
                        <div class="payment-methods">
                            <div class="payment-method selected" onclick="selectPayment('tunai')">
                                <div class="payment-icon">💵</div>
                                <div class="payment-name">Tunai</div>
                            </div>
                            <div class="payment-method" onclick="selectPayment('qris')">
                                <div class="payment-icon">📱</div>
                                <div class="payment-name">QRIS</div>
                            </div>
                            <div class="payment-method" onclick="selectPayment('debit')">
                                <div class="payment-icon">💳</div>
                                <div class="payment-name">Kartu Debit</div>
                            </div>
                            <div class="payment-method" onclick="selectPayment('kredit')">
                                <div class="payment-icon">🏦</div>
                                <div class="payment-name">Kartu Kredit</div>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" id="paymentMethod" value="tunai">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-input" name="note" rows="4" placeholder="Contoh: Bungkus rapat, kurang pedas, dll..."></textarea>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn btn-cancel" onclick="goBackToCart()">
                            ⬅ Kembali ke Keranjang
                        </button>
                        <button type="button" class="btn btn-submit" onclick="submitTransaction()">
                            ✅ Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    
    <!-- LOADING OVERLAY -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <h3>Menyimpan Transaksi...</h3>
        <p>Harap tunggu sebentar</p>
    </div>
    
    <script>
        // AMBIL DATA KERANJANG DARI LOCALSTORAGE
        let cart = JSON.parse(localStorage.getItem('kantin_cart')) || [];
        let selectedPayment = 'tunai';
        
        // LOAD DATA SAAT HALAMAN DIBUKA
        document.addEventListener('DOMContentLoaded', function() {
            if (cart.length === 0) {
                alert('Keranjang kosong! Kembali ke menu.');
                window.location.href = 'menu.php';
                return;
            }
            
            loadOrderSummary();
        });
        
        // LOAD RINGKASAN PESANAN
        function loadOrderSummary() {
            const orderItems = document.getElementById('orderItems');
            let totalItems = 0;
            let totalPrice = 0;
            
            // KOSONGKAN DULU
            orderItems.innerHTML = '';
            
            // TAMPILKAN SETIAP ITEM
            cart.forEach(item => {
                totalItems += item.qty;
                totalPrice += item.subtotal;
                
                const itemElement = document.createElement('div');
                itemElement.className = 'order-item';
                itemElement.innerHTML = `
                    <div>
                        <div class="item-name">${item.nama}</div>
                        <div class="item-qty">${item.qty} x Rp ${formatNumber(item.harga)}</div>
                    </div>
                    <div class="item-price">Rp ${formatNumber(item.subtotal)}</div>
                `;
                orderItems.appendChild(itemElement);
            });
            
            // UPDATE TOTAL
            document.getElementById('totalItems').textContent = totalItems;
            document.getElementById('totalPrice').textContent = `Rp ${formatNumber(totalPrice)}`;
            document.getElementById('grandTotal').textContent = `Rp ${formatNumber(totalPrice)}`;
            
            // SIMPAN KE HIDDEN INPUT UNTUK DIKIRIM KE SERVER
            document.getElementById('cartData').value = JSON.stringify(cart);
            document.getElementById('totalInput').value = totalPrice;
        }
        
        // PILIH METODE PEMBAYARAN
        function selectPayment(method) {
            selectedPayment = method;
            document.getElementById('paymentMethod').value = method;
            
            // UPDATE UI
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }
        
        // KEMBALI KE KERANJANG
        function goBackToCart() {
            window.location.href = 'cart.php';
        }
        
        // SUBMIT TRANSAKSI
        function submitTransaction() {
            // VALIDASI
            if (cart.length === 0) {
                alert('Keranjang kosong!');
                return;
            }
            
            if (!confirm('Simpan transaksi ini? Transaksi tidak dapat dibatalkan setelah disimpan.')) {
                return;
            }
            
            // TAMPILKAN LOADING
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // KIRIM FORM
            setTimeout(() => {
                document.getElementById('checkoutForm').submit();
            }, 1500);
        }
        
        // FORMAT ANGKA
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
</body>
</html>