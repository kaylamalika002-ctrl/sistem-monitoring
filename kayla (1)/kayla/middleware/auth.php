<?php
session_start();
require_once dirname(__DIR__) . '/config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    header('Location: ../index.php');
    exit;
}

function requireRole($pdo, $allowed_roles) {
    global $current_user_role;
    $current_user_role = $_SESSION['role'];
    if (!in_array($current_user_role, (array)$allowed_roles)) {
        $_SESSION['error'] = 'Akses ditolak';
        header('Location: ../index.php');
        exit;
    }
}

// Get current user info
function getCurrentUser($pdo) {
    $stmt = $pdo->prepare("SELECT u.*, g.nama_guru, o.nama_ortu FROM users u 
                           LEFT JOIN guru g ON u.id = g.user_id 
                           LEFT JOIN orang_tua o ON u.id = o.user_id 
                           WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>

