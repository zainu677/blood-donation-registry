<?php
// notifications.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: LOGIN.html');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT status, blood_group, availability FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$donor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$stmt2 = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM blood_requests WHERE blood_group = ? AND status = 'open'");
mysqli_stmt_bind_param($stmt2, "s", $donor['blood_group']);
mysqli_stmt_execute($stmt2);
$matchCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2))['total'];
mysqli_stmt_close($stmt2);

$notifications = [];

if ($donor['status'] === 'pending') {
    $notifications[] = ['type' => 'warning', 'text' => 'Your account is still awaiting admin approval.'];
} else {
    $notifications[] = ['type' => 'success', 'text' => 'Your account is approved and visible to hospitals.'];
}

if ($matchCount > 0) {
    $notifications[] = [
        'type' => 'info',
        'text' => "There " . ($matchCount == 1 ? "is" : "are") . " {$matchCount} open request(s) for your blood type ({$donor['blood_group']}).",
    ];
}

if ($donor['availability'] === 'unavailable') {
    $notifications[] = ['type' => 'warning', 'text' => "You're currently marked unavailable. Update this on your Profile page once you're ready to donate again."];
}

$page_title = 'Notifications';
$active = 'notifications';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Notifications</h1>
  <p class="subtitle">What's new on your account</p>

  <?php foreach ($notifications as $n): ?>
    <p class="banner-msg <?= $n['type'] ?>"><?= htmlspecialchars($n['text']) ?></p>
  <?php endforeach; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
