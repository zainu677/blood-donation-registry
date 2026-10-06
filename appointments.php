<?php
// appointments.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header('Location: LOGIN.html');
    exit;
}

// Mark a confirmed appointment completed or as a no-show
if (isset($_GET['mark']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $newStatus = $_GET['mark'] === 'completed' ? 'completed' : (($_GET['mark'] === 'cancelled') ? 'cancelled' : null);

    if ($newStatus) {
        $stmt = mysqli_prepare($conn, "UPDATE appointments SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: appointments.php');
    exit;
}

$pending = mysqli_fetch_all(
    mysqli_query(
        $conn,
        "SELECT a.id, a.appointment_date, a.appointment_time, a.location, u.fullname, u.blood_group, u.phone
         FROM appointments a
         JOIN users u ON u.id = a.donor_id
         WHERE a.status = 'pending'
         ORDER BY a.appointment_date ASC"
    ),
    MYSQLI_ASSOC
);

$confirmed = mysqli_fetch_all(
    mysqli_query(
        $conn,
        "SELECT a.id, a.appointment_date, a.appointment_time, a.location, u.fullname, u.blood_group, u.phone
         FROM appointments a
         JOIN users u ON u.id = a.donor_id
         WHERE a.status = 'confirmed'
         ORDER BY a.appointment_date ASC"
    ),
    MYSQLI_ASSOC
);

$recent = mysqli_fetch_all(
    mysqli_query(
        $conn,
        "SELECT a.appointment_date, a.appointment_time, a.location, a.status, u.fullname, u.blood_group
         FROM appointments a
         JOIN users u ON u.id = a.donor_id
         WHERE a.status IN ('completed', 'cancelled')
         ORDER BY a.appointment_date DESC
         LIMIT 10"
    ),
    MYSQLI_ASSOC
);

function fmtTime($t) {
    return $t ? htmlspecialchars(date('g:i A', strtotime($t))) : '<span style="color:#aaa;">not set</span>';
}

$page_title = 'Appointments';
$active = 'appointments';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Pending Confirmation</h1>
  <p class="subtitle">New bookings that haven't been reviewed yet - confirm a time so the donor knows to come</p>

  <?php if (count($pending) === 0): ?>
    <p class="no-results">Nothing waiting on confirmation right now.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Donor</th><th>Blood Type</th><th>Requested Date</th><th>Preferred Time</th><th>Location</th><th></th></tr>
      <?php foreach ($pending as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['fullname']) ?></td>
          <td><?= htmlspecialchars($a['blood_group'] ?: '-') ?></td>
          <td><?= htmlspecialchars($a['appointment_date']) ?></td>
          <td><?= fmtTime($a['appointment_time']) ?></td>
          <td><?= htmlspecialchars($a['location']) ?></td>
          <td>
            <a href="confirm-appointment.php?id=<?= (int) $a['id'] ?>"
               style="color:#0c5460;font-weight:600;font-size:12px;text-decoration:none;">Confirm &rarr;</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<div class="section-card">
  <h1>Confirmed - Upcoming</h1>
  <p class="subtitle">The donor has been told to come at this date and time</p>

  <?php if (count($confirmed) === 0): ?>
    <p class="no-results">No confirmed appointments yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Donor</th><th>Blood Type</th><th>Phone</th><th>Date</th><th>Time</th><th>Location</th><th></th></tr>
      <?php foreach ($confirmed as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['fullname']) ?></td>
          <td><?= htmlspecialchars($a['blood_group'] ?: '-') ?></td>
          <td><?= htmlspecialchars($a['phone']) ?></td>
          <td><?= htmlspecialchars($a['appointment_date']) ?></td>
          <td><?= fmtTime($a['appointment_time']) ?></td>
          <td><?= htmlspecialchars($a['location']) ?></td>
          <td style="white-space:nowrap;">
            <a href="?mark=completed&id=<?= (int) $a['id'] ?>" class="confirm-action" data-confirm="Mark this appointment completed?"
               style="color:#155724;font-weight:600;font-size:12px;text-decoration:none;margin-right:8px;">Completed</a>
            <a href="?mark=cancelled&id=<?= (int) $a['id'] ?>" class="confirm-action" data-confirm="Mark this appointment as a no-show?"
               style="color:#721c24;font-weight:600;font-size:12px;text-decoration:none;">No-show</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p style="margin-top:16px;font-size:12.5px;color:#888;">
    Tip: if the donor actually gives blood, use <a href="log-donation.php" style="color:#b71c1c;font-weight:600;">Log a Donation</a> instead of
    Completed here &mdash; logging the donation records their history <em>and</em> automatically marks their appointment completed for you.
  </p>
</div>

<div class="section-card">
  <h1>Recent History</h1>
  <?php if (count($recent) === 0): ?>
    <p class="no-results">Nothing completed or cancelled yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Donor</th><th>Blood Type</th><th>Date</th><th>Time</th><th>Location</th><th>Status</th></tr>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['fullname']) ?></td>
          <td><?= htmlspecialchars($r['blood_group'] ?: '-') ?></td>
          <td><?= htmlspecialchars($r['appointment_date']) ?></td>
          <td><?= fmtTime($r['appointment_time']) ?></td>
          <td><?= htmlspecialchars($r['location']) ?></td>
          <td><span class="badge <?= $r['status'] === 'completed' ? 'success' : 'neutral' ?>"><?= htmlspecialchars(ucfirst($r['status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
