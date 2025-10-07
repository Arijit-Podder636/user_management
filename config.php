<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // empty for default XAMPP
define('DB_NAME', 'user_management_db'); // your DB name
define('DB_PORT', 3307); // adjust if different

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($mysqli->connect_errno) {
    die("DB connection failed: " . $mysqli->connect_error);
}

date_default_timezone_set('Asia/Kolkata');
