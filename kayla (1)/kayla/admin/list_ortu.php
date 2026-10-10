<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'admin');

// Handle delete
if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM orang_tua WHERE id = ?");
    $stmt->execute([$delete_id]);
    header('Location: list_ortu.php?msg=deleted');
    exit;
}

$stmt = $pdo->query("SELECT o.id, o.nama_ortu, u.username FROM orang_tua o JOIN users u ON o.user_id = u.id");
$ortu_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Orang Tua</title>
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
        .btn-add { background: #667eea; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .btn-add:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); }
        .success-msg { background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 6px; border-left: 4px solid #28a745; margin-bottom: 24px; }
        .table-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f5f7fb; border-bottom: 2px solid #e0e0e0; }
        th { padding: 16px; text-align: left; font-weight: 600; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
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
        @media (max-width: 768px) { .header { flex-direction: column; padding: 20px; } .header h1 { font-size: 20px; margin-bottom: 16px; } .action-buttons { flex-direction: column; } .btn-edit, .btn-delete { width: 100%; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Orang Tua</h1>
        <div class="header-nav">
            <a href="dashboard.php" class="btn-header">Dashboard</a>
            <a href="../logout.php" class="btn-header">Logout</a>
        </div>
    </div>

    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> / <span>Daftar Orang Tua</span>
    </div>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 class="page-title">Kelola Orang Tua</h2>
            <a href="add_ortu.php" class="btn-add">Tambah Orang Tua Baru</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="success-msg">Orang Tua berhasil <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if (count($ortu_list) > 0): ?>
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 40%;">Nama Orang Tua</th>
                        <th style="width: 30%;">Username</th>
                        <th style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ortu_list as $o): ?>
                    <tr>
                        <td><?php echo $o['id']; ?></td>
                        <td><?php echo htmlspecialchars($o['nama_ortu']); ?></td>
                        <td><?php echo htmlspecialchars($o['username']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_ortu.php?id=<?php echo $o['id']; ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="flex: 1; display: flex;">
                                    <input type="hidden" name="delete_id" value="<?php echo $o['id']; ?>">
                                    <button type="submit" class="btn-delete" style="width: 100%;" onclick="return confirm('Apakah Anda yakin ingin menghapus orang tua ini?')">Hapus</button>
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
                <p>Belum ada data orang tua</p>
                <a href="add_ortu.php" class="btn-add">Tambah Orang Tua Pertama</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
