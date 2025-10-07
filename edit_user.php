<?php
require 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

// Only admin or owner can edit
if (!isAdmin() && $_SESSION['user_id'] != $id) {
    header('Location: index.php');
    exit;
}

// Fetch user safely
$stmt = $mysqli->prepare("SELECT id, role_id, first_name, last_name, email FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: index.php');
    exit;
}

// Fetch roles for admin
$roles = [];
if (isAdmin()) {
    $roleStmt = $mysqli->prepare("SELECT id, role_name FROM roles ORDER BY role_name ASC");
    $roleStmt->execute();
    $roleRes = $roleStmt->get_result();
    while ($row = $roleRes->fetch_assoc()) {
        $roles[] = $row;
    }
    $roleStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token");
    }

    $first = sanitize($_POST['first_name'] ?? '');
    $last = sanitize($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

    // Admin can change role
    $role_id = isAdmin() ? intval($_POST['role_id'] ?? $user['role_id']) : $user['role_id'];

    if (!$first || !$last || !$email) {
        $errors[] = "All fields are required";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE users SET role_id = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param("isssi", $role_id, $first, $last, $email, $id);

        if ($stmt->execute()) {
            // Update session if current user edited own profile
            if ($_SESSION['user_id'] == $id) {
                $_SESSION['first_name'] = $first;
                $_SESSION['email'] = $email;
            }
            header('Location: index.php?msg=User updated successfully');
            exit;
        } else {
            $errors[] = "Update failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

require 'header.php';
?>

<div class="form-row">
    <h2>Edit User</h2>

    <?php if (!empty($errors)): ?>
        <div class="notice">
            <ul><?php foreach ($errors as $err) echo "<li>" . e($err) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">

        <label>First Name</label>
        <input name="first_name" class="input" value="<?php echo e($user['first_name']); ?>" required>

        <label>Last Name</label>
        <input name="last_name" class="input" value="<?php echo e($user['last_name']); ?>" required>

        <label>Email</label>
        <input name="email" type="email" class="input" value="<?php echo e($user['email']); ?>" required>

        <?php if (isAdmin()): ?>
            <label>Role</label>
            <select name="role_id" class="input" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int)$role['id'] ?>" <?= ($role['id'] == $user['role_id']) ? 'selected' : '' ?>>
                        <?= e($role['role_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button class="btn" type="submit">Save Changes</button>
    </form>
</div>

<?php require 'footer.php'; ?>
