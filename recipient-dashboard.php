<?php
// recipient-dashboard.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header('Location: LOGIN.html');
    exit;
}

$myRequests = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM blood_requests WHERE requester_id = " . (int) $_SESSION['user_id']
))['total'];

$myFeedback = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM feedback WHERE user_id = " . (int) $_SESSION['user_id']
))['total'];

$page_title = 'Recipient Dashboard';
$active = 'dashboard';
include 'includes/layout-start.php';
?>

<div class="hero-banner recipient">
  <div>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></h2>
    <p>Search for donors, post what you need, and track your requests — all in one place.</p>
  </div>
  <svg width="80" height="90" viewBox="0 0 100 110" opacity="0.85">
    <path d="M50 15 C50 15 15 55 15 78 A35 35 0 0 0 85 78 C85 55 50 15 50 15 Z" fill="#fff" opacity="0.9"/>
  </svg>
</div>

<div class="stat-row">
  <div class="stat-card">
    <div class="stat-num"><?= (int) $myRequests ?></div>
    <div class="stat-label">Requests Posted</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $myFeedback ?></div>
    <div class="stat-label">Feedback Submitted</div>
  </div>
</div>

<div class="cards">

    <div class="action-card" style="cursor:default;">
        <div class="icon-badge blue">👤</div>
        <h3>My Profile</h3>
        <p>Coming soon</p>
    </div>

    <a href="search.php" class="action-card">
        <div class="icon-badge red">🔍</div>
        <h3>Find Donors</h3>
        <p>By blood type & location</p>
    </a>

    <a href="recipient-requests.php" class="action-card">
        <div class="icon-badge dark">📋</div>
        <h3>My Requests</h3>
        <p><?= $myRequests > 0 ? "$myRequests posted" : 'None yet' ?></p>
    </a>

    <a href="feedback.php" class="action-card">
        <div class="icon-badge yellow">💬</div>
        <h3>Give Feedback</h3>
        <p>Share your experience</p>
    </a>

    <div class="action-card" style="cursor:default;">
        <div class="icon-badge purple">🔔</div>
        <h3>Notifications</h3>
        <p>Coming soon</p>
    </div>

    <a href="logout.php" class="action-card">
        <div class="icon-badge green">⇥</div>
        <h3>Logout</h3>
        <p>See you next time</p>
    </a>

</div>

<p class="banner-msg info" style="margin-top:24px;">
    Donor phone numbers are only shared with verified hospitals — your search results show blood type and
    availability only. Contact a hospital to arrange a match.
</p>

<?php include 'includes/layout-end.php'; ?>
