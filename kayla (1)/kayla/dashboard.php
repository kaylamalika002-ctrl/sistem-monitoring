<?php
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}
$role = $_SESSION['role'];
$dashboard = $role == 'admin' ? 'admin/dashboard.php' : 
             ($role == 'guru' ? 'guru/dashboard.php' : 'ortu/dashboard.php');
header('Location: ' . $dashboard);
exit;
?>

