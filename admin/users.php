<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

$message = "";
$me = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    // UPDATE
    if ($action === 'update') {
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'student';
        $status = $_POST['status'] ?? 'active';

        if ($full_name === '' || !in_array($role, ['admin', 'student']) || !in_array($status, ['active', 'blocked'])) {
            $message = "Invalid data.";
        } elseif ($id === $me && ($role !== 'admin' || $status !== 'active')) {
            $message = "You cannot demote or block yourself.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET full_name=?, role=?, status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $full_name, $role, $status, $id);
            mysqli_stmt_execute($stmt);
            $message = "User updated.";
        }
    }

    // DELETE
    if ($action === 'delete') {
        if ($id === $me) {
            $message = "You cannot delete your own account.";
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $message = "User deleted.";
        }
    }
}

// If ?edit=ID is in the URL, load that user into the edit form
$editUser = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, role, status FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $editUser = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// READ
$users = mysqli_query($conn, "SELECT id, full_name, email, role, status, created_at FROM users ORDER BY id");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Users</title>
</head>
<body>
  <h2>Manage Users</h2>
  <p><a href="dashboard.php">Back to Dashboard</a> | <a href="subjects.php">Manage Subjects</a></p>

  <?php if ($message): ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
  <?php endif; ?>

  <?php if ($editUser): ?>
    <h3>Edit user: <?php echo htmlspecialchars($editUser['email']); ?></h3>
    <form method="post" action="users.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?php echo (int)$editUser['id']; ?>">

      <label>Full name:
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($editUser['full_name']); ?>" required>
      </label><br><br>

      <label>Role:
        <select name="role">
          <option value="student" <?php if ($editUser['role'] === 'student') echo 'selected'; ?>>student</option>
          <option value="admin" <?php if ($editUser['role'] === 'admin') echo 'selected'; ?>>admin</option>
        </select>
      </label><br><br>

      <label>Status:
        <select name="status">
          <option value="active" <?php if ($editUser['status'] === 'active') echo 'selected'; ?>>active</option>
          <option value="blocked" <?php if ($editUser['status'] === 'blocked') echo 'selected'; ?>>blocked</option>
        </select>
      </label><br><br>

      <button type="submit">Save</button>
      <a href="users.php">Cancel</a>
    </form>
    <hr>
  <?php endif; ?>

  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
    </tr>
    <?php while ($u = mysqli_fetch_assoc($users)): ?>
      <tr>
        <td><?php echo (int)$u['id']; ?></td>
        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
        <td><?php echo htmlspecialchars($u['email']); ?></td>
        <td><?php echo htmlspecialchars($u['role']); ?></td>
        <td><?php echo htmlspecialchars($u['status']); ?></td>
        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
        <td>
          <a href="users.php?edit=<?php echo (int)$u['id']; ?>">Edit</a>
          <?php if ((int)$u['id'] !== $me): ?>
            <form method="post" action="users.php" style="display:inline"
                  onsubmit="return confirm('Delete this user and all their uploads, questions and answers?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
              <button type="submit">Delete</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
