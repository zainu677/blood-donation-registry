<?php

$role = $_SESSION['role'] ?? '';

function sidebarLink($href, $emoji, $label, $key, $active)
{
    $cls = ($key === $active) ? 'active' : '';
    echo '<a href="' . htmlspecialchars($href) . '" class="' . $cls . '">'
       . '<span class="emoji">' . $emoji . '</span><span class="label">' . htmlspecialchars($label) . '</span>'
       . '</a>';
}
?>
<aside class="sidebar">
  <div class="brand"><span class="emoji">❤</span><span class="text">LifeLink</span></div>

  <nav>
    <?php if ($role === 'donor'): ?>
        <?php sidebarLink('donor.php', '🏠', 'Dashboard', 'dashboard', $active); ?>
        <?php sidebarLink('profile.php', '👤', 'Profile', 'profile', $active); ?>
        <?php sidebarLink('book-appointment.php', '📅', 'Book Appointment', 'appointment', $active); ?>
        <?php sidebarLink('donation-history.php', '📄', 'Donation History', 'history', $active); ?>
        <?php sidebarLink('campaigns.php', '📢', 'Campaigns', 'campaigns', $active); ?>
        <?php sidebarLink('notifications.php', '🔔', 'Notifications', 'notifications', $active); ?>

    <?php elseif ($role === 'hospital'): ?>
        <?php sidebarLink('hospital-dashboard.php', '🏠', 'Dashboard', 'dashboard', $active); ?>
        <?php sidebarLink('search.php', '🔍', 'Search Donors', 'search', $active); ?>
        <?php sidebarLink('request-blood.php', '🚨', 'Urgent Request', 'request', $active); ?>
        <?php sidebarLink('appointments.php', '📅', 'Appointments', 'appointments', $active); ?>
        <?php sidebarLink('log-donation.php', '🩸', 'Log a Donation', 'log-donation', $active); ?>



    <?php elseif ($role === 'bank'): ?>
        <?php sidebarLink('bank-dashboard.php', '🏠', 'Dashboard', 'dashboard', $active); ?>
        <?php sidebarLink('stock.php', '🩸', 'Manage Stock', 'stock', $active); ?>
        <?php sidebarLink('fulfill-requests.php', '✅', 'Fulfill Requests', 'fulfill', $active); ?>
        <?php sidebarLink('donation-log.php', '📄', 'Donation Log', 'donation-log', $active); ?>

    <?php elseif ($role === 'admin'): ?>
        <?php sidebarLink('admin-dashboard.php', '🏠', 'Dashboard', 'dashboard', $active); ?>
        <?php sidebarLink('create-staff.php', '🧑‍⚕️', 'Create Staff Account', 'create-staff', $active); ?>
    <?php endif; ?>

    <?php sidebarLink('change-password.php', '🔑', 'Change Password', 'change-password', $active); ?>
  </nav>

  <a href="logout.php" class="logout-link"><span class="emoji">⇥</span><span class="label">Logout</span></a>
</aside>
