<?php
// request-blood.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header('Location: LOGIN.html');
    exit;
}

$matches    = null;
$show_phone = ($_SESSION['status'] === 'approved');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_group = $_POST['blood_group'] ?? '';
    $quantity    = (int) ($_POST['quantity'] ?? 0);
    $location    = trim($_POST['location'] ?? '');

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO blood_requests (requester_id, blood_group, quantity, location) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isis", $_SESSION['user_id'], $blood_group, $quantity, $location);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $matches = findMatchingDonors($conn, $blood_group, $location);
}

$history_stmt = mysqli_prepare(
    $conn,
    "SELECT blood_group, quantity, location, status, created_at FROM blood_requests WHERE requester_id = ? ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($history_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($history_stmt);
$history = mysqli_fetch_all(mysqli_stmt_get_result($history_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($history_stmt);

$page_title = 'Post Urgent Request';
$active = 'request';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Post Urgent Blood Request</h1>
  <p class="subtitle">This alerts matching available donors in the area</p>

  <form method="POST" action="request-blood.php">
    <div class="form-grid">
      <div class="form-field">
        <label for="blood_group">Blood Type Needed</label>
        <select id="blood_group" name="blood_group" required>
          <option value="" disabled selected>Select blood type</option>
          <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
            <option value="<?= $bg ?>"><?= $bg ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-field">
        <label for="quantity">Quantity (units)</label>
        <input type="number" id="quantity" name="quantity" min="1" placeholder="e.g. 2" required>
      </div>
    </div>

    <div class="form-field">
      <label for="location">Location</label>
      <input type="text" id="location" name="location" placeholder="City / District" required>
    </div>

    <button type="submit" class="btn-primary">Post Request</button>
  </form>

  <?php if ($matches !== null): ?>
    <p style="margin-top:20px;font-weight:600;color:#333;">
      Request posted. <?= count($matches) ?> matching donor(s) found:
    </p>

    <?php if (count($matches) === 0): ?>
      <p class="no-results">No matching donors are currently available for this request.</p>
    <?php else: ?>
      <table class="results-table">
        <tr>
          <th>Name</th>
          <th>Blood Type</th>
          <th>Phone</th>
          <th>Last Donation</th>
        </tr>
        <?php foreach ($matches as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['fullname']) ?></td>
            <td><?= htmlspecialchars($d['blood_group']) ?></td>
            <td>
              <?php if ($show_phone): ?>
                <?= htmlspecialchars($d['phone']) ?>
              <?php else: ?>
                <span class="hidden-phone">Hidden - verify account to view</span>
              <?php endif; ?>
            </td>
            <td><?= $d['last_donation_date'] ? htmlspecialchars($d['last_donation_date']) : 'Never donated' ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  <?php endif; ?>
</div>

<div class="section-card">
  <h1>My Requests</h1>
  <p class="subtitle">Every urgent request you've posted, and whether it's been fulfilled</p>

  <?php if (count($history) === 0): ?>
    <p class="no-results">You haven't posted any requests yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Blood Type</th><th>Quantity</th><th>Location</th><th>Status</th><th>Posted</th></tr>
      <?php foreach ($history as $h): ?>
        <tr>
          <td><?= htmlspecialchars($h['blood_group']) ?></td>
          <td><?= (int) $h['quantity'] ?></td>
          <td><?= htmlspecialchars($h['location']) ?></td>
          <td><span class="badge <?= $h['status'] === 'fulfilled' ? 'success' : 'info' ?>"><?= htmlspecialchars(ucfirst($h['status'])) ?></span></td>
          <td><?= htmlspecialchars($h['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
