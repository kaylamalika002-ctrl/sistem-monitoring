<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: list_siswa.php');
    exit;
}

$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas")->fetchAll();
$ortu_list = $pdo->query("SELECT id, nama_ortu FROM orang_tua")->fetchAll();

$stmt = $pdo->prepare("SELECT s.*, k.nama_kelas, o.nama_ortu FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id LEFT JOIN orang_tua o ON s.ortu_id = o.id WHERE s.id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch();
if (!$siswa) {
    header('Location: list_siswa.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = trim($_POST['nis']);
    $nama_siswa = trim($_POST['nama_siswa']);
    $kelas_id = (int)$_POST['kelas_id'];
    $ortu_id = (int)($_POST['ortu_id'] ?? 0);
    if (empty($nis) || empty($nama_siswa) || $kelas_id <= 0) {
        $error = 'NIS, nama, kelas wajib';
    } else {
        $stmt = $pdo->prepare("UPDATE siswa SET nis = ?, nama_siswa = ?, kelas_id = ?, ortu_id = ? WHERE id = ?");
        $stmt->execute([$nis, $nama_siswa, $kelas_id, $ortu_id ?: null, $id]);
        header('Location: list_siswa.php?msg=updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
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
        <h1>Edit Siswa</h1>
        <div class="header-nav">
            <a href="list_siswa.php" class="btn-header">Daftar Siswa</a>
            <a href="dashboard.php" class="btn-header">Dashboard</a>
        </div>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> / <a href="list_siswa.php">Daftar Siswa</a> / <span>Edit</span>
    </div>

    <div class="container">
        <h2 class="page-title">Edit Data Siswa</h2>

        <div class="form-card">
            <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nis">NIS (Nomor Induk Siswa)</label>
                    <input type="text" id="nis" name="nis" placeholder="Contoh: 001" value="<?php echo htmlspecialchars($siswa['nis']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="nama_siswa">Nama Siswa</label>
                    <input type="text" id="nama_siswa" name="nama_siswa" placeholder="Masukkan nama siswa" value="<?php echo htmlspecialchars($siswa['nama_siswa']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="kelas_id">Kelas</label>
                    <select id="kelas_id" name="kelas_id" required>
                        <?php foreach ($kelas_list as $k): ?>
                        <option value="<?php echo $k['id']; ?>" <?php echo ($siswa['kelas_id'] == $k['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint">Kelas tempat siswa berada</div>
                </div>

                <div class="form-group">
                    <label for="ortu_id">Orang Tua (Opsional)</label>
                    <select id="ortu_id" name="ortu_id">
                        <option value="">-- Tanpa Orang Tua --</option>
                        <?php foreach ($ortu_list as $o): ?>
                        <option value="<?php echo $o['id']; ?>" <?php echo ($siswa['ortu_id'] == $o['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($o['nama_ortu']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-submit">Simpan Perubahan</button>
                    <a href="list_siswa.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
