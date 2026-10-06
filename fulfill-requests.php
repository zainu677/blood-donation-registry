<?php
// fulfill-requests.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bank') {
    header('Location: LOGIN.html');
    exit;
}

if (isset($_GET['fulfill'])) {
    $request_id = (int) $_GET['fulfill'];

    $stmt = mysqli_prepare($conn, "SELECT blood_group, quantity FROM blood_requests WHERE id = ? AND status = 'open'");
    mysqli_stmt_bind_param($stmt, "i", $request_id);
    mysqli_stmt_execute($stmt);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($request) {
        $update = mysqli_prepare($conn, "UPDATE blood_requests SET status = 'fulfilled' WHERE id = ?");
        mysqli_stmt_bind_param($update, "i", $request_id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        // Deduct from stock, never below zero
        $deduct = mysqli_prepare(
            $conn,
            "UPDATE blood_stock SET units_available = GREATEST(0, units_available - ?) WHERE blood_group = ?"
        );
        mysqli_stmt_bind_param($deduct, "is", $request['quantity'], $request['blood_group']);
        mysqli_stmt_execute($deduct);
        mysqli_stmt_close($deduct);
    }

    header('Location: fulfill-requests.php');
    exit;
}

$open = mysqli_fetch_all(
    mysqli_query(
        $conn,
        "SELECT br.id, br.blood_group, br.quantity, br.location, br.created_at, u.fullname, u.role AS requester_role
         FROM blood_requests br
         JOIN users u ON u.id = br.requester_id
         WHERE br.status = 'open'
         ORDER BY br.created_at ASC"
    ),
    MYSQLI_ASSOC
);

$fulfilled = mysqli_fetch_all(
    mysqli_query(
        $conn,
        "SELECT br.blood_group, br.quantity, br.location, u.fullname
         FROM blood_requests br
         JOIN users u ON u.id = br.requester_id
         WHERE br.status = 'fulfilled'
         ORDER BY br.created_at DESC
         LIMIT 10"
    ),
    MYSQLI_ASSOC
);

$page_title = 'Fulfill Requests';
$active = 'fulfill';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Open Blood Requests</h1>
  <p class="subtitle">Mark a request fulfilled once the blood has been supplied - this deducts it from stock automatically</p>

  <?php if (count($open) === 0): ?>
    <p class="no-results">No open requests right now.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Requested By</th><th>Type</th><th>Blood Type</th><th>Quantity</th><th>Location</th><th>Posted</th><th></th></tr>
      <?php foreach ($open as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['fullname']) ?></td>
          <td><?= htmlspecialchars(ucfirst($r['requester_role'])) ?></td>
          <td><strong><?= htmlspecialchars($r['blood_group']) ?></strong></td>
          <td><?= (int) $r['quantity'] ?></td>
          <td><?= htmlspecialchars($r['location']) ?></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td>
            <a href="?fulfill=<?= (int) $r['id'] ?>" class="confirm-action" data-confirm="Mark this request as fulfilled and deduct stock?"
               style="color:#155724;font-weight:600;font-size:12px;text-decoration:none;"> Fulfilled</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<div class="section-card">
  <h1>Recently Fulfilled</h1>
  <?php if (count($fulfilled) === 0): ?>
    <p class="no-results">Nothing fulfilled yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Requested By</th><th>Blood Type</th><th>Quantity</th><th>Location</th></tr>
      <?php foreach ($fulfilled as $f): ?>
        <tr>
          <td><?= htmlspecialchars($f['fullname']) ?></td>
          <td><?= htmlspecialchars($f['blood_group']) ?></td>
          <td><?= (int) $f['quantity'] ?></td>
          <td><?= htmlspecialchars($f['location']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
