<?php
// admin-dashboard.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: LOGIN.html');
    exit;
}

$totalUsers   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role != 'admin'"))['total'];
$pendingUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE status = 'pending'"))['total'];
$totalDonors  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'donor'"))['total'];
$totalDonations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM donations"))['total'];

$result = mysqli_query(
    $conn,
    "SELECT id, fullname, username, email, role, blood_group, location, status
     FROM users
     WHERE role != 'admin'
     ORDER BY created_at DESC"
);

$page_title = 'NBTS manager Dashboard';
$active = 'dashboard';
include 'includes/layout-start.php';
?>

<div class="hero-banner admin">
  <div>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></h2>
    <p>Approve new accounts, keep records accurate, and log donations as they happen.</p>
  </div>
  <svg width="80" height="90" viewBox="0 0 100 110" opacity="0.85">
    <path d="M50 10 L90 25 V55 C90 80 72 98 50 105 C28 98 10 80 10 55 V25 Z" fill="#fff" opacity="0.15"/>
    <path d="M50 20 L80 32 V55 C80 74 66 88 50 94 C34 88 20 74 20 55 V32 Z" fill="#fff" opacity="0.35"/>
  </svg>
</div>

<div class="stat-row">
  <div class="stat-card">
    <div class="stat-num"><?= (int) $totalUsers ?></div>
    <div class="stat-label">Total Accounts</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $pendingUsers ?></div>
    <div class="stat-label">Pending Approval</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $totalDonors ?></div>
    <div class="stat-label">Registered Donors</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $totalDonations ?></div>
    <div class="stat-label">Donations Logged</div>
  </div>
</div>

<div class="section-card">
  <h1>All Accounts</h1>
  <p class="subtitle">Approve, edit, or remove donor, recipient, and hospital records</p>

  <?php if (mysqli_num_rows($result) === 0): ?>
    <p class="no-results">No accounts registered yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr>
        <th>Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Blood Type</th>
        <th>Location</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= htmlspecialchars($row['fullname']) ?></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars(ucfirst($row['role'])) ?></td>
          <td><?= htmlspecialchars($row['blood_group'] ?: '-') ?></td>
          <td><?= htmlspecialchars($row['location']) ?></td>
          <td>
            <span class="badge <?= $row['status'] === 'approved' ? 'success' : 'warning' ?>">
              <?= htmlspecialchars(ucfirst($row['status'])) ?>
            </span>
          </td>
          <td style="white-space:nowrap;">
            <?php if ($row['status'] === 'pending'): ?>
              <a href="approve-user.php?id=<?= (int) $row['id'] ?>" style="color:#155724;font-weight:600;font-size:12px;text-decoration:none;margin-right:8px;">Approve</a>
            <?php endif; ?>
            <a href="edit-user.php?id=<?= (int) $row['id'] ?>" style="color:#3730a3;font-weight:600;font-size:12px;text-decoration:none;margin-right:8px;">Edit</a>
            <a href="delete-user.php?id=<?= (int) $row['id'] ?>" class="confirm-action" data-confirm="Delete this record? This cannot be undone."
               style="color:#721c24;font-weight:600;font-size:12px;text-decoration:none;">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
