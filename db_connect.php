<?php
// Database connection settings
$servername = "localhost";
$username   = "root";
$password   = ""; // leave empty for XAMPP
$dbname     = "user_management_db"; // <-- your actual database name
$port       = 3307; // MySQL port shown in XAMPP

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// ✅ If connection successful
echo "✅ Database connected successfully!<br>";

// Example: Generate a unique Identicon for a user
// (In a real project, replace this with dynamic user email from session)
$email = "user@example.com";  

// Create MD5 hash for Gravatar identicon
$hash = md5(strtolower(trim($email)));

// Identicon URL
$identicon_url = "https://www.gravatar.com/avatar/$hash?d=identicon";

// Show it as image preview
echo "<img src='$identicon_url' alt='User Identicon' width='100' height='100'>";
?>
