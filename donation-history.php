<?php
// donation-history.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: LOGIN.html');
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT donation_date, hospital_name FROM donations WHERE donor_id = ? ORDER BY donation_date DESC"
);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$donations = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$page_title = 'Donation History';
$active = 'history';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Donation History</h1>
  <p class="subtitle">Every donation recorded against your account</p>

  <?php if (count($donations) === 0): ?>
    <p class="no-results">No donations recorded yet. Once a hospital logs your next donation, it'll show up here.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Date</th><th>Hospital / Location</th></tr>
      <?php foreach ($donations as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['donation_date']) ?></td>
          <td><?= htmlspecialchars($d['hospital_name'] ?: '-') ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
