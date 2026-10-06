<?php
// create-admin.php
// Visit this file ONCE in your browser to create the first admin account.
// Then delete this file - leaving it on a live server would let anyone create an admin account.

require_once 'includes/db.php';

$username = 'admin';
$email    = 'admin@lifelink.local';
$password = 'admin123'; // change this after your first login!

$check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($check, "s", $username);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    mysqli_stmt_close($check);
    die("An admin account already exists. Delete this file (create-admin.php) for safety.");
}
mysqli_stmt_close($check);

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$insert = mysqli_prepare(
    $conn,
    "INSERT INTO users (fullname, username, email, phone, password_hash, role, location, status)
     VALUES ('System Admin', ?, ?, '0000000000', ?, 'admin', 'HQ', 'approved')"
);
mysqli_stmt_bind_param($insert, "sss", $username, $email, $password_hash);
mysqli_stmt_execute($insert);
mysqli_stmt_close($insert);

echo "Admin account created!<br>";
echo "Username: <strong>admin</strong><br>";
echo "Password: <strong>admin123</strong><br><br>";
echo "<strong style='color:red;'>Delete this file (create-admin.php) now for security.</strong>";
