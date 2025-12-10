<?php
include 'includes/config.php';

$error = '';
$success = '';

// PROSES REGISTER
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    if ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        // Cek username sudah ada
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password sederhana (untuk ujian pakai md5)
            $hashed_password = md5($password);
            $query = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Registrasi gagal: " . mysqli_error($conn);
            }
        }
    }
}

// PROSES LOGIN
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $hashed_password = md5($password);
    $query = "SELECT * FROM users WHERE username='$username' AND password='$hashed_password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        // ========== TAMBAHAN UNTUK FIX SESSION ==========
        // Hapus session lama sebelum buat yang baru
        $_SESSION = array();
        
        // Regenerate session ID untuk keamanan
        session_regenerate_id(true);
        // ========== SAMPAI SINI ==========
        
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: menu.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Kantin Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(135deg, rgba(115, 175, 111, 0.05) 0%, rgba(231, 222, 175, 0.05) 100%);
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #73AF6F;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .tab-container {
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .tab {
            display: flex;
            background-color: #E7DEAF;
        }

        .tab button {
            flex: 1;
            padding: 18px;
            border: none;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            background-color: transparent;
            color: #666;
            transition: all 0.3s;
            position: relative;
        }

        .tab button.active {
            color: #73AF6F;
        }

        .tab button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background-color: #73AF6F;
        }

        .tab button:hover:not(.active) {
            background-color: rgba(115, 175, 111, 0.1);
        }

        .form-container {
            padding: 50px;
            background-color: white;
            display: block;
        }

        .form-container h2 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 600;
            font-size: 15px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #73AF6F;
            font-size: 18px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: white;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #73AF6F;
            outline: none;
            box-shadow: 0 0 0 3px rgba(115, 175, 111, 0.2);
        }

        button[type="submit"] {
            background-color: #73AF6F;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 10px rgba(115, 175, 111, 0.3);
        }

        button[type="submit"]:hover {
            background-color: #5a9c56;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(115, 175, 111, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: center;
            animation: fadeIn 0.5s;
        }

        .error {
            color: #d32f2f;
            background-color: #ffebee;
            border-left: 5px solid #d32f2f;
        }

        .success {
            color: #388e3c;
            background-color: #e8f5e9;
            border-left: 5px solid #388e3c;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .features {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #73AF6F;
        }

        .features h3 {
            color: #73AF6F;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .features ul {
            list-style: none;
            padding-left: 0;
        }

        .features li {
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .features li i {
            margin-right: 10px;
            color: #73AF6F;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 28px;
            }
            
            .tab button {
                padding: 15px;
                font-size: 16px;
            }
        }
        /* Logo Styling */
.logo-container {
    margin-bottom: 20px;
    text-align: center;
}

.site-logo {
    max-width: 300px;
    height: auto;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
}

.site-logo:hover {
    transform: scale(1.05);
}

/* Untuk responsive */
@media (max-width: 768px) {
    .site-logo {
        max-width: 250px;
    }
}

@media (max-width: 480px) {
    .site-logo {
        max-width: 200px;
    }
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
    <div class="header">
    <!-- LOGO KANTIN SEHAT -->
    <div class="logo-container">
        <img src="assets/images/logo.png" alt="Kantin Sehat" class="site-logo">
        <!-- Tambahkan teks fallback untuk SEO -->
        <span class="sr-only">Kantin Sehat</span>
    </div>
</div>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="tab-container">
            <div class="tab">
                <button onclick="showTab('login')" id="loginTab" class="active">Login</button>
                <button onclick="showTab('register')" id="registerTab">Register</button>
            </div>
            
            <div id="loginForm" class="form-container">
                <h2>Login</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="loginUsername">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="loginUsername" name="username" placeholder="Masukkan username Anda" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="loginPassword" name="password" placeholder="Masukkan password Anda" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="login">Login</button>
                </form>
                
            </div>
            
            <div id="registerForm" class="form-container" style="display: none;">
                <h2>Register</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="regUsername">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user-plus"></i>
                            <input type="text" id="regUsername" name="username" placeholder="Buat username baru" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="regPassword">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" id="regPassword" name="password" placeholder="Buat password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" id="confirmPassword" name="confirm_password" placeholder="Konfirmasi password" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="register">Register</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Sembunyikan semua form
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'none';
            
            // Hapus kelas active dari semua tab
            document.getElementById('loginTab').classList.remove('active');
            document.getElementById('registerTab').classList.remove('active');
            
            // Tampilkan form yang dipilih dan aktifkan tabnya
            document.getElementById(tabName + 'Form').style.display = 'block';
            document.getElementById(tabName + 'Tab').classList.add('active');
        }
    </script>
</body>
</html>