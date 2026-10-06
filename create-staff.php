<?php
// create-staff.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

$saved = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';
    $location = trim($_POST['location'] ?? '');

    if (!in_array($role, ['hospital', 'bank'], true)) {
        $error = 'Please choose Hospital Staff or Blood Bank Staff.';
    } elseif (!$fullname || !$username || !$email || !$phone || !$password || !$location) {
        $error = 'Please fill in all fields.';
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'That username or email is already registered.';
        }
        mysqli_stmt_close($check);

        if (!$error) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Admin is creating and vetting this account directly, so it's approved immediately
            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO users (fullname, username, email, phone, password_hash, role, location, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')"
            );
            mysqli_stmt_bind_param($insert, "sssssss", $fullname, $username, $email, $phone, $password_hash, $role, $location);
            mysqli_stmt_execute($insert);
            mysqli_stmt_close($insert);
            $saved = true;
        }
    }
}

$page_title = 'Create Staff Account';
$active = 'create-staff';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Create Staff Account</h1>
  <p class="subtitle">Hospital and blood bank accounts are created here, not through public registration</p>

  <?php if ($saved): ?>
    <p class="banner-msg success">Account created and approved. They can log in right away — tell them to change this temporary password from their sidebar's "Change Password" link once they do.</p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="banner-msg warning"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="create-staff.php">
    <div class="form-grid">
      <div class="form-field">
        <label for="role">Account Type</label>
        <select id="role" name="role" required>
          <option value="" disabled selected>Select type</option>
          <option value="hospital">Hospital Staff</option>
          <option value="bank">Blood Bank Staff</option>
        </select>
      </div>
      <div class="form-field">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" required>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-field">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required>
      </div>
      <div class="form-field">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" placeholder="City / District" required>
      </div>
    </div>

    <div class="form-field">
      <label for="password">Temporary Password</label>
      <input type="text" id="password" name="password" placeholder="Give this to the staff member to change later" required>
    </div>

    <button type="submit" class="btn-primary">Create Account</button>
  </form>
</div>

<?php include 'includes/layout-end.php'; ?>
