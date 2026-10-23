<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

$message = "";
$messageType = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    // CREATE
    if ($action === 'add') {
        if ($name === '') {
            $message = "Subject name cannot be empty.";
            $messageType = "danger";
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
            $messageType = "danger";
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

$page_title = "Manage Subjects - StudyCircle";
include "../includes/header.php";
?>
<style>
  .admin-card { border-top: 4px solid var(--nsbm-green); }
</style>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Manage Subjects</h2>
    <div>
      <a href="users.php" class="btn btn-outline-primary btn-sm">Manage Users</a>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <div class="card admin-card mb-4">
    <div class="card-body">
      <?php if ($editSubject): ?>
        <h5 class="card-title">Edit subject</h5>
        <form method="post" action="subjects.php" class="row g-2">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?php echo (int)$editSubject['id']; ?>">
          <div class="col-md-8">
            <input type="text" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($editSubject['name']); ?>" required>
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="subjects.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      <?php else: ?>
        <h5 class="card-title">Add subject</h5>
        <form method="post" action="subjects.php" class="row g-2">
          <input type="hidden" name="action" value="add">
          <div class="col-md-8">
            <input type="text" name="name" class="form-control" placeholder="e.g. Web Development" required>
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Add</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="card admin-card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
              <tr>
                <td><?php echo (int)$s['id']; ?></td>
                <td><?php echo htmlspecialchars($s['name']); ?></td>
                <td>
                  <a href="subjects.php?edit=<?php echo (int)$s['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <form method="post" action="subjects.php" class="d-inline"
                        onsubmit="return confirm('Delete this subject? Notes and questions under it will also be deleted.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
