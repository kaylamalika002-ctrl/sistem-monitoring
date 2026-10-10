<?php
require_once '../middleware/auth.php';
requireRole($pdo, 'ortu');

$ortu_id = $pdo->prepare("SELECT id FROM orang_tua WHERE user_id = ?");
$ortu_id->execute([$_SESSION['user_id']]);
$ortu_id = $ortu_id->fetchColumn();

$laporan_id = (int)($_GET['id'] ?? 0);
if ($laporan_id > 0 && $ortu_id) {
    $stmt = $pdo->prepare("DELETE FROM laporan_kegiatan WHERE id = ? AND siswa_id IN (SELECT id FROM siswa WHERE ortu_id = ?)");
    $stmt->execute([$laporan_id, $ortu_id]);
}
header('Location: riwayat.php?msg=deleted');
exit;
?>

