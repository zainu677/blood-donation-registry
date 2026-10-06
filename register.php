<?php


require_once 'includes/db.php';

function fail($message)
{
    header('Location: register.html?error=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$age      = (int) ($_POST['age'] ?? 0);
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$role     = $_POST['role'] ?? '';
$blood_group = !empty($_POST['blood_group']) ? $_POST['blood_group'] : null;
$location = trim($_POST['location'] ?? '');

if (!in_array($role, ['donor', 'recipient'], true)) {
    fail('Only donor and recipient accounts can be created here.');
}

if (!$fullname || !$username || !$email || !$phone || !$age || !$password || !$location) {
    fail('Please fill in all required fields.');
}

if ($password !== $confirm) {
    fail('Passwords do not match.');
}

if ($role === 'donor' && ($age < 18 || $age > 65)) {
    fail('Donors must be between 18 and 65 years old.');
}

$check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
mysqli_stmt_bind_param($check, "ss", $username, $email);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    mysqli_stmt_close($check);
    fail('That username or email is already registered.');
}
mysqli_stmt_close($check);

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$insert = mysqli_prepare(
    $conn,
    "INSERT INTO users (fullname, username, email, phone, password_hash, role, age, blood_group, location, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
);
mysqli_stmt_bind_param(
    $insert,
    "sssssssss",
    $fullname, $username, $email, $phone, $password_hash, $role, $age, $blood_group, $location
);
mysqli_stmt_execute($insert);
mysqli_stmt_close($insert);
mysqli_close($conn);

header('Location: LOGIN.html?registered=1');
exit;
