<?php
session_start();

// HAPUS SEMUA DATA SESSION
$_SESSION = array();

// HAPUS COOKIE SESSION JIKA ADA
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// HANCURKAN SESSION
session_destroy();

// KOSONGKAN KERANJANG DI LOCALSTORAGE (via JavaScript redirect)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Logout - Kantin Sehat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(135deg, rgba(115, 175, 111, 0.05) 0%, rgba(231, 222, 175, 0.05) 100%);
        }
        
        .logout-container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(115, 175, 111, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
            border-left: 5px solid #73AF6F;
            border-right: 5px solid #E7DEAF;
            position: relative;
            overflow: hidden;
        }
        
        .logout-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(115, 175, 111, 0.05), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .logout-icon {
            font-size: 80px;
            margin-bottom: 25px;
            color: #73AF6F;
            position: relative;
            z-index: 1;
            background: linear-gradient(45deg, #E7DEAF, #f5f1e0);
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 8px 20px rgba(115, 175, 111, 0.2);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 32px;
            font-weight: 800;
            position: relative;
            z-index: 1;
        }
        
        p {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }
        
        .loading-container {
            margin: 30px 0;
            position: relative;
            z-index: 1;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #E7DEAF;
            border-top: 4px solid #73AF6F;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: #73AF6F;
            font-weight: 600;
            font-size: 16px;
        }
        
        .btn-login {
            display: inline-block;
            background: linear-gradient(45deg, #73AF6F, #5a9c56);
            color: white;
            padding: 16px 40px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            margin-top: 20px;
            transition: all 0.3s;
            position: relative;
            z-index: 1;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(115, 175, 111, 0.3);
        }
        
        .btn-login:hover {
            background: linear-gradient(45deg, #5a9c56, #488345);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(115, 175, 111, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .additional-info {
            margin-top: 30px;
            padding: 20px;
            background-color: #E7DEAF;
            border-radius: 12px;
            text-align: left;
            position: relative;
            z-index: 1;
        }
        
        .additional-info h3 {
            color: #73AF6F;
            margin-bottom: 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .additional-info h3::before {
            content: "ℹ️";
        }
        
        .additional-info ul {
            list-style: none;
            padding-left: 0;
        }
        
        .additional-info li {
            color: #666;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .additional-info li::before {
            content: "✓";
            color: #73AF6F;
            font-weight: bold;
        }
        
        .countdown {
            font-weight: bold;
            color: #73AF6F;
            font-size: 18px;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .logout-container {
                padding: 40px 25px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            p {
                font-size: 16px;
            }
            
            .logout-icon {
                width: 100px;
                height: 100px;
                font-size: 60px;
            }
            
            .btn-login {
                padding: 14px 30px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .logout-container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .logout-icon {
                width: 80px;
                height: 80px;
                font-size: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">👋</div>
        <h1>Logout Berhasil</h1>
        <p>Anda telah berhasil keluar dari sistem Kantin Sehat. Terima kasih telah menggunakan layanan kami.</p>
        
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <p class="loading-text">Mengarahkan ke halaman login dalam <span class="countdown" id="countdown">3</span> detik...</p>
        </div>
    </div>
    
    <script>
        // KOSONGKAN KERANJANG DI LOCALSTORAGE
        localStorage.removeItem('kantin_cart');
        
        // COUNTDOWN TIMER
        let seconds = 3;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
        
        // REDIRECT OTOMATIS SETELAH 3 DETIK
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html>