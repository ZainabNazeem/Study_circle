<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    // CREATE
    if ($action === 'add') {
        if ($name === '') {
            $message = "Subject name cannot be empty.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO subjects (name) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "s", $name);
            mysqli_stmt_execute($stmt);
            $message = "Subject added.";
        }
    }

    // UPDATE
    if ($action === 'update') {
        if ($name === '') {
            $message = "Subject name cannot be empty.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE subjects SET name=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "si", $name, $id);
            mysqli_stmt_execute($stmt);
            $message = "Subject updated.";
        }
    }

    // DELETE
    if ($action === 'delete') {
        $stmt = mysqli_prepare($conn, "DELETE FROM subjects WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $message = "Subject deleted.";
    }
}

// If ?edit=ID is in the URL, load that subject into the edit form
$editSubject = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT id, name FROM subjects WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $editSubject = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// READ
$subjects = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Subjects</title>
</head>
<body>
  <h2>Manage Subjects</h2>
  <p><a href="dashboard.php">Back to Dashboard</a> | <a href="users.php">Manage Users</a></p>

  <?php if ($message): ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
  <?php endif; ?>

  <?php if ($editSubject): ?>
    <h3>Edit subject</h3>
    <form method="post" action="subjects.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?php echo (int)$editSubject['id']; ?>">
      <input type="text" name="name" value="<?php echo htmlspecialchars($editSubject['name']); ?>" required>
      <button type="submit">Save</button>
      <a href="subjects.php">Cancel</a>
    </form>
  <?php else: ?>
    <h3>Add subject</h3>
    <form method="post" action="subjects.php">
      <input type="hidden" name="action" value="add">
      <input type="text" name="name" placeholder="e.g. Web Development" required>
      <button type="submit">Add</button>
    </form>
  <?php endif; ?>
  <hr>

  <table border="1" cellpadding="6" cellspacing="0">
    <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
    <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
      <tr>
        <td><?php echo (int)$s['id']; ?></td>
        <td><?php echo htmlspecialchars($s['name']); ?></td>
        <td>
          <a href="subjects.php?edit=<?php echo (int)$s['id']; ?>">Edit</a>
          <form method="post" action="subjects.php" style="display:inline"
                onsubmit="return confirm('Delete this subject? Notes and questions under it will also be deleted.');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
