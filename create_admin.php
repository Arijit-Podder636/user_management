<?php
// create_admin.php - run once to create a default admin, then remove this file.
require 'functions.php';

$email = 'admin@example.com';
$pass = 'Admin@123';
$first = 'Admin';
$last = 'User';

// check if exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "Admin already exists.";
    exit;
}
$stmt->close();

$stmt = $mysqli->prepare("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
$stmt->execute();
$stmt->bind_result($role_id);
$stmt->fetch();
$stmt->close();

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO users (role_id, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $role_id, $first, $last, $email, $hash);
if ($stmt->execute()) {
    echo "Admin created: $email with password $pass. DELETE create_admin.php now for security.";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
