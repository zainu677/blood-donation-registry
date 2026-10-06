<?php
// login.php
// Processes the form submitted by LOGIN.html.

session_start();
require_once 'includes/db.php';

function loginFail($message)
{
    header('Location: LOGIN.html?error=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: LOGIN.html');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    loginFail('Please enter both username and password.');
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, fullname, password_hash, role, status FROM users WHERE username = ?"
);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user['password_hash'])) {
    loginFail('Incorrect username or password.');
}

// Donors need admin approval before they can log in and use personal features
if ($user['role'] === 'donor' && $user['status'] !== 'approved') {
    loginFail('Your account is still awaiting admin approval. Please check back later.');
}

// Requirement: donors and hospital staff must log in before using personal features
$_SESSION['user_id']  = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['role']     = $user['role'];
$_SESSION['status']   = $user['status']; // used to gate phone-number visibility for hospitals

switch ($user['role']) {
    case 'donor':
        header('Location: donor.php');
        break;
    case 'hospital':
        header('Location: hospital-dashboard.php');
        break;
    case 'recipient':
        header('Location: recipient-dashboard.php');
        break;
    case 'bank':
        header('Location: bank-dashboard.php');
        break;
    case 'admin':
        header('Location: admin-dashboard.php');
        break;
    default:
        header('Location: homepage.html');
}
exit;
