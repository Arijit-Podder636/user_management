<?php
// profile.php
require 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token check
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF token";
    } else {
        // Handle full name & phone update
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Validation
        if (!preg_match("/^[a-zA-Z ]+$/", $full_name)) {
            $errors[] = "Full name can only contain letters and spaces.";
        }
        if (!preg_match("/^[0-9]{10}$/", $phone)) {
            $errors[] = "Phone number must be 10 digits.";
        }

        if (empty($errors)) {
            if (updateProfile($full_name, $phone)) {
                $success = "Profile updated successfully.";
                $user = getCurrentUser(); // Refresh data
            } else {
                $errors[] = "Database error while updating profile.";
            }
        }

        // Update password
        if (!empty($_POST['new_password'])) {
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_new_password'] ?? '';
            if (strlen($new) < 6) {
                $errors[] = "Password must be at least 6 characters";
            } elseif ($new !== $confirm) {
                $errors[] = "Passwords do not match";
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hash, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $success .= $success ? " Password updated." : "Password updated.";
                } else {
                    $errors[] = "Database error while updating password.";
                }
                $stmt->close();
            }
        }

        // Handle profile pic upload
        if (!empty($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['profile_pic'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload error";
            } else {
                if ($file['size'] > 2 * 1024 * 1024) {
                    $errors[] = "File too large. Max 2MB.";
                } else {
                    $check = getimagesize($file['tmp_name']);
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
                    if (!$check || !isset($allowed[$check['mime']])) {
                        $errors[] = "Invalid image type. Only JPG & PNG allowed.";
                    } else {
                        $ext = $allowed[$check['mime']];
                        $newName = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                        $dest = __DIR__ . '/uploads/' . $newName;
                        if (!move_uploaded_file($file['tmp_name'], $dest)) {
                            $errors[] = "Could not save uploaded file.";
                        } else {
                            $oldpic = $user['profile_pic'];
                            if ($oldpic && file_exists(__DIR__ . '/uploads/' . $oldpic)) {
                                @unlink(__DIR__ . '/uploads/' . $oldpic);
                            }

                            $stmt = $mysqli->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                            $stmt->bind_param("si", $newName, $_SESSION['user_id']);
                            if ($stmt->execute()) {
                                $_SESSION['profile_pic'] = $newName;
                                $success .= $success ? " Profile picture updated." : "Profile picture updated.";
                                $user['profile_pic'] = $newName; // Refresh picture in session
                            } else {
                                $errors[] = "Database error while updating profile picture.";
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
}

require 'header.php';
?>

<div class="form-row">
    <h2>Edit Profile</h2>

    <?php if (!empty($errors)): ?>
        <div class="notice"><ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="notice"><?php echo e($success); ?></div>
    <?php endif; ?>

    <p><strong>Full Name:</strong> <?php echo e($user['full_name']); ?></p>
    <p><strong>Phone:</strong> <?php echo e($user['phone']); ?></p>
    <p><strong>Email:</strong> <?php echo e($user['email']); ?></p>

    <h3>Edit Profile Info</h3>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo e($user['full_name']); ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo e($user['phone']); ?>" required>

        <button class="btn" type="submit">Update Info</button>
    </form>

    <h3>Change Password</h3>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
        <label>New password</label>
        <input type="password" name="new_password" class="input">
        <label>Confirm new password</label>
        <input type="password" name="confirm_new_password" class="input">
        <button class="btn" type="submit">Update Password</button>
    </form>

    <h3>Upload Profile Picture (max 2MB, jpg/png)</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
        <input type="file" name="profile_pic" accept="image/png, image/jpeg">
        <button class="btn" type="submit">Upload</button>
    </form>

    <?php if ($user['profile_pic']): ?>
        <h4>Current Picture</h4>
        <img src="uploads/<?php echo e($user['profile_pic']); ?>" class="profile-pic" alt="profile">
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
