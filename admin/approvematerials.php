<?php

include '../config/db.php';
include '../includes/auth.php';
requireAdmin();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id      = (int)$_POST['material_id'];
    $action  = $_POST['action'];
    $current = $_POST['current'] ?? 'pending';

    if ($action == 'approved' || $action == 'rejected') {
        $stmt = $conn->prepare("UPDATE materials SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $id);
        $stmt->execute();
    }
    header("Location: approvematerials.php?status=" . urlencode($current));
    exit;
}

$allowed = ['pending', 'approved', 'rejected'];
$status  = $_GET['status'] ?? 'pending';
if (!in_array($status, $allowed)) {
    $status = 'pending';
}
$stmt = $conn->prepare(
    "SELECT m.id, m.title, m.file_name, m.created_at,
            s.name AS subject, u.full_name AS uploader
     FROM materials m
     JOIN subjects s ON m.subject_id = s.id
     JOIN users u ON m.user_id = u.id
     WHERE m.status = ?
     ORDER BY m.created_at DESC"
);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();

$page_title = "Approve Materials";
include '../includes/header.php';
?>

<div class="container mt-4">
  <h2>Manage Study Materials</h2>

  <ul class="nav nav-tabs mb-3">
    <?php foreach ($allowed as $tab) { ?>
      <li class="nav-item">
        <a class="nav-link <?php echo $status == $tab ? 'active' : ''; ?>"
           href="?status=<?php echo $tab; ?>"><?php echo ucfirst($tab); ?></a>
      </li>
    <?php } ?>
  </ul>

  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <th>Title</th><th>Subject</th><th>Uploaded by</th>
        <th>Date</th><th>File</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows == 0) { ?>
        <tr><td colspan="6" class="text-center">No <?php echo $status; ?> materials.</td></tr>
      <?php } ?>

      <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td><?php echo htmlspecialchars($row['title']); ?></td>
          <td><?php echo htmlspecialchars($row['subject']); ?></td>
          <td><?php echo htmlspecialchars($row['uploader']); ?></td>
          <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
          <td>
            <a href="/studycircle/uploads/<?php echo urlencode($row['file_name']); ?>"
               target="_blank" class="btn btn-outline-secondary btn-sm">View</a>
          </td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="material_id" value="<?php echo $row['id']; ?>">
              <input type="hidden" name="current" value="<?php echo $status; ?>">
              <?php if ($status != 'approved') { ?>
                <button name="action" value="approved" class="btn btn-success btn-sm">Approve</button>
              <?php } ?>
              <?php if ($status != 'rejected') { ?>
                <button name="action" value="rejected" class="btn btn-danger btn-sm" onclick="return confirm('Reject this material?');">Reject</button>
              <?php } ?>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>