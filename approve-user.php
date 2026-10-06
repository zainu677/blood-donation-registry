<?php
// approve-user.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "UPDATE users SET status = 'approved' WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: admin-dashboard.php');
exit;
