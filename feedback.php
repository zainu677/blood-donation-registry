<?php
// feedback.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header('Location: LOGIN.html');
    exit;
}

$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $rating  = !empty($_POST['rating']) ? (int) $_POST['rating'] : null;

    if ($message !== '') {
        $stmt = mysqli_prepare($conn, "INSERT INTO feedback (user_id, message, rating) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isi", $_SESSION['user_id'], $message, $rating);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $submitted = true;
    }
}

$history_stmt = mysqli_prepare(
    $conn,
    "SELECT message, rating, created_at FROM feedback WHERE user_id = ? ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($history_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($history_stmt);
$history = mysqli_fetch_all(mysqli_stmt_get_result($history_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($history_stmt);

$page_title = 'Give Feedback';
$active = 'feedback';
include 'includes/layout-start.php';
?>

<div class="section-card">
  <h1>Give Feedback</h1>
  <p class="subtitle">Tell us about your experience using the registry</p>

  <?php if ($submitted): ?>
    <p class="banner-msg success">Thanks - your feedback has been submitted.</p>
  <?php endif; ?>

  <form method="POST" action="feedback.php">
    <div class="form-field">
      <label>Rating (optional)</label>
      <div class="star-rating" style="font-size:32px;color:#e8b400;cursor:pointer;letter-spacing:4px;">
        <span>☆</span><span>☆</span><span>☆</span><span>☆</span><span>☆</span>
      </div>
      <input type="hidden" id="rating_value" name="rating" value="0">
    </div>

    <div class="form-field">
      <label for="message">Your Feedback</label>
      <textarea id="message" name="message" rows="4" placeholder="How was your experience?" required></textarea>
    </div>

    <button type="submit" class="btn-primary">Submit Feedback</button>
  </form>
</div>

<div class="section-card">
  <h1>My Past Feedback</h1>
  <?php if (count($history) === 0): ?>
    <p class="no-results">You haven't submitted any feedback yet.</p>
  <?php else: ?>
    <table class="results-table">
      <tr><th>Rating</th><th>Message</th><th>Date</th></tr>
      <?php foreach ($history as $f): ?>
        <tr>
          <td><?= $f['rating'] ? str_repeat('★', (int) $f['rating']) : '-' ?></td>
          <td><?= htmlspecialchars($f['message']) ?></td>
          <td><?= htmlspecialchars($f['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
