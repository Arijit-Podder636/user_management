<?php
require 'functions.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first    = sanitize($_POST['first_name'] ?? '');
    $last     = sanitize($_POST['last_name'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone    = sanitize($_POST['phone'] ?? '');
    $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validation
    if (!$first) $errors[] = "First name required";
    if (!$last) $errors[] = "Last name required";
    if (!$full_name) {
        $errors[] = "Full name required";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $full_name)) {
        $errors[] = "Full name can only contain letters and spaces.";
    }

    if (!$phone) {
        $errors[] = "Phone number required";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (!$email) $errors[] = "Valid email required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm) $errors[] = "Passwords do not match";

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Get role_id for 'User'
        $role_name = 'User'; // default role
        $stmt = $mysqli->prepare("SELECT id FROM roles WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $stmt->bind_result($role_id);
        $stmt->fetch();
        $stmt->close();

        if (!$role_id) {
            $role_id = 2; // fallback role ID if not found
        }

        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into database
        $stmt = $mysqli->prepare("INSERT INTO users (role_id, first_name, last_name, full_name, phone, email, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issssss", $role_id, $first, $last, $full_name, $phone, $email, $hash);

        if ($stmt->execute()) {
            header('Location: login.php?registered=1');
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Add this line to identify the page
$page_context = 'register';

require 'header.php';
?>

<div class="form-row">
    <h2>Register</h2>

    <?php if (!empty($errors)): ?>
        <div class="notice">
            <ul><?php foreach ($errors as $err) echo "<li>" . e($err) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <label>First Name</label>
        <input type="text" name="first_name" class="input" value="<?= e($_POST['first_name'] ?? '') ?>" required>

        <label>Last Name</label>
        <input type="text" name="last_name" class="input" value="<?= e($_POST['last_name'] ?? '') ?>" required>

        <label>Full Name</label>
        <input type="text" name="full_name" class="input" value="<?= e($_POST['full_name'] ?? '') ?>" required pattern="[a-zA-Z ]+" title="Only letters and spaces allowed">

        <label>Phone</label>
        <input type="text" name="phone" class="input" value="<?= e($_POST['phone'] ?? '') ?>" required pattern="\d{10}" title="Enter exactly 10 digits">

        <label>Email</label>
        <input type="email" name="email" class="input" value="<?= e($_POST['email'] ?? '') ?>" required>

        <label>Password</label>
        <input type="password" name="password" class="input" required minlength="6">

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="input" required minlength="6">

        <button class="btn" type="submit">Register</button>
    </form>
</div>

<?php require 'footer.php'; ?>
