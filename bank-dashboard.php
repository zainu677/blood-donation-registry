<?php
// bank-dashboard.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bank') {
    header('Location: LOGIN.html');
    exit;
}

$totalUnits = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(units_available),0) AS total FROM blood_stock"))['total'];
$lowStockTypes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM blood_stock WHERE units_available < 5"))['total'];
$openRequests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM blood_requests WHERE status = 'open'"))['total'];
$totalDonations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM donations"))['total'];

$stockByType = mysqli_fetch_all(
    mysqli_query($conn, "SELECT blood_group, units_available FROM blood_stock ORDER BY blood_group"),
    MYSQLI_ASSOC
);

$page_title = 'Blood Bank Dashboard';
$active = 'dashboard';
include 'includes/layout-start.php';
?>

<div class="hero-banner bank">
  <div>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></h2>
    <p>Keep stock levels healthy and fulfill requests as they come in.</p>
  </div>
  <svg width="80" height="90" viewBox="0 0 100 110" opacity="0.85">
    <rect x="20" y="30" width="60" height="60" rx="8" fill="#fff" opacity="0.2"/>
    <rect x="30" y="10" width="40" height="30" rx="6" fill="#fff" opacity="0.4"/>
  </svg>
</div>

<div class="stat-row">
  <div class="stat-card">
    <div class="stat-num"><?= (int) $totalUnits ?></div>
    <div class="stat-label">Total Units in Stock</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $lowStockTypes ?></div>
    <div class="stat-label">Blood Types Running Low</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $openRequests ?></div>
    <div class="stat-label">Open Requests to Fulfill</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $totalDonations ?></div>
    <div class="stat-label">Total Donations Logged</div>
  </div>
</div>

<div class="section-card">
  <h1>Stock at a Glance</h1>
  <div class="stat-row" style="grid-template-columns:repeat(auto-fit, minmax(90px, 1fr));">
    <?php foreach ($stockByType as $s): ?>
      <div class="stat-card" style="text-align:center;padding:14px;">
        <div class="stat-num" style="font-size:20px;"><?= htmlspecialchars($s['blood_group']) ?></div>
        <div class="stat-label"><?= (int) $s['units_available'] ?> units</div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="cards">

    <a href="stock.php" class="action-card">
        <div class="icon-badge blue">🩸</div>
        <h3>Manage Stock</h3>
        <p>Add or remove units</p>
    </a>

    <a href="fulfill-requests.php" class="action-card">
        <div class="icon-badge red">✅</div>
        <h3>Fulfill Requests</h3>
        <p><?= $openRequests > 0 ? "$openRequests waiting" : 'All caught up' ?></p>
    </a>

    <a href="donation-log.php" class="action-card">
        <div class="icon-badge dark">📄</div>
        <h3>Donation Log</h3>
        <p>System-wide records</p>
    </a>

    <a href="logout.php" class="action-card">
        <div class="icon-badge green">⇥</div>
        <h3>Logout</h3>
        <p>See you next time</p>
    </a>

</div>

<?php include 'includes/layout-end.php'; ?>
