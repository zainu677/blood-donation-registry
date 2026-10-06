<?php
// hospital-dashboard.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header('Location: LOGIN.html');
    exit;
}

$openRequests = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM blood_requests WHERE requester_id = " . (int) $_SESSION['user_id'] . " AND status = 'open'"
))['total'];

$totalAvailableDonors = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users
     WHERE role = 'donor' AND status = 'approved'
       AND (last_donation_date IS NULL OR DATEDIFF(CURDATE(), last_donation_date) >= 90)"
))['total'];

$upcomingAppointments = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM appointments WHERE status = 'scheduled'"
))['total'];

$page_title = 'Hospital Dashboard';
$active = 'dashboard';
include 'includes/layout-start.php';
?>

<div class="hero-banner hospital">
  <div>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></h2>
    <p>
      <?php if ($_SESSION['status'] !== 'approved'): ?>
        Your account is awaiting verification — you can search, but donor phone numbers stay hidden until you're approved.
      <?php else: ?>
        You're a verified hospital account — full donor contact details are visible in search results.
      <?php endif; ?>
    </p>
  </div>
  <svg width="90" height="90" viewBox="0 0 100 100" opacity="0.85">
    <path d="M50 12 L50 88 M12 50 L88 50" stroke="#fff" stroke-width="14" stroke-linecap="round" opacity="0.25"/>
    <circle cx="50" cy="50" r="34" fill="none" stroke="#fff" stroke-width="6" opacity="0.5"/>
  </svg>
</div>

<div class="stat-row">
  <div class="stat-card">
    <div class="stat-num"><?= (int) $totalAvailableDonors ?></div>
    <div class="stat-label">Currently Available Donors</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $openRequests ?></div>
    <div class="stat-label">Your Open Requests</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $upcomingAppointments ?></div>
    <div class="stat-label">Upcoming Appointments</div>
  </div>
</div>

<div class="cards">

    <a href="search.php" class="action-card">
        <div class="icon-badge blue">🔍</div>
        <h3>Search Donors</h3>
        <p>By blood type & location</p>
    </a>

    <a href="request-blood.php" class="action-card">
        <div class="icon-badge red">🚨</div>
        <h3>Post Urgent Request</h3>
        <p>Alerts matching donors</p>
    </a>

    <a href="appointments.php" class="action-card">
        <div class="icon-badge yellow">📅</div>
        <h3>Appointments</h3>
        <p><?= $upcomingAppointments > 0 ? "$upcomingAppointments upcoming" : 'None scheduled' ?></p>
    </a>

    <a href="log-donation.php" class="action-card">
        <div class="icon-badge purple">🩸</div>
        <h3>Log a Donation</h3>
        <p>Record a completed donation</p>
    </a>

    <a href="logout.php" class="action-card">
        <div class="icon-badge dark">⇥</div>
        <h3>Logout</h3>
        <p>See you next time</p>
    </a>

</div>

<?php include 'includes/layout-end.php'; ?>
