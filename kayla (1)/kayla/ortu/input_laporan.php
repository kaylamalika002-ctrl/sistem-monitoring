<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'ortu');

// Get child
$stmt = $pdo->prepare("SELECT s.id, s.nama_siswa, s.nis FROM siswa s JOIN orang_tua o ON s.ortu_id = o.id WHERE o.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$child = $stmt->fetch();
if (!$child) {
    die('Tidak ada siswa');
}

$siswa_id = $child['id'];
$siswa_name = $child['nama_siswa'];
$siswa_nis = $child['nis'];
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    $sholat = [
        'subuh' => isset($_POST['sholat_subuh']) ? (int)$_POST['sholat_subuh'] : 0,
        'dzuhur' => isset($_POST['sholat_dzuhur']) ? (int)$_POST['sholat_dzuhur'] : 0,
        'ashar' => isset($_POST['sholat_ashar']) ? (int)$_POST['sholat_ashar'] : 0,
        'maghrib' => isset($_POST['sholat_maghrib']) ? (int)$_POST['sholat_maghrib'] : 0,
        'isya' => isset($_POST['sholat_isya']) ? (int)$_POST['sholat_isya'] : 0
    ];
    $belajar = trim($_POST['kegiatan_belajar']);
    $tambahan = trim($_POST['kegiatan_tambahan']);

    try {
        $stmt = $pdo->prepare("INSERT INTO laporan_kegiatan (siswa_id, tanggal, sholat_subuh, sholat_dzuhur, sholat_ashar, sholat_maghrib, sholat_isya, kegiatan_belajar, kegiatan_tambahan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$siswa_id, $tanggal, $sholat['subuh'], $sholat['dzuhur'], $sholat['ashar'], $sholat['maghrib'], $sholat['isya'], $belajar, $tambahan]);
        $success = 'Laporan berhasil disimpan';
        $tanggal = date('Y-m-d');
    } catch (PDOException $e) {
        $error = 'Error: Tanggal sudah ada (silakan edit di riwayat)';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Laporan - <?php echo htmlspecialchars($siswa_name); ?></title>
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
            padding: 20px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .navbar-left h2 {
            color: #667eea;
            font-size: 20px;
            margin: 0;
        }

        .nav-links {
            display: flex;
            gap: 10px;
        }

        .btn-link {
            background: #f0f0f0;
            color: #667eea;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-link:hover {
            background: #667eea;
            color: white;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-card h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: #888;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .checkbox-item label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }

        .form-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-section:last-of-type {
            border-bottom: none;
        }

        .form-section h3 {
            color: #667eea;
            font-size: 16px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-cancel {
            background: #f0f0f0;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-cancel:hover {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: stretch;
            }

            .button-group {
                flex-direction: column;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h2>Input Laporan Harian</h2>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="btn-link">Dashboard</a>
            <a href="riwayat.php" class="btn-link">Riwayat</a>
        </div>
    </div>

    <div class="container">
        <div class="form-card">
            <h1>Input Laporan Harian</h1>
            <p class="form-subtitle">Siswa: <strong><?php echo htmlspecialchars($siswa_name); ?></strong> (<?php echo htmlspecialchars($siswa_nis); ?>)</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> Kembali ke <a href="dashboard.php" style="color: inherit; font-weight: bold;">dashboard</a></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="tanggal">Tanggal Laporan</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?php echo $tanggal; ?>" required>
                </div>

                <div class="form-section">
                    <h3>Sholat 5 Waktu</h3>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="sholat_subuh" name="sholat_subuh" value="1" <?php echo (isset($_POST['sholat_subuh']) ? 'checked' : ''); ?>>
                            <label for="sholat_subuh">Subuh</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="sholat_dzuhur" name="sholat_dzuhur" value="1" <?php echo (isset($_POST['sholat_dzuhur']) ? 'checked' : ''); ?>>
                            <label for="sholat_dzuhur">Dzuhur</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="sholat_ashar" name="sholat_ashar" value="1" <?php echo (isset($_POST['sholat_ashar']) ? 'checked' : ''); ?>>
                            <label for="sholat_ashar">Ashar</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="sholat_maghrib" name="sholat_maghrib" value="1" <?php echo (isset($_POST['sholat_maghrib']) ? 'checked' : ''); ?>>
                            <label for="sholat_maghrib">Maghrib</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="sholat_isya" name="sholat_isya" value="1" <?php echo (isset($_POST['sholat_isya']) ? 'checked' : ''); ?>>
                            <label for="sholat_isya">Isya</label>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Kegiatan</h3>
                    <div class="form-group">
                        <label for="kegiatan_belajar">Kegiatan Belajar</label>
                        <textarea id="kegiatan_belajar" name="kegiatan_belajar" placeholder="Tuliskan kegiatan belajar yang dilakukan"><?php echo isset($_POST['kegiatan_belajar']) ? htmlspecialchars($_POST['kegiatan_belajar']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="kegiatan_tambahan">Kegiatan Tambahan</label>
                        <textarea id="kegiatan_tambahan" name="kegiatan_tambahan" placeholder="Tuliskan kegiatan tambahan (opsional)"><?php echo isset($_POST['kegiatan_tambahan']) ? htmlspecialchars($_POST['kegiatan_tambahan']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-submit">Simpan Laporan</button>
                    <a href="dashboard.php" class="btn btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
