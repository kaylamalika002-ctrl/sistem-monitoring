<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: list_kelas.php');
    exit;
}

$guru_list = $pdo->query("SELECT id, nama_guru FROM guru")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ?");
$stmt->execute([$id]);
$kelas = $stmt->fetch();
if (!$kelas) {
    header('Location: list_kelas.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kelas = trim($_POST['nama_kelas']);
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    if (empty($nama_kelas)) {
        $error = 'Nama kelas wajib';
    } else {
        $stmt = $pdo->prepare("UPDATE kelas SET nama_kelas = ?, guru_id = ? WHERE id = ?");
        $stmt->execute([$nama_kelas, $guru_id ?: null, $id]);
        header('Location: list_kelas.php?msg=updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas</title>
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
        .form-group input, .form-group select { width: 100%; padding: 12px 14px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; font-family: inherit; transition: all 0.3s; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .form-group .hint { font-size: 12px; color: #999; margin-top: 4px; }
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
        <h1>Edit Kelas</h1>
        <div class="header-nav">
            <a href="list_kelas.php" class="btn-header">Daftar Kelas</a>
            <a href="dashboard.php" class="btn-header">Dashboard</a>
        </div>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> / <a href="list_kelas.php">Daftar Kelas</a> / <span>Edit</span>
    </div>

    <div class="container">
        <h2 class="page-title">Edit Data Kelas</h2>

        <div class="form-card">
            <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nama_kelas">Nama Kelas</label>
                    <input type="text" id="nama_kelas" name="nama_kelas" placeholder="Contoh: 1A, 2B, 3C" value="<?php echo htmlspecialchars($kelas['nama_kelas']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="guru_id">Guru Pengajar (Opsional)</label>
                    <select id="guru_id" name="guru_id">
                        <option value="">-- Tanpa Guru --</option>
                        <?php foreach ($guru_list as $g): ?>
                        <option value="<?php echo $g['id']; ?>" <?php echo ($kelas['guru_id'] == $g['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['nama_guru']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint">Pilih guru yang mengajar kelas ini</div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-submit">Simpan Perubahan</button>
                    <a href="list_kelas.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
