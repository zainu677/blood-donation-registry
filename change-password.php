<?php
// change-password.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: LOGIN.html');
    exit;
}

$saved = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user || !password_verify($current, $user['password_hash'])) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $update = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $new_hash, $_SESSION['user_id']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        $saved = true;
    }
}

$page_title = 'Change Password';
$active = 'change-password';
include 'includes/layout-start.php';
?>

<div class="section-card" style="max-width:480px;">
  <h1>Change Password</h1>
  <p class="subtitle">
    If admin gave you a temporary password, this is where you replace it with one only you know.
  </p>

  <?php if ($saved): ?>
    <p class="banner-msg success">Password updated. Use your new password next time you log in.</p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="banner-msg warning"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="change-password.php">
    <div class="form-field">
      <label for="current_password">Current Password</label>
      <input type="password" id="current_password" name="current_password" required>
    </div>

    <div class="form-field">
      <label for="new_password">New Password</label>
      <input type="password" id="new_password" name="new_password" minlength="6" required>
    </div>

    <div class="form-field">
      <label for="confirm_password">Confirm New Password</label>
      <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
    </div>

    <button type="submit" class="btn-primary">Update Password</button>
  </form>
</div>

<?php include 'includes/layout-end.php'; ?>
