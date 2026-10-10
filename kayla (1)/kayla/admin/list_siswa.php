<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'admin');

// Handle delete
if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
    $stmt->execute([$delete_id]);
    header('Location: list_siswa.php?msg=deleted');
    exit;
}

// Search
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search) {
    $where = 'WHERE s.nis LIKE ? OR s.nama_siswa LIKE ? OR k.nama_kelas LIKE ? OR o.nama_ortu LIKE ?';
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT s.*, k.nama_kelas, o.nama_ortu FROM siswa s 
                       LEFT JOIN kelas k ON s.kelas_id = k.id 
                       LEFT JOIN orang_tua o ON s.ortu_id = o.id $where");
$stmt->execute($params);
$siswa_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa</title>
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
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .page-title { font-size: 24px; font-weight: 600; margin-bottom: 24px; }
        .action-bar { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; align-items: center; }
        .search-form { flex: 1; min-width: 200px; display: flex; gap: 8px; }
        .search-form input { flex: 1; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; }
        .search-form button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.3s; }
        .search-form button:hover { background: #5568d3; }
        .btn-add { background: #667eea; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .btn-add:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); }
        .success-msg { background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 6px; border-left: 4px solid #28a745; margin-bottom: 24px; }
        .table-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f5f7fb; border-bottom: 2px solid #e0e0e0; }
        th { padding: 16px; text-align: left; font-weight: 600; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
        td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; }
        tbody tr { transition: background 0.2s; }
        tbody tr:hover { background: #fafbfc; }
        .action-buttons { display: flex; gap: 8px; }
        .btn-edit { background: #28a745; color: white; padding: 8px 14px; border: none; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.3s; }
        .btn-edit:hover { background: #218838; }
        .btn-delete { background: #dc3545; color: white; padding: 8px 14px; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.3s; }
        .btn-delete:hover { background: #c82333; }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state p { color: #999; margin-bottom: 24px; }
        @media (max-width: 768px) { .header { flex-direction: column; padding: 20px; } .header h1 { font-size: 20px; margin-bottom: 16px; } .action-bar { flex-direction: column; } .search-form { width: 100%; } .action-buttons { flex-direction: column; } .btn-edit, .btn-delete { width: 100%; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Siswa</h1>
        <div class="header-nav">
            <a href="dashboard.php" class="btn-header">Dashboard</a>
            <a href="../logout.php" class="btn-header">Logout</a>
        </div>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> / <span>Daftar Siswa</span>
    </div>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 class="page-title">Kelola Siswa</h2>
            <a href="add_siswa.php" class="btn-add">Tambah Siswa Baru</a>
        </div>

        <div class="action-bar">
            <form method="GET" class="search-form">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari NIS, nama, kelas, atau orang tua...">
                <button type="submit">Cari</button>
            </form>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="success-msg">Siswa berhasil <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if (count($siswa_list) > 0): ?>
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 10%;">NIS</th>
                        <th style="width: 25%;">Nama Siswa</th>
                        <th style="width: 18%;">Kelas</th>
                        <th style="width: 20%;">Orang Tua</th>
                        <th style="width: 19%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswa_list as $s): ?>
                    <tr>
                        <td><?php echo $s['id']; ?></td>
                        <td><?php echo htmlspecialchars($s['nis']); ?></td>
                        <td><?php echo htmlspecialchars($s['nama_siswa']); ?></td>
                        <td><?php echo htmlspecialchars($s['nama_kelas']); ?></td>
                        <td><?php echo $s['nama_ortu'] ?? '-'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_siswa.php?id=<?php echo $s['id']; ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="flex: 1; display: flex;">
                                    <input type="hidden" name="delete_id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" class="btn-delete" style="width: 100%;" onclick="return confirm('Apakah Anda yakin? Data dan laporan akan terhapus!')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="table-card">
            <div class="empty-state">
                <p><?php echo $search ? 'Tidak ada hasil pencarian' : 'Belum ada data siswa'; ?></p>
                <a href="add_siswa.php" class="btn-add">Tambah Siswa Pertama</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
