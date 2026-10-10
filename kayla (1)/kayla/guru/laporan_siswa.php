<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'guru');

$siswa_id = (int)($_GET['siswa_id'] ?? 0);
if ($siswa_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Verify siswa in my kelas
$guru_kelas_id = $pdo->query("SELECT k.id FROM kelas k JOIN guru g ON k.guru_id = g.id WHERE g.user_id = " . $_SESSION['user_id'])->fetchColumn();
if (!$guru_kelas_id || !$pdo->query("SELECT 1 FROM siswa WHERE id = $siswa_id AND kelas_id = $guru_kelas_id")->fetch()) {
    die('Akses ditolak');
}

$siswa = $pdo->prepare("SELECT nama_siswa, nis FROM siswa WHERE id = ?");
$siswa->execute([$siswa_id]);
$siswa = $siswa->fetch();
$siswa_name = $siswa['nama_siswa'] ?? 'Unknown';
$siswa_nis = $siswa['nis'] ?? '-';

// Filter
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT * FROM laporan_kegiatan WHERE siswa_id = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal DESC");
$stmt->execute([$siswa_id, $date_from, $date_to]);
$laporan_list = $stmt->fetchAll();

// Calculate statistics
$total_days = count($laporan_list);
$sholat_count = ['subuh' => 0, 'dzuhur' => 0, 'ashar' => 0, 'maghrib' => 0, 'isya' => 0];
foreach ($laporan_list as $l) {
    if ($l['sholat_subuh']) $sholat_count['subuh']++;
    if ($l['sholat_dzuhur']) $sholat_count['dzuhur']++;
    if ($l['sholat_ashar']) $sholat_count['ashar']++;
    if ($l['sholat_maghrib']) $sholat_count['maghrib']++;
    if ($l['sholat_isya']) $sholat_count['isya']++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Siswa - <?php echo htmlspecialchars($siswa_name); ?></title>
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

        .btn-back {
            background: #f0f0f0;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header-section h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-info {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }

        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-size: 13px;
            color: #666;
            font-weight: 600;
        }

        .form-group input {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 3px;
        }

        .stat-label {
            color: #888;
            font-size: 12px;
        }

        .table-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .table-section h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .laporan-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
        }

        .laporan-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .laporan-table th {
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            border: none;
            font-size: 13px;
        }

        .laporan-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        .laporan-table tbody tr {
            transition: background-color 0.2s;
        }

        .laporan-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .sholat-check {
            text-align: center;
            font-weight: 700;
            font-size: 16px;
        }

        .sholat-check.yes {
            color: #28a745;
        }

        .sholat-check.no {
            color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: stretch;
            }

            .navbar-left {
                flex-direction: column;
            }

            .filter-form {
                flex-direction: column;
            }

            .form-group {
                width: 100%;
            }

            .filter-form .btn-filter {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .laporan-table {
                font-size: 12px;
            }

            .laporan-table th,
            .laporan-table td {
                padding: 8px 5px;
            }

            .student-info {
                flex-direction: column;
                gap: 10px;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h2>Laporan Siswa</h2>
        </div>
        <a href="dashboard.php" class="btn-back">Kembali ke Dashboard</a>
    </div>

    <div class="container">
        <div class="header-section">
            <h1><?php echo htmlspecialchars($siswa_name); ?></h1>
            <div class="student-info">
                <div class="info-item">
                    <span>NIS:</span>
                    <strong><?php echo htmlspecialchars($siswa_nis); ?></strong>
                </div>
                <div class="info-item">
                    <span>Periode:</span>
                    <strong><?php echo date('d/m/Y', strtotime($date_from)); ?> - <?php echo date('d/m/Y', strtotime($date_to)); ?></strong>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <form method="GET" class="filter-form">
                <input type="hidden" name="siswa_id" value="<?php echo $siswa_id; ?>">
                <div class="form-group">
                    <label for="date_from">Dari Tanggal:</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="form-group">
                    <label for="date_to">Sampai Tanggal:</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>

        <?php if ($total_days > 0): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-number"><?php echo $sholat_count['subuh']; ?>/<?php echo $total_days; ?></div>
                <div class="stat-label">Subuh</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-number"><?php echo $sholat_count['dzuhur']; ?>/<?php echo $total_days; ?></div>
                <div class="stat-label">Dzuhur</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-number"><?php echo $sholat_count['ashar']; ?>/<?php echo $total_days; ?></div>
                <div class="stat-label">Ashar</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-number"><?php echo $sholat_count['maghrib']; ?>/<?php echo $total_days; ?></div>
                <div class="stat-label">Maghrib</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-number"><?php echo $sholat_count['isya']; ?>/<?php echo $total_days; ?></div>
                <div class="stat-label">Isya</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-section">
            <h3>Detail Laporan Kegiatan</h3>
            <?php if ($laporan_list): ?>
                <div class="table-wrapper">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Subuh</th>
                                <th>Dzuhur</th>
                                <th>Ashar</th>
                                <th>Maghrib</th>
                                <th>Isya</th>
                                <th>Belajar</th>
                                <th>Tambahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_list as $l): ?>
                            <tr>
                                <td><strong><?php echo date('d/m/Y', strtotime($l['tanggal'])); ?></strong></td>
                                <td class="sholat-check <?php echo $l['sholat_subuh'] ? 'yes' : 'no'; ?>"><?php echo $l['sholat_subuh'] ? '✓' : '✗'; ?></td>
                                <td class="sholat-check <?php echo $l['sholat_dzuhur'] ? 'yes' : 'no'; ?>"><?php echo $l['sholat_dzuhur'] ? '✓' : '✗'; ?></td>
                                <td class="sholat-check <?php echo $l['sholat_ashar'] ? 'yes' : 'no'; ?>"><?php echo $l['sholat_ashar'] ? '✓' : '✗'; ?></td>
                                <td class="sholat-check <?php echo $l['sholat_maghrib'] ? 'yes' : 'no'; ?>"><?php echo $l['sholat_maghrib'] ? '✓' : '✗'; ?></td>
                                <td class="sholat-check <?php echo $l['sholat_isya'] ? 'yes' : 'no'; ?>"><?php echo $l['sholat_isya'] ? '✓' : '✗'; ?></td>
                                <td><?php echo htmlspecialchars($l['kegiatan_belajar']); ?></td>
                                <td><?php echo htmlspecialchars($l['kegiatan_tambahan']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Tidak ada laporan untuk periode yang dipilih</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
