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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Laporan - <?php echo htmlspecialchars($siswa_name); ?></title>
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
        }

        .header-info {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 14px;
            flex-wrap: wrap;
        }

        .table-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .table-card h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .laporan-table {
            width: 100%;
            border-collapse: collapse;
        }

        .laporan-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .laporan-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: none;
            font-size: 13px;
        }

        .laporan-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        .laporan-table tbody tr {
            transition: background-color 0.2s;
        }

        .laporan-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .sholat-status {
            display: inline-block;
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 3px;
            font-family: monospace;
            font-weight: 600;
            color: #667eea;
        }

        .text-truncate {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #667eea;
            color: white;
        }

        .btn-edit:hover {
            background: #5568d3;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
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

            .header-info {
                flex-direction: column;
                gap: 10px;
            }

            .laporan-table {
                font-size: 12px;
            }

            .laporan-table th,
            .laporan-table td {
                padding: 10px;
            }

            .text-truncate {
                max-width: 100px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h2>Riwayat Laporan</h2>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="btn-link">Dashboard</a>
            <a href="input_laporan.php" class="btn-link">Input Baru</a>
        </div>
    </div>

    <div class="container">
        <div class="header-section">
            <h1><?php echo htmlspecialchars($siswa_name); ?></h1>
            <div class="header-info">
                <span>NIS: <strong><?php echo htmlspecialchars($siswa_nis); ?></strong></span>
            </div>
        </div>

        <div class="table-card">
            <h3>Daftar Laporan (30 Hari Terakhir)</h3>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM laporan_kegiatan WHERE siswa_id = ? ORDER BY tanggal DESC LIMIT 30");
            $stmt->execute([$siswa_id]);
            $laporan_list = $stmt->fetchAll();
            ?>

            <?php if ($laporan_list): ?>
                <div class="table-wrapper">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Sholat</th>
                                <th>Kegiatan Belajar</th>
                                <th>Kegiatan Lain</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_list as $l): ?>
                            <tr>
                                <td><strong><?php echo date('d/m/Y', strtotime($l['tanggal'])); ?></strong></td>
                                <td>
                                    <span class="sholat-status">
                                        <?php echo ($l['sholat_subuh']?'S':'-') . ($l['sholat_dzuhur']?'D':'-') . ($l['sholat_ashar']?'A':'-') . ($l['sholat_maghrib']?'M':'-') . ($l['sholat_isya']?'I':'-'); ?>
                                    </span>
                                </td>
                                <td class="text-truncate" title="<?php echo htmlspecialchars($l['kegiatan_belajar']); ?>"><?php echo htmlspecialchars($l['kegiatan_belajar']); ?></td>
                                <td class="text-truncate" title="<?php echo htmlspecialchars($l['kegiatan_tambahan']); ?>"><?php echo htmlspecialchars($l['kegiatan_tambahan']) ?: '-'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_laporan.php?id=<?php echo $l['id']; ?>" class="btn-action btn-edit">Edit</a>
                                        <a href="delete_laporan.php?id=<?php echo $l['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus laporan ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Belum Ada Laporan</h3>
                    <p>Mulai dengan <a href="input_laporan.php" style="color: #667eea; font-weight: bold;">input laporan baru</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
