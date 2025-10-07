<?php
// delete_user.php
require 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validate ID and CSRF token
$id = intval($_POST['id'] ?? 0);
$token = $_POST['csrf_token'] ?? '';

if ($id <= 0 || !checkToken($token)) {
    die("Invalid request");
}

global $mysqli;

// 1️⃣ Fetch profile picture filename safely
$stmt = $mysqli->prepare("SELECT profile_pic FROM users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

// 2️⃣ Delete user with prepared statement
$stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // 3️⃣ Delete profile picture file if it exists and is not default
    if (!empty($profile_pic) && $profile_pic !== 'default.png' && file_exists(__DIR__ . '/uploads/' . $profile_pic)) {
        @unlink(__DIR__ . '/uploads/' . $profile_pic);
    }
    header('Location: index.php?msg=User+deleted+successfully');
    exit;
} else {
    die("Delete failed: " . $stmt->error);
}

$stmt->close();
?>
