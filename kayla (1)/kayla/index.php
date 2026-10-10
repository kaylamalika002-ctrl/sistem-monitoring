<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Monitoring Kegiatan Siswa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .logo-section h1 {
            color: white;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .logo-section p {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #c33;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .demo-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .demo-section h3 {
            color: #333;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .demo-item {
            padding: 12px;
            background: #f5f7fb;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13px;
            border-left: 3px solid #667eea;
        }

        .demo-item:last-child {
            margin-bottom: 0;
        }

        .demo-item strong {
            color: #333;
            display: block;
            margin-bottom: 4px;
        }

        .demo-item code {
            color: #764ba2;
            background: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }

        .demo-icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-size: 12px;
            margin-right: 8px;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }

            .logo-section h1 {
                font-size: 20px;
            }

            .logo-icon {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            <div class="logo-icon">📚</div>
            <h1>Kayla Monitor</h1>
            <p>Sistem Monitoring Kegiatan Siswa</p>
        </div>

        <div class="login-card">
            <?php 
            if (isset($_SESSION['error'])) { 
                echo '<div class="error-message">⚠️ ' . htmlspecialchars($_SESSION['error']) . '</div>'; 
                unset($_SESSION['error']); 
            } 
            ?>
            
            <form method="POST" action="auth/login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
                </div>

                <button type="submit" class="btn-login">Masuk ke Sistem</button>
            </form>
        </div>

        <div class="demo-section">
            <h3>🔑 Akun Demo Tersedia</h3>
            
            <div class="demo-item">
                <strong><span class="demo-icon">👨‍💼</span>Admin</strong>
                <code>admin</code> / <code>admin123</code>
            </div>

            <div class="demo-item">
                <strong><span class="demo-icon">👨‍🏫</span>Guru</strong>
                <code>guru1</code> / <code>guru123</code>
            </div>

            <div class="demo-item">
                <strong><span class="demo-icon">👨‍👩‍👧</span>Orang Tua</strong>
                <code>ortu1</code> / <code>ortu123</code>
            </div>
        </div>
    </div>
</body>
</html>
