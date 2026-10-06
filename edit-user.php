<?php
// edit-user.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    die("Record not found. <a href='admin-dashboard.php'>Back to dashboard</a>");
}

$page_title = 'Edit Record';
$active = 'dashboard';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Edit Record</h1>
  <p class="subtitle"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars(ucfirst($user['role'])) ?>)</p>

  <form action="update-user.php" method="POST">
    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">

    <div class="form-grid">
      <div class="form-field">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
      </div>
      <div class="form-field">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-field">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?= htmlspecialchars($user['location']) ?>" required>
      </div>

      <?php if ($user['role'] === 'donor'): ?>
        <div class="form-field">
          <label for="blood_group">Blood Group</label>
          <select id="blood_group" name="blood_group">
            <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
              <option value="<?= $bg ?>" <?= ($user['blood_group'] === $bg) ? 'selected' : '' ?>><?= $bg ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($user['role'] === 'donor'): ?>
      <div class="form-field">
        <label for="last_donation_date">Last Donation Date</label>
        <input type="date" id="last_donation_date" name="last_donation_date" value="<?= htmlspecialchars($user['last_donation_date'] ?? '') ?>">
      </div>
    <?php endif; ?>

    <button type="submit" class="btn-primary">Save Changes</button>
    <p style="text-align:center;margin-top:14px;"><a href="admin-dashboard.php" style="color:#b71c1c;font-weight:600;font-size:13px;text-decoration:none;">&larr; Cancel and go back</a></p>
  </form>
</div>

<?php include 'includes/layout-end.php'; ?>
