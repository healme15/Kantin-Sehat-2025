<?php
error_reporting(0); // Matikan semua error
session_start();
include 'includes/config.php';

// CEK APAKAH ADA TRANSAKSI TERAKHIR
if (!isset($_SESSION['last_transaction_id'])) {
    header("Location: menu.php");
    exit();
}

$transaction_id = $_SESSION['last_transaction_id'];
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// AMBIL DATA TRANSAKSI
$query = "SELECT t.*, u.username 
          FROM transactions t 
          JOIN users u ON t.user_id = u.id 
          WHERE t.id = '$transaction_id' AND t.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$transaction = mysqli_fetch_assoc($result);

// AMBIL DETAIL ITEM
$query_details = "SELECT td.*, p.nama, p.harga 
                  FROM transaction_details td 
                  JOIN products p ON td.product_id = p.id 
                  WHERE td.transaction_id = '$transaction_id'";
$result_details = mysqli_query($conn, $query_details);
$items = [];
while ($row = mysqli_fetch_assoc($result_details)) {
    $items[] = $row;
}

// HAPUS SESSION TRANSAKSI
unset($_SESSION['last_transaction_id']);
unset($_SESSION['transaction_total']);
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>Struk Transaksi - Kantin Sehat</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Courier New", monospace;
      }

      body {
        background: #f5f5f5;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
      }

      .struk-container {
        width: 400px;
        background: white;
        border: 2px dashed #ccc;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      }

      .header-struk {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #333;
      }

      .store-name {
        font-size: 24px;
        font-weight: bold;
        letter-spacing: 2px;
      }

      .store-address {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
      }

      .transaction-info {
        margin-bottom: 20px;
      }

      .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 14px;
      }

      .items-table {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
      }

      .items-table th {
        text-align: left;
        padding: 8px 0;
        border-bottom: 1px dashed #ccc;
        font-size: 14px;
      }

      .items-table td {
        padding: 8px 0;
        border-bottom: 1px dashed #eee;
        font-size: 13px;
      }

      .qty {
        text-align: center;
        width: 50px;
      }

      .price,
      .subtotal {
        text-align: right;
      }

      .total-section {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #333;
      }

      .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 15px;
      }

      .grand-total {
        font-size: 20px;
        font-weight: bold;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #333;
      }

      .footer-struk {
        text-align: center;
        margin-top: 30px;
        padding-top: 15px;
        border-top: 1px dashed #ccc;
        font-size: 12px;
        color: #666;
      }

      .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
      }

      .btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        display: inline-block;
      }

      .btn-print {
        background: #27ae60;
        color: white;
      }

      .btn-print:hover {
        background: #219653;
      }

      .btn-menu {
        background: #c0392b;
        color: white;
      }

      .btn-menu:hover {
        background: #a93226;
      }

      @media print {
        body {
          background: white;
        }
        .struk-container {
          border: none;
          box-shadow: none;
          width: 100%;
        }
        .action-buttons {
          display: none;
        }
      }
    </style>
  </head>
  <body>
    <div class="struk-container">
      <!-- HEADER STRUK -->
      <div class="header-struk">
        <div class="store-name">KANTIN SEHAT</div>
        <div class="store-address">Jl. Pahlawan No. 15, Kota Bandung</div>
        <div class="store-address">Telp: +62 855-9153-3494</div>
      </div>

      <!-- INFO TRANSAKSI -->
      <div class="transaction-info">
        <div class="info-row">
          <span>No. Transaksi:</span>
          <span
            >#<?php echo str_pad($transaction['id'], 6, '0', STR_PAD_LEFT); ?></span
          >
        </div>
        <div class="info-row">
          <span>Tanggal:</span>
          <span
            ><?php echo date('d/m/Y H:i:s', strtotime($transaction['created_at'])); ?></span
          >
        </div>
        <div class="info-row">
          <span>Pelanggan:</span>
          <span><?php echo $transaction['username']; ?></span>
        </div>
      </div>

      <!-- DAFTAR ITEM -->
      <table class="items-table">
        <thead>
          <tr>
            <th>Item</th>
            <th class="qty">Qty</th>
            <th class="price">Harga</th>
            <th class="subtotal">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td><?php echo $item['nama']; ?></td>
            <td class="qty"><?php echo $item['qty']; ?></td>
            <td class="price">
              Rp
              <?php echo number_format($item['harga'], 0, ',', '.'); ?>
            </td>
            <td class="subtotal">
              Rp
              <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- TOTAL -->
      <div class="total-section">
        <div class="total-row">
          <span>Total Item:</span>
          <span><?php echo count($items); ?></span>
        </div>
        <div class="total-row">
          <span>Total Harga:</span>
          <span
            >Rp
            <?php echo number_format($transaction['total'], 0, ',', '.'); ?></span
          >
        </div>
        <div class="total-row grand-total">
          <span>GRAND TOTAL:</span>
          <span
            >Rp
            <?php echo number_format($transaction['total'], 0, ',', '.'); ?></span
          >
        </div>
      </div>

      <!-- FOOTER STRUK -->
      <div class="footer-struk">
        <div>Terima kasih telah berbelanja di Kantin Sehat</div>
        <div>** Struk ini sebagai bukti pembayaran **</div>
        <div>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</div>
      </div>

      <!-- TOMBOL AKSI -->
      <div class="action-buttons">
        <button class="btn btn-print" onclick="window.print()">
          🖨️ Cetak Struk
        </button>
        <a href="menu.php" class="btn btn-menu">🏠 Kembali ke Menu</a>
      </div>
    </div>

    <script>
      // AUTO PRINT (optional)
      // window.onload = function() {
      //     setTimeout(() => {
      //         window.print();
      //     }, 1000);
      // };

      // KOSONGKAN KERANJANG SETELAH TRANSAKSI
      localStorage.removeItem("kantin_cart");
    </script>
  </body>
</html>