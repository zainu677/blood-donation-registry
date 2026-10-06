<?php
// update-user.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

$id       = (int) ($_POST['id'] ?? 0);
$fullname = trim($_POST['fullname'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$blood_group = !empty($_POST['blood_group']) ? $_POST['blood_group'] : null;
$last_donation_date = !empty($_POST['last_donation_date']) ? $_POST['last_donation_date'] : null;

$stmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET fullname = ?, phone = ?, location = ?, blood_group = ?, last_donation_date = ?
     WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "sssssi", $fullname, $phone, $location, $blood_group, $last_donation_date, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: admin-dashboard.php');
exit;
