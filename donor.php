<?php
// donor.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: LOGIN.html');
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT fullname, blood_group, location, availability, status, last_donation_date FROM users WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$donor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Total donations
$stmt2 = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM donations WHERE donor_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt2);
$totalDonations = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2))['total'];
mysqli_stmt_close($stmt2);

// Days since last donation
$daysSince = null;
if ($donor['last_donation_date']) {
    $daysSince = (int) floor((strtotime('now') - strtotime($donor['last_donation_date'])) / 86400);
}
$isEligible = ($daysSince === null || $daysSince >= 90);

// Open campaigns matching this donor's blood type
$stmt3 = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM blood_requests WHERE blood_group = ? AND status = 'open'");
mysqli_stmt_bind_param($stmt3, "s", $donor['blood_group']);
mysqli_stmt_execute($stmt3);
$openCampaigns = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt3))['total'];
mysqli_stmt_close($stmt3);

$page_title = 'Donor Dashboard';
$active = 'dashboard';
include 'includes/layout-start.php';
?>

<div class="hero-banner donor">
  <div>
    <h2>Welcome back, <?= htmlspecialchars($donor['fullname']) ?>!</h2>
    <p>
      <?= $isEligible ? "You're eligible to donate right now — every drop helps." : "You're on your 90-day rest window. Thanks for your last donation!" ?>
    </p>
  </div>
  <svg width="90" height="100" viewBox="0 0 200 240" opacity="0.85">
    <path d="M100 10 C100 10 20 110 20 165 A80 80 0 0 0 180 165 C180 110 100 10 100 10 Z" fill="#ffffff" opacity="0.18"/>
    <path d="M100 45 C100 45 45 120 45 160 A55 55 0 0 0 155 160 C155 120 100 45 100 45 Z" fill="#ffffff"/>
  </svg>
</div>

<div class="stat-row">
  <div class="stat-card">
    <div class="stat-num"><?= htmlspecialchars($donor['blood_group'] ?: 'N/A') ?></div>
    <div class="stat-label">Blood Type</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $totalDonations ?></div>
    <div class="stat-label">Total Donations</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= $daysSince !== null ? $daysSince : '—' ?></div>
    <div class="stat-label">Days Since Last Donation</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= (int) $openCampaigns ?></div>
    <div class="stat-label">Open Requests for Your Type</div>
  </div>
</div>

<?php if ($donor['status'] !== 'approved'): ?>
  <p class="banner-msg warning">Your account is still awaiting admin approval — hospitals won't see you in search yet.</p>
<?php endif; ?>

<div class="cards">

    <a href="profile.php" class="action-card">
        <div class="icon-badge blue">👤</div>
        <h3>Profile</h3>
        <p><?= htmlspecialchars($donor['location']) ?></p>
    </a>

    <a href="book-appointment.php" class="action-card">
        <div class="icon-badge red">📅</div>
        <h3>Book Appointment</h3>
        <p>Schedule your next donation</p>
    </a>

    <a href="donation-history.php" class="action-card">
        <div class="icon-badge dark">📄</div>
        <h3>Donation History</h3>
        <p><?= $totalDonations > 0 ? "$totalDonations recorded" : 'None yet' ?></p>
    </a>

    <a href="campaigns.php" class="action-card">
        <div class="icon-badge purple">📢</div>
        <h3>Campaigns</h3>
        <p><?= $openCampaigns > 0 ? "$openCampaigns matching now" : 'None right now' ?></p>
    </a>

    <a href="notifications.php" class="action-card">
        <div class="icon-badge yellow">🔔</div>
        <h3>Notifications</h3>
        <p>Account & match alerts</p>
    </a>

    <a href="logout.php" class="action-card">
        <div class="icon-badge green">⇥</div>
        <h3>Logout</h3>
        <p>See you next time</p>
    </a>

</div>

<?php include 'includes/layout-end.php'; ?>
