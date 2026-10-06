<?php
// confirm-appointment.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header('Location: LOGIN.html');
    exit;
}

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';

    if ($appointment_date && $appointment_time) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE appointments SET appointment_date = ?, appointment_time = ?, status = 'confirmed'
             WHERE id = ? AND status = 'pending'"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $appointment_date, $appointment_time, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: appointments.php');
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT a.appointment_date, a.appointment_time, a.location, u.fullname, u.blood_group
     FROM appointments a JOIN users u ON u.id = a.donor_id
     WHERE a.id = ? AND a.status = 'pending'"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$appt = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$appt) {
    die("That appointment was not found or has already been reviewed. <a href='appointments.php'>Back to Appointments</a>");
}

$page_title = 'Confirm Appointment';
$active = 'appointments';
include 'includes/layout-start.php';
?>

<div class="section-card" style="max-width:520px;">
  <h1>Confirm Appointment</h1>
  <p class="subtitle">
    <?= htmlspecialchars($appt['fullname']) ?> (<?= htmlspecialchars($appt['blood_group'] ?: 'N/A') ?>)
    requested <?= htmlspecialchars($appt['appointment_date']) ?> at <?= htmlspecialchars($appt['location']) ?>
  </p>

  <form method="POST" action="confirm-appointment.php">
    <input type="hidden" name="id" value="<?= (int) $id ?>">
    <div class="form-grid">
      <div class="form-field">
        <label for="appointment_date">Confirmed Date</label>
        <input type="date" id="appointment_date" name="appointment_date" value="<?= htmlspecialchars($appt['appointment_date']) ?>" required>
      </div>
      <div class="form-field">
        <label for="appointment_time">Confirmed Time</label>
        <input type="time" id="appointment_time" name="appointment_time"
               value="<?= htmlspecialchars($appt['appointment_time'] ?: '09:00') ?>" required>
      </div>
    </div>
    <button type="submit" class="btn-primary">Confirm Appointment</button>
    <p style="text-align:center;margin-top:14px;"><a href="appointments.php" style="color:#b71c1c;font-weight:600;font-size:13px;text-decoration:none;">&larr; Cancel and go back</a></p>
  </form>
</div>

<?php include 'includes/layout-end.php'; ?>
