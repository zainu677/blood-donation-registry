<?php
// log-donation.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header('Location: LOGIN.html');
    exit;
}

$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_id       = (int) ($_POST['donor_id'] ?? 0);
    $donation_date  = $_POST['donation_date'] ?? date('Y-m-d');
    $hospital_name  = trim($_POST['hospital_name'] ?? '');

    // Record the donation
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO donations (donor_id, donation_date, hospital_name) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iss", $donor_id, $donation_date, $hospital_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Requirement 12: update the donor's availability status after a completed donation
    $update = mysqli_prepare(
        $conn,
        "UPDATE users SET last_donation_date = ?, availability = 'unavailable' WHERE id = ?"
    );
    mysqli_stmt_bind_param($update, "si", $donation_date, $donor_id);
    mysqli_stmt_execute($update);
    mysqli_stmt_close($update);

    // If this donor had a scheduled appointment, mark it completed automatically
    // so hospital staff never have to update the same visit in two places.
    $completeAppt = mysqli_prepare(
        $conn,
        "UPDATE appointments SET status = 'completed'
         WHERE donor_id = ? AND status IN ('pending', 'confirmed')
         ORDER BY appointment_date ASC LIMIT 1"
    );
    mysqli_stmt_bind_param($completeAppt, "i", $donor_id);
    mysqli_stmt_execute($completeAppt);
    mysqli_stmt_close($completeAppt);

    $saved = true;
}

// Donor list for the dropdown
$donors = mysqli_fetch_all(
    mysqli_query($conn, "SELECT id, fullname, blood_group FROM users WHERE role = 'donor' AND status = 'approved' ORDER BY fullname"),
    MYSQLI_ASSOC
);

// Recent donations log
$recent = mysqli_fetch_all(
    mysqli_query(
        $conn,
        "SELECT d.donation_date, d.hospital_name, u.fullname, u.blood_group
         FROM donations d
         JOIN users u ON u.id = d.donor_id
         ORDER BY d.donation_date DESC
         LIMIT 15"
    ),
    MYSQLI_ASSOC
);

$page_title = 'Log a Donation';
$active = 'log-donation';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Log a Completed Donation</h1>
  <p class="subtitle">This updates the donor's history and marks them unavailable for the next 90 days</p>

  <?php if ($saved): ?>
    <p class="banner-msg success">Donation recorded — the donor's history and availability have been updated.</p>
  <?php endif; ?>

  <form method="POST" action="log-donation.php">
    <div class="form-row" style="display:flex;gap:20px;margin-bottom:18px;">
      <div class="form-group" style="flex:1;display:flex;flex-direction:column;">
        <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Donor</label>
        <select name="donor_id" required style="padding:12px 14px;border:1px solid #ccc;border-radius:8px;font-size:14px;background:#fafafa;">
          <option value="" disabled selected>Select donor</option>
          <?php foreach ($donors as $d): ?>
            <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['fullname']) ?> (<?= htmlspecialchars($d['blood_group'] ?: 'N/A') ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="flex:1;display:flex;flex-direction:column;">
        <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Donation Date</label>
        <input type="date" name="donation_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required
               style="padding:12px 14px;border:1px solid #ccc;border-radius:8px;font-size:14px;background:#fafafa;">
      </div>
    </div>

    <div class="form-group" style="display:flex;flex-direction:column;margin-bottom:20px;">
      <label style="font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Hospital / Location</label>
      <input type="text" name="hospital_name" placeholder="e.g. Muhimbili National Hospital" required
             style="padding:12px 14px;border:1px solid #ccc;border-radius:8px;font-size:14px;background:#fafafa;">
    </div>

    <button type="submit" style="width:100%;padding:13px;background:#b71c1c;color:#fff;font-size:16px;font-weight:600;border:none;border-radius:8px;cursor:pointer;">
      Save Donation
    </button>
  </form>
</div>

<div class="section-card">
  <h1>Recent Donations</h1>
  <?php if (count($recent) === 0): ?>
    <p class="no-results">No donations logged yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Donor</th><th>Blood Type</th><th>Hospital / Location</th><th>Date</th></tr>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['fullname']) ?></td>
          <td><?= htmlspecialchars($r['blood_group'] ?: '-') ?></td>
          <td><?= htmlspecialchars($r['hospital_name']) ?></td>
          <td><?= htmlspecialchars($r['donation_date']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
