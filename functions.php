<?php
require_once __DIR__ . '/config.php';

/**
 * Sanitize output to prevent XSS
 */
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Trim input for sanitization
 */
function sanitize($s) {
    return trim($s);
}

/**
 * Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get user's role name from role_id
 */
function getUserRoleName($role_id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT role_name FROM roles WHERE id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
    return $name ?? null;
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    // Prefer role name in session (case-insensitive)
    if (isset($_SESSION['role']) && is_string($_SESSION['role'])) {
        if (strtolower(trim($_SESSION['role'])) === 'admin') {
            return true;
        }
    }

    // Fallback: if role_id is stored in session, verify against DB
    if (isset($_SESSION['role_id']) && is_numeric($_SESSION['role_id'])) {
        global $mysqli;
        $rid = intval($_SESSION['role_id']);
        $stmt = $mysqli->prepare("SELECT role_name FROM roles WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $rid);
            $stmt->execute();
            $stmt->bind_result($rname);
            $stmt->fetch();
            $stmt->close();
            if ($rname && strtolower(trim($rname)) === 'admin') {
                return true;
            }
        }
    }

    return false;
}



/**
 * Generate CSRF token
 */
function getToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Check CSRF token validity
 */
function checkToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $mysqli;
    if (!isLoggedIn()) return null;

    $uid = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT u.id, u.first_name, u.last_name, u.full_name, u.phone, u.email, u.profile_pic, r.role_name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // If full_name is empty, create it from first and last names
    if (empty($user['full_name'])) {
        $user['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    }

    return $user;
}


/**
 * Update current user's profile info
 */
function updateProfile($full_name, $phone) {
    global $mysqli;
    if (!isLoggedIn()) return false;

    $uid = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $full_name, $phone, $uid);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Add a new user (Admin function)
 */
function addUser($first, $last, $email, $password, $role_id) {
    global $mysqli;

    // Sanitize inputs
    $first = sanitize($first);
    $last = sanitize($last);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $password = trim($password);
    $role_id = intval($role_id);

    $errors = [];

    // Basic validation
    if (!$first || !$last || !$email || !$password || !$role_id) {
        $errors[] = "All fields are required.";
    }

    if (!$email) {
        $errors[] = "Invalid email address.";
    }

    // Check if email already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists.";
    }
    $stmt->close();

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $mysqli->prepare("INSERT INTO users (role_id, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        return ['success' => false, 'errors' => ["Prepare failed: " . $mysqli->error]];
    }

    $stmt->bind_param("issss", $role_id, $first, $last, $email, $hash);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true];
    } else {
        $errors[] = $stmt->error;
        $stmt->close();
        return ['success' => false, 'errors' => $errors];
    }
}


?>
