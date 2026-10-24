<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

$message = "";
$messageType = "success";
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
            $messageType = "danger";
        } elseif ($id === $me && ($role !== 'admin' || $status !== 'active')) {
            $message = "You cannot demote or block yourself.";
            $messageType = "danger";
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
            $messageType = "danger";
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
// SEARCH + FILTER
$q = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT id, full_name, email, role, status, created_at FROM users WHERE 1=1";
$types = "";
$params = [];

if ($q !== '') {
    $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
    $like = "%" . $q . "%";
    $types .= "ss";
    $params[] = $like;
    $params[] = $like;
}
if (in_array($roleFilter, ['admin', 'student'])) {
    $sql .= " AND role=?";
    $types .= "s";
    $params[] = $roleFilter;
}
if (in_array($statusFilter, ['active', 'blocked'])) {
    $sql .= " AND status=?";
    $types .= "s";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY id";

$stmt = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$users = mysqli_stmt_get_result($stmt);

$page_title = "Manage Users - StudyCircle";
include "../includes/header.php";
?>
<style>
  .admin-card { border-top: 4px solid var(--nsbm-green); }
</style>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Manage Users</h2>
    <div>
      <a href="subjects.php" class="btn btn-outline-primary btn-sm">Manage Subjects</a>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if ($editUser): ?>
    <div class="card admin-card mb-4">
      <div class="card-body">
        <h5 class="card-title">Edit user: <?php echo htmlspecialchars($editUser['email']); ?></h5>
        <form method="post" action="users.php">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?php echo (int)$editUser['id']; ?>">

          <div class="mb-3">
            <label class="form-label">Full name</label>
            <input type="text" name="full_name" class="form-control"
                   value="<?php echo htmlspecialchars($editUser['full_name']); ?>" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Role</label>
              <select name="role" class="form-select">
                <option value="student" <?php if ($editUser['role'] === 'student') echo 'selected'; ?>>student</option>
                <option value="admin" <?php if ($editUser['role'] === 'admin') echo 'selected'; ?>>admin</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="active" <?php if ($editUser['status'] === 'active') echo 'selected'; ?>>active</option>
                <option value="blocked" <?php if ($editUser['status'] === 'blocked') echo 'selected'; ?>>blocked</option>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Save</button>
          <a href="users.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="card admin-card mb-4">
    <div class="card-body">
      <form method="get" action="users.php" class="row g-2">
        <div class="col-md-5">
          <input type="text" name="q" class="form-control" placeholder="Search name or email..."
                 value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-md-2">
          <select name="role" class="form-select">
            <option value="">All roles</option>
            <option value="admin" <?php if ($roleFilter === 'admin') echo 'selected'; ?>>admin</option>
            <option value="student" <?php if ($roleFilter === 'student') echo 'selected'; ?>>student</option>
          </select>
        </div>
        <div class="col-md-2">
          <select name="status" class="form-select">
            <option value="">All status</option>
            <option value="active" <?php if ($statusFilter === 'active') echo 'selected'; ?>>active</option>
            <option value="blocked" <?php if ($statusFilter === 'blocked') echo 'selected'; ?>>blocked</option>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary">Search</button>
          <a href="users.php" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card admin-card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
              <tr>
                <td><?php echo (int)$u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                  <span class="badge <?php echo $u['role'] === 'admin' ? 'bg-primary' : 'bg-secondary'; ?>">
                    <?php echo htmlspecialchars($u['role']); ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?php echo $u['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                    <?php echo htmlspecialchars($u['status']); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                <td>
                  <a href="users.php?edit=<?php echo (int)$u['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <?php if ((int)$u['id'] !== $me): ?>
                    <form method="post" action="users.php" class="d-inline"
                          onsubmit="return confirm('Delete this user and all their uploads, questions and answers?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($users) === 0): ?>
              <tr><td colspan="7" class="text-center text-muted">No users found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
