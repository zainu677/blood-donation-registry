<?php
// search.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['hospital', 'recipient'], true)) {
    header('Location: LOGIN.html');
    exit;
}

$donors   = [];
$searched = false;
// Only verified hospital accounts ever see phone numbers - recipients never do
$show_phone = ($_SESSION['role'] === 'hospital' && $_SESSION['status'] === 'approved');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched    = true;
    $blood_group = $_POST['blood_group'] ?? '';
    $location    = trim($_POST['location'] ?? '');
    $donors      = findMatchingDonors($conn, $blood_group, $location);
}

$page_title = 'Search Donors';
$active = 'search';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Search for Donors</h1>
  <p class="subtitle">Find available donors by blood type and location</p>

  <form method="POST" action="search.php">
    <div class="form-grid">
      <div class="form-field">
        <label for="blood_group">Blood Type</label>
        <select id="blood_group" name="blood_group" required>
          <option value="" disabled <?= !isset($_POST['blood_group']) ? 'selected' : '' ?>>Select blood type</option>
          <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
            <option value="<?= $bg ?>" <?= (($_POST['blood_group'] ?? '') === $bg) ? 'selected' : '' ?>><?= $bg ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-field">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" placeholder="City / District"
               value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
      </div>
    </div>

    <button type="submit" class="btn-primary">Search</button>
  </form>

  <?php if ($searched): ?>
    <?php if (count($donors) === 0): ?>
      <p class="no-results">No matching donors found.</p>
    <?php else: ?>
      <table class="results-table">
        <tr>
          <th>Name</th>
          <th>Blood Type</th>
          <th>Phone</th>
          <th>Last Donation</th>
        </tr>
        <?php foreach ($donors as $d): ?>
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

<?php include 'includes/layout-end.php'; ?>
