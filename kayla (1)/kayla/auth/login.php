<?php
session_start();
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username dan password wajib diisi';
        header('Location: ../index.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $username;
        // Redirect based on role
        $dashboard = $user['role'] == 'admin' ? 'admin/dashboard.php' : 
                     ($user['role'] == 'guru' ? 'guru/dashboard.php' : 'ortu/dashboard.php');
        header('Location: ../' . $dashboard);
        exit;
    } else {
        $_SESSION['error'] = 'Username atau password salah';
    }
}
header('Location: ../index.php');
exit;
?>

