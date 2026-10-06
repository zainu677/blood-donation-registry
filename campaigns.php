<?php
// campaigns.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: LOGIN.html');
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT blood_group, quantity, location, created_at
     FROM blood_requests
     WHERE blood_group = (SELECT blood_group FROM users WHERE id = ?)
       AND status = 'open'
     ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$campaigns = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$page_title = 'Campaigns';
$active = 'campaigns';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Campaigns & Urgent Needs</h1>
  <p class="subtitle">Open requests that match your blood type</p>

  <?php if (count($campaigns) === 0): ?>
    <p class="no-results">No open requests match your blood type right now. Check back soon!</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Blood Type</th><th>Quantity</th><th>Location</th><th>Posted</th></tr>
      <?php foreach ($campaigns as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['blood_group']) ?></td>
          <td><?= (int) $c['quantity'] ?></td>
          <td><?= htmlspecialchars($c['location']) ?></td>
          <td><?= htmlspecialchars($c['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <p style="margin-top:16px;font-size:13px;color:#777;">
      Interested in helping with one of these? Book an appointment and mention the location when you arrive.
    </p>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
