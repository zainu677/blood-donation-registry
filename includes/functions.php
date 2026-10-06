<?php
// includes/functions.php

/**
 * Finds approved donors matching a blood group and location who haven't
 * donated in the last 90 days (requirement: only show truly available donors).
 */
function findMatchingDonors($conn, $blood_group, $location)
{
    $sql = "SELECT id, fullname, phone, blood_group, location, last_donation_date
            FROM users
            WHERE role = 'donor'
              AND status = 'approved'
              AND blood_group = ?
              AND location LIKE ?
              AND (last_donation_date IS NULL OR DATEDIFF(CURDATE(), last_donation_date) >= 90)";

    $stmt = mysqli_prepare($conn, $sql);
    $locationLike = '%' . $location . '%';
    mysqli_stmt_bind_param($stmt, "ss", $blood_group, $locationLike);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $donors = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $donors;
}
