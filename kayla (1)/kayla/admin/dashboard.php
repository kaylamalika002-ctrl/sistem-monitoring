<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'admin');
$user = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Monitoring Siswa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fb;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .user-badge {
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
        }

        .navigation {
            display: none;
        }

        .navigation a {
            display: none;
        }

        .navigation a:hover {
            display: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border-top: 4px solid #667eea;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.12);
        }

        .stat-card.guru {
            border-top-color: #667eea;
        }

        .stat-card.kelas {
            border-top-color: #764ba2;
        }

        .stat-card.siswa {
            border-top-color: #f093fb;
        }

        .stat-card.ortu {
            border-top-color: #4facfe;
        }

        .stat-card.laporan {
            border-top-color: #43e97b;
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .stat-label {
            font-size: 13px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
        }

        .stat-action {
            display: inline-block;
            background: #f0f2f5;
            color: #667eea;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .stat-action:hover {
            background: #667eea;
            color: white;
        }

        .table-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-top: 30px;
        }

        .table-section h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f5f7fb;
            border-bottom: 2px solid #e0e0e0;
        }

        table th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table tb, table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        table tbody tr {
            transition: background 0.2s ease;
        }

        table tbody tr:hover {
            background: #fafbfc;
        }

        table td:nth-child(2) {
            font-weight: 600;
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-lihat {
            background: #667eea;
            color: white;
        }

        .btn-lihat:hover {
            background: #5568d3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 20px;
            }

            .header h1 {
                font-size: 22px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 20px;
            }

            .navigation a {
                font-size: 12px;
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Admin</h1>
        <div class="header-right">
            <div class="user-info">
                <span>Selamat datang,</span>
                <div class="user-badge"><?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="section-title">
            Overview Sistem
        </div>

        <div class="stats-grid">
            <?php
            // Count data
            $guru_count = $pdo->query("SELECT COUNT(*) as count FROM guru")->fetch()['count'];
            $kelas_count = $pdo->query("SELECT COUNT(*) as count FROM kelas")->fetch()['count'];
            $siswa_count = $pdo->query("SELECT COUNT(*) as count FROM siswa")->fetch()['count'];
            $ortu_count = $pdo->query("SELECT COUNT(*) as count FROM orang_tua")->fetch()['count'];
            $laporan_count = $pdo->query("SELECT COUNT(*) as count FROM laporan_kegiatan")->fetch()['count'];
            ?>

            <div class="stat-card guru">
                <div class="stat-label">Total Guru</div>
                <div class="stat-value"><?php echo $guru_count; ?></div>
                <a href="list_guru.php" class="stat-action">Kelola Guru</a>
            </div>

            <div class="stat-card kelas">
                <div class="stat-label">Total Kelas</div>
                <div class="stat-value"><?php echo $kelas_count; ?></div>
                <a href="list_kelas.php" class="stat-action">Kelola Kelas</a>
            </div>

            <div class="stat-card siswa">
                <div class="stat-label">Total Siswa</div>
                <div class="stat-value"><?php echo $siswa_count; ?></div>
                <a href="list_siswa.php" class="stat-action">Kelola Siswa</a>
            </div>

            <div class="stat-card ortu">
                <div class="stat-label">Total Orang Tua</div>
                <div class="stat-value"><?php echo $ortu_count; ?></div>
                <a href="list_ortu.php" class="stat-action">Kelola Ortu</a>
            </div>

            <div class="stat-card laporan">
                <div class="stat-label">Total Laporan</div>
                <div class="stat-value"><?php echo $laporan_count; ?></div>
                <a href="../dashboard.php" class="stat-action">Lihat Laporan</a>
            </div>
        </div>

        <div class="table-section">
            <h3>Ringkasan Data</h3>
            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Guru</td>
                        <td><?php echo $guru_count; ?></td>
                        <td><a href="list_guru.php" class="btn btn-lihat">Lihat Detail</a></td>
                    </tr>
                    <tr>
                        <td>Kelas</td>
                        <td><?php echo $kelas_count; ?></td>
                        <td><a href="list_kelas.php" class="btn btn-lihat">Lihat Detail</a></td>
                    </tr>
                    <tr>
                        <td>Siswa</td>
                        <td><?php echo $siswa_count; ?></td>
                        <td><a href="list_siswa.php" class="btn btn-lihat">Lihat Detail</a></td>
                    </tr>
                    <tr>
                        <td>Orang Tua</td>
                        <td><?php echo $ortu_count; ?></td>
                        <td><a href="list_ortu.php" class="btn btn-lihat">Lihat Detail</a></td>
                    </tr>
                    <tr>
                        <td>Laporan Kegiatan</td>
                        <td><?php echo $laporan_count; ?></td>
                        <td><a href="../dashboard.php" class="btn btn-lihat">Lihat Detail</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

