<?php
// contact.php
// Processes the "Contact Us" form on homepage.html.

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: homepage.html');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$subject || !$message) {
    header('Location: homepage.html?sent=0#contact');
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: homepage.html?sent=1#contact');
exit;
