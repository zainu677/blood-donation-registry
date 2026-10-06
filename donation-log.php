<?php
// donation-log.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bank') {
    header('Location: LOGIN.html');
    exit;
}

$donations = mysqli_fetch_all(
    mysqli_query(
        $conn,
        "SELECT d.donation_date, d.hospital_name, u.fullname, u.blood_group
         FROM donations d
         JOIN users u ON u.id = d.donor_id
         ORDER BY d.donation_date DESC"
    ),
    MYSQLI_ASSOC
);

$page_title = 'Donation Log';
$active = 'donation-log';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>System-Wide Donation Log</h1>
  <p class="subtitle">Every donation recorded by hospital staff, across all donors</p>

  <?php if (count($donations) === 0): ?>
    <p class="no-results">No donations logged yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Donor</th><th>Blood Type</th><th>Hospital / Location</th><th>Date</th></tr>
      <?php foreach ($donations as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['fullname']) ?></td>
          <td><?= htmlspecialchars($d['blood_group'] ?: '-') ?></td>
          <td><?= htmlspecialchars($d['hospital_name']) ?></td>
          <td><?= htmlspecialchars($d['donation_date']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
