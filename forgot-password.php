<?php
// forgot-password.php
// Processes the form submitted by forgot-password.html.

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$resetLink = null;
$error = null;

if (!$email) {
    $error = 'Please enter your email address.';
} else {
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $insert = mysqli_prepare($conn, "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insert, "sss", $email, $token, $expires);
        mysqli_stmt_execute($insert);
        mysqli_stmt_close($insert);

        // NOTE: this only shows the link for now. To actually email it, hook this
        // up to an SMTP library like PHPMailer with your email provider's credentials.
        $resetLink = 'reset-password.php?token=' . urlencode($token);
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | Blood Donation Registry</title>
<link rel="stylesheet" href="forgot-password.css">
</head>
<body>

  <div class="forgot-container">
    <div class="forgot-card">

      <div class="icon-circle">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#b71c1c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
      </div>

      <?php if ($error): ?>
        <h1>Forgot Password?</h1>
        <p class="subtitle" style="color:#b71c1c;"><?= htmlspecialchars($error) ?></p>
        <p class="back-link"><a href="forgot-password.html">&larr; Try again</a></p>

      <?php else: ?>
        <h1>Check Your Request</h1>
        <p class="subtitle">
          If <strong><?= htmlspecialchars($email) ?></strong> is registered, a reset link has been sent.
        </p>

        <?php if ($resetLink): ?>
          <p class="subtitle" style="font-size:12px;color:#999;">
            (Demo mode - no email service connected yet, so here's the link directly:)<br>
            <a href="<?= htmlspecialchars($resetLink) ?>"><?= htmlspecialchars($resetLink) ?></a>
          </p>
        <?php endif; ?>

        <p class="back-link"><a href="LOGIN.html">&larr; Back to Login</a></p>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
