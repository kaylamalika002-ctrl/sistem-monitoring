<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'guru');
$user = getCurrentUser($pdo);

// Get guru kelas
$stmt = $pdo->prepare("SELECT k.* FROM kelas k WHERE k.guru_id = (SELECT id FROM guru WHERE user_id = ?)");
$stmt->execute([$_SESSION['user_id']]);
$kelas = $stmt->fetch();

// Get total siswa
$total_siswa = 0;
if ($kelas) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM siswa WHERE kelas_id = ?");
    $stmt->execute([$kelas['id']]);
    $total_siswa = $stmt->fetch()['count'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - <?php echo htmlspecialchars($user['nama_guru']); ?></title>
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

        .navbar-right {
            display: flex;
            gap: 10px;
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
            max-width: 1200px;
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

        .class-info {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-block;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #888;
            font-size: 14px;
        }

        .students-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .students-section h3 {
            color: #333;
            font-size: 22px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .no-class {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #888;
        }

        .no-class p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
        }

        .students-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .students-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: none;
        }

        .students-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .students-table tbody tr {
            transition: background-color 0.2s;
        }

        .students-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .btn-laporan {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-laporan:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: stretch;
            }

            .navbar-left, .navbar-right {
                flex-direction: column;
            }

            .header-section h1 {
                font-size: 24px;
            }

            .students-table {
                font-size: 13px;
            }

            .students-table th, .students-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h2>Dashboard Guru</h2>
            <div class="user-info">Halo, <?php echo htmlspecialchars($user['nama_guru']); ?></div>
        </div>
        <div class="navbar-right">
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="header-section">
            <h1>Selamat Datang</h1>
            <?php if ($kelas): ?>
                <div class="class-info">Kelas: <strong><?php echo htmlspecialchars($kelas['nama_kelas']); ?></strong></div>
            <?php else: ?>
                <div class="class-info" style="background: rgba(255, 100, 100, 0.2); color: rgba(255, 100, 100, 1);">Belum ada kelas yang ditugaskan</div>
            <?php endif; ?>
        </div>

        <?php if ($kelas): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-number"><?php echo $total_siswa; ?></div>
                    <div class="stat-label">Total Siswa</div>
                </div>
            </div>

            <div class="students-section">
                <h3>Daftar Siswa Kelas <?php echo htmlspecialchars($kelas['nama_kelas']); ?></h3>
                <?php
                $stmt = $pdo->prepare("SELECT s.id, s.nis, s.nama_siswa, o.nama_ortu FROM siswa s LEFT JOIN orang_tua o ON s.ortu_id = o.id WHERE s.kelas_id = ? ORDER BY s.nama_siswa ASC");
                $stmt->execute([$kelas['id']]);
                $siswa_list = $stmt->fetchAll();
                
                if ($siswa_list):
                ?>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Orang Tua</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa_list as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['nis']); ?></td>
                            <td><strong><?php echo htmlspecialchars($s['nama_siswa']); ?></strong></td>
                            <td><?php echo $s['nama_ortu'] ?? '<em style="color: #999;">-</em>'; ?></td>
                            <td><a href="laporan_siswa.php?siswa_id=<?php echo $s['id']; ?>" class="btn-laporan">Lihat Laporan</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <p>Tidak ada siswa di kelas ini</p>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-class">
                <p>Belum ada kelas yang ditugaskan untuk Anda</p>
                <p>Silakan hubungi administrator untuk mendapatkan penugasan kelas</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
