<?php
// book-appointment.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: LOGIN.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = !empty($_POST['appointment_time']) ? $_POST['appointment_time'] : null;
    $location = trim($_POST['location'] ?? '');

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO appointments (donor_id, appointment_date, appointment_time, location) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isss", $_SESSION['user_id'], $appointment_date, $appointment_time, $location);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Donors can cancel while it's still pending or confirmed - not once it's already happened
if (isset($_GET['cancel'])) {
    $cancel_id = (int) $_GET['cancel'];
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND donor_id = ? AND status IN ('pending', 'confirmed')"
    );
    mysqli_stmt_bind_param($stmt, "ii", $cancel_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: book-appointment.php');
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, appointment_date, appointment_time, location, status
     FROM appointments WHERE donor_id = ? ORDER BY appointment_date DESC"
);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$appointments = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$page_title = 'Book Appointment';
$active = 'appointment';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Book a Donation Appointment</h1>
  <p class="subtitle">Pick a date and, if you like, a preferred time - the hospital will confirm the exact slot</p>

  <form method="POST" action="book-appointment.php">
    <div class="form-grid">
      <div class="form-field">
        <label for="appointment_date">Date</label>
        <input type="date" id="appointment_date" name="appointment_date" min="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="form-field">
        <label for="appointment_time">Preferred Time (optional)</label>
        <input type="time" id="appointment_time" name="appointment_time">
      </div>
      <div class="form-field">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" placeholder="City / Clinic" required>
      </div>
    </div>
    <button type="submit" class="btn-primary">Book Appointment</button>
  </form>
</div>

<div class="section-card">
  <h1>My Appointments</h1>

  <?php if (count($appointments) === 0): ?>
    <p class="no-results">No appointments booked yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Date</th><th>Time</th><th>Location</th><th>Status</th><th></th></tr>
      <?php foreach ($appointments as $a):
        $badgeClass = ['pending' => 'warning', 'confirmed' => 'info', 'completed' => 'success', 'cancelled' => 'neutral'][$a['status']] ?? 'neutral';
        $statusText = ['pending' => 'Pending Review', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'][$a['status']] ?? ucfirst($a['status']);
      ?>
        <tr>
          <td><?= htmlspecialchars($a['appointment_date']) ?></td>
          <td><?= $a['appointment_time'] ? htmlspecialchars(date('g:i A', strtotime($a['appointment_time']))) : '—' ?></td>
          <td><?= htmlspecialchars($a['location']) ?></td>
          <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statusText) ?></span></td>
          <td>
            <?php if (in_array($a['status'], ['pending', 'confirmed'], true)): ?>
              <a href="?cancel=<?= (int) $a['id'] ?>" class="confirm-action" data-confirm="Cancel this appointment?"
                 style="color:#b71c1c;font-weight:600;font-size:12px;text-decoration:none;">Cancel</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

    <p style="margin-top:16px;font-size:12.5px;color:#888;">
      <span class="badge warning" style="margin-right:6px;">Pending Review</span> the hospital hasn't looked at this yet &mdash;
      <span class="badge info" style="margin:0 6px;">Confirmed</span> means the date/time above is set and you should go &mdash;
      if it's still Pending close to your date, it's worth double-checking with the hospital directly.
    </p>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
