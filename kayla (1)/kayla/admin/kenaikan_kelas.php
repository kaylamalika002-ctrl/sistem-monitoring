<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'admin');

$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas")->fetchAll();
$siswa_list = $pdo->query("SELECT s.id, nama_siswa, nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id")->fetchAll();

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_id = (int)$_POST['siswa_id'];
    $new_kelas_id = (int)$_POST['new_kelas_id'];
    if ($siswa_id <= 0 || $new_kelas_id <= 0) {
        $error = 'Pilih siswa dan kelas baru';
    } else {
        $stmt = $pdo->prepare("UPDATE siswa SET kelas_id = ? WHERE id = ?");
        $stmt->execute([$new_kelas_id, $siswa_id]);
        $success = 'Siswa berhasil naik kelas';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenaikan Kelas</title>
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
        .form-group select { width: 100%; padding: 12px 14px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; font-family: inherit; transition: all 0.3s; }
        .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .error-msg { background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 6px; border-left: 4px solid #dc3545; margin-bottom: 24px; }
        .success-msg { background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 6px; border-left: 4px solid #28a745; margin-bottom: 24px; }
        .btn-submit { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4); }
        .btn-submit:active { transform: translateY(0); }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state p { color: #999; margin-bottom: 16px; }
        @media (max-width: 768px) { .header { flex-direction: column; padding: 20px; } .header h1 { font-size: 20px; margin-bottom: 16px; } .form-card { padding: 24px; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kenaikan Kelas</h1>
        <div class="header-nav">
            <a href="dashboard.php" class="btn-header">Dashboard</a>
            <a href="../logout.php" class="btn-header">Logout</a>
        </div>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> / <span>Kenaikan Kelas</span>
    </div>

    <div class="container">
        <h2 class="page-title">Proses Kenaikan Kelas</h2>

        <div class="form-card">
            <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (count($siswa_list) > 0): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="siswa_id">Pilih Siswa</label>
                    <select name="siswa_id" id="siswa_id" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswa_list as $s): ?>
                        <option value="<?php echo $s['id']; ?>">
                            <?php echo htmlspecialchars($s['nama_siswa']) . ' (Kelas: ' . htmlspecialchars($s['nama_kelas']) . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="new_kelas_id">Kelas Tujuan</label>
                    <select name="new_kelas_id" id="new_kelas_id" required>
                        <option value="">-- Pilih Kelas Baru --</option>
                        <?php foreach ($kelas_list as $k): ?>
                        <option value="<?php echo $k['id']; ?>">
                            <?php echo htmlspecialchars($k['nama_kelas']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Naikkan Siswa</button>
            </form>
            <?php else: ?>
            <div class="empty-state">
                <p>Belum ada siswa yang dapat dinaikkan kelas</p>
                <p style="font-size: 12px; color: #ccc;">Pastikan ada siswa dan kelas yang tersedia</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
