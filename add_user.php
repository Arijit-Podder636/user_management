<?php
require 'functions.php';

// Only admin can access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = sanitize($_POST['first_name'] ?? '');
    $last = sanitize($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $role_id = intval($_POST['role_id'] ?? 0);

    // Validation
    if (!$first || !$last || !$email || !$password || !$role_id) {
        $errors[] = "All fields are required.";
    }

    if (!$email) {
        $errors[] = "Invalid email address.";
    }

    if (empty($errors)) {
        // Check for existing email
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already exists.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user with selected role
        $stmt = $mysqli->prepare("INSERT INTO users (role_id, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $errors[] = "Database error: " . $mysqli->error;
        } else {
            $stmt->bind_param("issss", $role_id, $first, $last, $email, $hash);
            if ($stmt->execute()) {
                header('Location: index.php');
                exit;
            } else {
                $errors[] = $stmt->error;
            }
            $stmt->close();
        }
    }
}

require 'header.php';
?>

<div class="form-row">
  <h2>Add User (Admin)</h2>

  <?php if (!empty($errors)): ?>
    <div class="notice">
      <ul>
        <?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <label>First name</label>
    <input name="first_name" class="input" required>

    <label>Last name</label>
    <input name="last_name" class="input" required>

    <label>Email</label>
    <input name="email" class="input" type="email" required>

    <label>Password</label>
    <input name="password" class="input" type="password" required>

    <label>Role</label>
    <select name="role_id" class="input" required>
      <option value="">Select role</option>
      <?php
      $roles = $mysqli->query("SELECT id, role_name FROM roles ORDER BY role_name ASC");
      if ($roles && $roles->num_rows > 0) {
          while ($row = $roles->fetch_assoc()) {
              echo '<option value="' . (int)$row['id'] . '">' . e($row['role_name']) . '</option>';
          }
      } else {
          echo '<option value="">No roles available</option>';
      }
      ?>
    </select>

    <button class="btn" type="submit">Create</button>
  </form>
</div>

<?php require 'footer.php'; ?>
