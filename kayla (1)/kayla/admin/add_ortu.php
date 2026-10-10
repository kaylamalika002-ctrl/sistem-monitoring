<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'admin');

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama_ortu = trim($_POST['nama_ortu']);

    if (empty($username) || empty($password) || empty($nama_ortu)) {
        $error = 'Semua field wajib';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah ada';
        } else {
            $pdo->beginTransaction();
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'ortu')");
                $stmt->execute([$username, $hash]);
                $user_id = $pdo->lastInsertId();
                
                $stmt = $pdo->prepare("INSERT INTO orang_tua (user_id, nama_ortu) VALUES (?, ?)");
                $stmt->execute([$user_id, $nama_ortu]);
                
                $pdo->commit();
                header('Location: list_ortu.php?msg=added');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Orang Tua</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fb; color: #333; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .header h1 { font-size: 24px; font-weight: 600; }
        .header-nav { display: flex; gap: 12px; }
        .btn-header { background: rgba(255,255,255,0.2); color: white; border: 2px solid rgba(255,255,255,0.3); padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .btn-header:hover { background: rgba(255,255,255,0.3); border-color: rgba(255,255,255,0.5); }
        .breadcrumb { background: white; padding: 16px 40px; display: flex; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .breadcrumb a { color: #667eea; text-decoration: none; font-weight: 500; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .page-title { font-size: 24px; font-weight: 600; margin-bottom: 32px; }
        .form-card { background: white; border-radius: 12px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px 14px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; font-family: inherit; transition: all 0.3s; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .form-group input::placeholder { color: #999; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 6px; border-left: 4px solid #dc3545; margin-bottom: 24px; }
        .btn-group { display: flex; gap: 12px; }
        .btn-submit { flex: 1; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4); }
        .btn-cancel { flex: 1; padding: 12px; background: #f0f0f0; color: #666; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; transition: all 0.3s; }
        .btn-cancel:hover { background: #e0e0e0; border-color: #d0d0d0; }
        @media (max-width: 768px) { .header { flex-direction: column; padding: 20px; } .header h1 { font-size: 20px; margin-bottom: 16px; } .form-card { padding: 24px; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tambah Orang Tua</h1>
        <div class="header-nav">
            <a href="list_ortu.php" class="btn-header">Daftar Orang Tua</a>
            <a href="dashboard.php" class="btn-header">Dashboard</a>
        </div>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> / <a href="list_ortu.php">Daftar Orang Tua</a> / <span>Tambah Orang Tua</span>
    </div>

    <div class="container">
        <h2 class="page-title">Tambah Data Orang Tua Baru</h2>

        <div class="form-card">
            <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <div class="form-group">
                    <label for="nama_ortu">Nama Orang Tua</label>
                    <input type="text" id="nama_ortu" name="nama_ortu" placeholder="Masukkan nama orang tua" value="<?php echo isset($_POST['nama_ortu']) ? htmlspecialchars($_POST['nama_ortu']) : ''; ?>" required>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-submit">Simpan Orang Tua</button>
                    <a href="list_ortu.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
