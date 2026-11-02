<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

$message = "";
$messageType = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    // UPDATE: toggle read / new
    if ($action === 'toggle') {
        $stmt = mysqli_prepare($conn, "UPDATE contact_messages SET status = IF(status='new','read','new') WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $message = "Message status updated.";
    }

    // DELETE
    if ($action === 'delete') {
        $stmt = mysqli_prepare($conn, "DELETE FROM contact_messages WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $message = "Message deleted.";
    }
}

// VIEW one message (opening it marks it as read)
$viewMsg = null;
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, subject, message, status, created_at FROM contact_messages WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $viewId);
    mysqli_stmt_execute($stmt);
    $viewMsg = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($viewMsg && $viewMsg['status'] === 'new') {
        $stmt = mysqli_prepare($conn, "UPDATE contact_messages SET status='read' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $viewId);
        mysqli_stmt_execute($stmt);
        $viewMsg['status'] = 'read';
    }
}

// SEARCH + FILTER
$q = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT id, name, email, subject, message, status, created_at FROM contact_messages WHERE 1=1";
$types = "";
$params = [];

if ($q !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $like = "%" . $q . "%";
    $types .= "sss";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if (in_array($statusFilter, ['new', 'read'])) {
    $sql .= " AND status=?";
    $types .= "s";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$messages = mysqli_stmt_get_result($stmt);

// Count of unread messages
$newCountRes = mysqli_query($conn, "SELECT COUNT(*) FROM contact_messages WHERE status='new'");
$newCount = (int)mysqli_fetch_row($newCountRes)[0];

$page_title = "Contact Messages - StudyCircle";
include "../includes/header.php";
?>
<style>
  .admin-card { border-top: 4px solid var(--nsbm-green); }
</style>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Contact Messages
      <?php if ($newCount > 0): ?>
        <span class="badge bg-danger fs-6"><?php echo $newCount; ?> new</span>
      <?php endif; ?>
    </h2>
    <div>
      <a href="users.php" class="btn btn-outline-primary btn-sm">Manage Users</a>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if ($viewMsg): ?>
    <div class="card admin-card mb-4">
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($viewMsg['subject']); ?></h5>
        <p class="text-muted mb-2">
          From <strong><?php echo htmlspecialchars($viewMsg['name']); ?></strong>
          (<a href="mailto:<?php echo htmlspecialchars($viewMsg['email']); ?>"><?php echo htmlspecialchars($viewMsg['email']); ?></a>)
          &middot; <?php echo htmlspecialchars($viewMsg['created_at']); ?>
        </p>
        <p><?php echo nl2br(htmlspecialchars($viewMsg['message'])); ?></p>
        <a href="messages.php" class="btn btn-outline-secondary btn-sm">Close</a>
        <a href="mailto:<?php echo htmlspecialchars($viewMsg['email']); ?>?subject=Re: <?php echo rawurlencode($viewMsg['subject']); ?>"
           class="btn btn-primary btn-sm">Reply by email</a>
      </div>
    </div>
  <?php endif; ?>

  <!-- Search and filter -->
  <div class="card admin-card mb-4">
    <div class="card-body">
      <form method="get" action="messages.php" class="row g-2">
        <div class="col-md-6">
          <input type="text" name="q" class="form-control" placeholder="Search name, email or subject..."
                 value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">All status</option>
            <option value="new" <?php if ($statusFilter === 'new') echo 'selected'; ?>>new</option>
            <option value="read" <?php if ($statusFilter === 'read') echo 'selected'; ?>>read</option>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary">Search</button>
          <a href="messages.php" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Messages table -->
  <div class="card admin-card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th><th>From</th><th>Subject</th><th>Status</th><th>Received</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($m = mysqli_fetch_assoc($messages)): ?>
              <tr>
                <td><?php echo (int)$m['id']; ?></td>
                <td>
                  <?php echo htmlspecialchars($m['name']); ?><br>
                  <small class="text-muted"><?php echo htmlspecialchars($m['email']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($m['subject']); ?></td>
                <td>
                  <span class="badge <?php echo $m['status'] === 'new' ? 'bg-danger' : 'bg-secondary'; ?>">
                    <?php echo htmlspecialchars($m['status']); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($m['created_at']); ?></td>
                <td>
                  <a href="messages.php?view=<?php echo (int)$m['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                  <form method="post" action="messages.php" class="d-inline">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                      <?php echo $m['status'] === 'new' ? 'Mark read' : 'Mark new'; ?>
                    </button>
                  </form>
                  <form method="post" action="messages.php" class="d-inline"
                        onsubmit="return confirm('Delete this message?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($messages) === 0): ?>
              <tr><td colspan="6" class="text-center text-muted">No messages found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
