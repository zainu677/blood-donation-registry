<?php
// stock.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bank') {
    header('Location: LOGIN.html');
    exit;
}

$LOW_STOCK_THRESHOLD = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_group = $_POST['blood_group'] ?? '';
    $action      = $_POST['action'] ?? '';
    $quantity    = max(0, (int) ($_POST['quantity'] ?? 0));

    if ($quantity > 0 && in_array($blood_group, ['A+','A-','B+','B-','AB+','AB-','O+','O-'], true)) {
        if ($action === 'add') {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE blood_stock SET units_available = units_available + ? WHERE blood_group = ?"
            );
        } else {
            // remove, but never below zero
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE blood_stock SET units_available = GREATEST(0, units_available - ?) WHERE blood_group = ?"
            );
        }
        mysqli_stmt_bind_param($stmt, "is", $quantity, $blood_group);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

$stock = mysqli_fetch_all(
    mysqli_query($conn, "SELECT blood_group, units_available, last_updated FROM blood_stock ORDER BY blood_group"),
    MYSQLI_ASSOC
);

$page_title = 'Manage Stock';
$active = 'stock';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Blood Inventory</h1>
  <p class="subtitle">Current stock levels by blood type</p>

  <table class="results-table">
    <tr><th>Blood Type</th><th>Units Available</th><th>Last Updated</th><th>Status</th></tr>
    <?php foreach ($stock as $s): ?>
      <tr>
        <td><strong><?= htmlspecialchars($s['blood_group']) ?></strong></td>
        <td><?= (int) $s['units_available'] ?></td>
        <td><?= htmlspecialchars($s['last_updated']) ?></td>
        <td>
          <?php if ((int) $s['units_available'] === 0): ?>
            <span class="badge danger">Out of Stock</span>
          <?php elseif ((int) $s['units_available'] < $LOW_STOCK_THRESHOLD): ?>
            <span class="badge warning">Low Stock</span>
          <?php else: ?>
            <span class="badge success">Healthy</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="section-card">
  <h1>Adjust Stock</h1>
  <p class="subtitle">Record units coming in or going out</p>

  <form method="POST" action="stock.php">
    <div class="form-grid">
      <div class="form-field">
        <label for="blood_group">Blood Type</label>
        <select id="blood_group" name="blood_group" required>
          <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
            <option value="<?= $bg ?>"><?= $bg ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-field">
        <label for="quantity">Units</label>
        <input type="number" id="quantity" name="quantity" min="1" required>
      </div>

      <div class="form-field">
        <label for="action">Action</label>
        <select id="action" name="action" required>
          <option value="add">Add to stock</option>
          <option value="remove">Remove from stock</option>
        </select>
      </div>
    </div>

    <button type="submit" class="btn-primary">Update Inventory</button>
  </form>
</div>

<?php include 'includes/layout-end.php'; ?>
