<?php
require 'functions.php';

// START: New code added here
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Set the page context for the header
$page_context = 'login';
// END: New code added here

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = "Email and password are required";
    } else {
        // Get user data including role
        $stmt = $mysqli->prepare("
            SELECT u.id, u.first_name, u.last_name, u.email, u.password, u.role_id, u.profile_pic, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['role_id']    = $user['role_id'];
            $_SESSION['role_name']  = $user['role_name']; // important for admin check
            $_SESSION['profile_pic'] = $user['profile_pic'];

            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Invalid email or password";
        }
        $stmt->close();
    }
}

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Add this line to identify the page
$page_context = 'login';

require 'header.php';
?>

<div class="form-row">
    <h2>Login</h2>

    <?php if (!empty($_GET['registered'])): ?>
        <div class="notice">Registration successful. Please login.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="notice">
            <ul><?php foreach ($errors as $err) echo "<li>" . e($err) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Email</label>
        <input type="email" name="email" class="input" value="<?= e($_POST['email'] ?? '') ?>" required>
        <label>Password</label>
        <input type="password" name="password" class="input" required>
        <button type="submit" class="btn">Login</button>
    </form>
</div>

<?php require 'footer.php'; ?>