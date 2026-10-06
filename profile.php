<?php
// profile.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: LOGIN.html');
    exit;
}

$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone        = trim($_POST['phone'] ?? '');
    $location     = trim($_POST['location'] ?? '');
    $availability = ($_POST['availability'] === 'available') ? 'available' : 'unavailable';

    $stmt = mysqli_prepare($conn, "UPDATE users SET phone = ?, location = ?, availability = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $phone, $location, $availability, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $saved = true;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT fullname, username, email, phone, blood_group, location, availability FROM users WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$donor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$page_title = 'My Profile';
$active = 'profile';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>My Profile</h1>
  <p class="subtitle"><?= htmlspecialchars($donor['fullname']) ?> (<?= htmlspecialchars($donor['username']) ?>)</p>

  <?php if ($saved): ?>
    <p class="banner-msg success">Your details have been updated.</p>
  <?php endif; ?>

  <form method="POST" action="profile.php">
    <div style="display:flex;gap:20px;margin-bottom:18px;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;display:flex;flex-direction:column;">
        <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Email</label>
        <input type="email" value="<?= htmlspecialchars($donor['email']) ?>" disabled
               style="padding:12px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#f0f0f0;color:#888;">
      </div>
      <div style="flex:1;min-width:200px;display:flex;flex-direction:column;">
        <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Blood Type</label>
        <input type="text" value="<?= htmlspecialchars($donor['blood_group'] ?: 'N/A') ?>" disabled
               style="padding:12px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#f0f0f0;color:#888;">
      </div>
    </div>

    <div style="display:flex;gap:20px;margin-bottom:18px;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;display:flex;flex-direction:column;">
        <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Phone Number</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($donor['phone']) ?>" required
               style="padding:12px 14px;border:1px solid #ccc;border-radius:8px;font-size:14px;background:#fafafa;">
      </div>
      <div style="flex:1;min-width:200px;display:flex;flex-direction:column;">
        <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($donor['location']) ?>" required
               style="padding:12px 14px;border:1px solid #ccc;border-radius:8px;font-size:14px;background:#fafafa;">
      </div>
    </div>

    <div style="display:flex;flex-direction:column;margin-bottom:22px;">
      <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Availability</label>
      <select name="availability" style="padding:12px 14px;border:1px solid #ccc;border-radius:8px;font-size:14px;background:#fafafa;">
        <option value="available" <?= $donor['availability'] === 'available' ? 'selected' : '' ?>>Available to donate</option>
        <option value="unavailable" <?= $donor['availability'] === 'unavailable' ? 'selected' : '' ?>>Not available right now</option>
      </select>
    </div>

    <button type="submit" style="width:100%;padding:13px;background:#b71c1c;color:#fff;font-size:16px;font-weight:600;border:none;border-radius:8px;cursor:pointer;">
      Save Changes
    </button>
  </form>
</div>

<?php include 'includes/layout-end.php'; ?>
