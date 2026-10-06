<?php
// includes/layout-start.php
// Expects $page_title and $active to be set by the including page before this runs.
$fullname = $_SESSION['fullname'] ?? 'User';
$initials = strtoupper(substr(trim($fullname), 0, 1)) ?: 'U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title ?? 'Dashboard') ?> | LifeLink</title>
<link rel="stylesheet" href="dashboard-theme.css">
<link rel="stylesheet" href="results.css">
</head>
<body>

<div class="app-shell">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <h1><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h1>
            <div class="user-chip">
                <div class="avatar-circle"><?= htmlspecialchars($initials) ?></div>
                <?= htmlspecialchars($fullname) ?>
            </div>
        </div>

        <div class="page-content">
