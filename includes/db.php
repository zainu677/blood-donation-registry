<?php
// includes/db.php
// Single place to configure the database connection.
// Every other PHP file includes this instead of connecting on its own.

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';           // default XAMPP password is blank
$db_name = 'lifelink_db';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
