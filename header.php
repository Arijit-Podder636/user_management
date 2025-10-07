<?php
// header.php

// Helper function to get current page
function currentPage() {
    return basename($_SERVER['PHP_SELF']);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Header (Green Bar) */
    .dashboard-topbar {
      background: #065f46;
      color: #fff;
      padding: 12px 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.15);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .dashboard-topbar .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .dashboard-topbar h1 {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .dashboard-topbar h1 a {
      color: #fff;
      text-decoration: none;
    }

    .topbar-nav {
      display: flex;
      align-items: center;
      gap: 12px; /* space between buttons */
    }

    .btn {
      background: #0f766e;
      color: #fff;
      text-decoration: none;
      padding: 6px 14px;
      border-radius: 6px;
      font-weight: 500;
      transition: 0.25s;
    }

    .btn:hover {
      background: #115e59;
    }

    .btn-logout {
      background: #b91c1c;
    }

    .btn-logout:hover {
      background: #991b1b;
    }

    main.container {
      margin-top: 25px;
    }

    @media (max-width: 600px) {
      .dashboard-topbar .container {
        flex-direction: column;
        align-items: flex-start;
      }

      .topbar-nav {
        margin-top: 10px;
        gap: 8px;
        flex-wrap: wrap;
      }
    }
  </style>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
  <header class="dashboard-topbar">
    <div class="container topbar-flex">
      <h1><a href="index.php">User Management System</a></h1>
      <nav class="topbar-nav">
        <?php if (isLoggedIn()): ?>
          <a href="profile.php" class="btn">Profile</a>
          <?php if (isAdmin()): ?>
            <a href="add_user.php" class="btn">Add User</a>
          <?php endif; ?>
          <a href="logout.php" class="btn btn-logout">Logout</a>
        <?php else: ?>
          <?php if (currentPage() == "login.php"): ?>
            <a href="register.php" class="btn">Register</a>
          <?php elseif (currentPage() == "register.php"): ?>
            <a href="login.php" class="btn">Login</a>
          <?php else: ?>
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn">Register</a>
          <?php endif; ?>
        <?php endif; ?>
      </nav>
    </div>
  </header>


  <main class="container">
