<?php
// index.php
require 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require 'header.php';
?>



<!-- Dashboard Title -->
<div class="dashboard-title">
    <h2>Dashboard</h2>
</div>

<!-- User Table -->
<div class="container">
    <table>
        <thead>
            <tr>
                <th>Profile Picture</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isAdmin()) {
                $stmt = $mysqli->prepare("
                    SELECT u.id, u.first_name, u.last_name, u.email, u.profile_pic, r.role_name
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    ORDER BY u.id DESC
                ");
            } else {
                $stmt = $mysqli->prepare("
                    SELECT u.id, u.first_name, u.last_name, u.email, u.profile_pic, r.role_name
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.id = ?
                    LIMIT 1
                ");
                $stmt->bind_param('i', $_SESSION['user_id']);
            }

            $stmt->execute();
            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()):
            ?>
                <tr>
                    <td class="profile-pic-cell">
                        <div class="profile-pic-wrapper">
                            <img src="uploads/<?php echo e($row['profile_pic'] ?: 'default.png'); ?>"
                                 class="profile-pic" alt="profile"
                                 onclick="enlargeImage(this)">
                        </div>
                    </td>
                    <td><?php echo e($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e($row['role_name']); ?></td>
                    <td>
                        <a class="btn" href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a>
                        <?php if (isAdmin()): ?>
                            <form method="post" action="delete_user.php" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo getToken(); ?>">
                                <button type="submit" class="btn btn-delete">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; $stmt->close(); ?>
        </tbody>
    </table>
</div>

<!-- Image Modal -->
<div id="imgModal" class="modal" onclick="closeImage()">
    <span class="modal-close">&times;</span>
    <img id="modalImage" class="modal-content">
</div>

<script>
function enlargeImage(img) {
    const modal = document.getElementById('imgModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = 'block';
    modalImg.src = img.src;
}
function closeImage() {
    document.getElementById('imgModal').style.display = 'none';
}
</script>

<style>
/* Green Header Bar */
.main-header {
    background: #065f46;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.main-header h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}

.header-right a {
    color: #fff;
    text-decoration: none;
    margin-left: 18px;
    font-weight: 500;
    padding: 6px 12px;
    background: #0f766e;
    border-radius: 6px;
    transition: 0.2s;
}

.header-right a:hover {
    background: #115e59;
}

.logout {
    background: #b91c1c !important;
}

.dashboard-title {
    text-align: center;
    margin-top: 20px;
    font-size: 20px;
    color: #065f46;
    font-weight: bold;
}
</style>

<?php require 'footer.php'; ?>
