<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'ortu');
$user = getCurrentUser($pdo);

// Get child
$stmt = $pdo->prepare("SELECT s.* FROM siswa s WHERE s.ortu_id = (SELECT id FROM orang_tua WHERE user_id = ?)");
$stmt->execute([$_SESSION['user_id']]);
$siswa = $stmt->fetch();

// Check today
$today = date('Y-m-d');
$today_laporan = null;
if ($siswa) {
    $stmt = $pdo->prepare("SELECT * FROM laporan_kegiatan WHERE siswa_id = ? AND tanggal = ?");
    $stmt->execute([$siswa['id'], $today]);
    $today_laporan = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Orang Tua - <?php echo htmlspecialchars($user['nama_ortu']); ?></title>
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

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar-left h2 {
            color: #667eea;
            font-size: 20px;
            margin: 0;
        }

        .user-info {
            color: #555;
            font-size: 14px;
            background: #f0f0f0;
            padding: 8px 15px;
            border-radius: 20px;
        }

        .btn-logout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header-section {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header-section h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .student-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .student-info-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }

        .student-details h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 5px;
        }

        .student-details p {
            color: #888;
            font-size: 14px;
        }

        .status-box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .status-box.completed {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }

        .status-box.pending {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
        }

        .status-box h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .status-box p {
            margin: 0;
            font-size: 14px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .no-student {
            background: white;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: #888;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: stretch;
            }

            .navbar-left {
                flex-direction: column;
            }

            .header-section h1 {
                font-size: 24px;
            }

            .student-info-header {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h2>Dashboard Orang Tua</h2>
            <div class="user-info">Halo, <?php echo htmlspecialchars($user['nama_ortu']); ?></div>
        </div>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="container">
        <div class="header-section">
            <h1>Pantau Perkembangan Anak</h1>
        </div>

        <?php if ($siswa): ?>
            <div class="student-card">
                <div class="student-info-header">
                    <div class="student-avatar">S</div>
                    <div class="student-details">
                        <h2><?php echo htmlspecialchars($siswa['nama_siswa']); ?></h2>
                        <p>NIS: <?php echo htmlspecialchars($siswa['nis']); ?></p>
                    </div>
                </div>

                <?php if ($today_laporan): ?>
                    <div class="status-box completed">
                        <h3>Sudah Input Laporan Hari Ini</h3>
                        <p>Laporan harian untuk <?php echo date('d/m/Y'); ?> telah diinput</p>
                    </div>
                <?php else: ?>
                    <div class="status-box pending">
                        <h3>Belum Input Laporan Hari Ini</h3>
                        <p>Mohon segera input laporan harian untuk <?php echo date('d/m/Y'); ?></p>
                    </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="input_laporan.php" class="btn btn-primary">Input Laporan Baru</a>
                    <a href="riwayat.php" class="btn btn-secondary">Lihat Riwayat</a>
                </div>
            </div>
        <?php else: ?>
            <div class="no-student">
                <h3>Siswa Tidak Ditemukan</h3>
                <p>Belum ada siswa terdaftar. Silakan hubungi administrator.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

