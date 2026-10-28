<?php
include "../includes/auth.php";
include "../config/db.php";
requireLogin();

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "success";

if (isset($_POST['join_group_id'])) {
    $group_id = (int)$_POST['join_group_id'];

    $check_stmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $group_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows == 0) {
        $join_stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $join_stmt->bind_param("ii", $group_id, $user_id);
        if ($join_stmt->execute()) {
            $message = "Successfully joined the group!";
        } else {
            $message = "Error joining group.";
            $message_type = "danger";
        }
    } else {
        $message = "You are already a member of this group.";
        $message_type = "warning";
    }
}

$groups_result = $conn->query("SELECT id, name FROM study_groups ORDER BY name");

$page_title = "Study Groups";
include "../includes/header.php";
?>

<div class="container mt-4">
  <h2>Available Study Groups</h2>

  <?php if ($message != "") { ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
  <?php } ?>

  <p>
    <a href="create_group.php" class="btn btn-primary">Create a New Study Group</a>
    <a href="schedule_session.php" class="btn btn-outline-primary">Schedule a Study Session</a>
  </p>

  <table class="table table-bordered table-hover bg-white">
    <thead>
      <tr><th>Group Name</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php if ($groups_result->num_rows == 0) { ?>
        <tr><td colspan="2" class="text-center">No study groups yet.</td></tr>
      <?php } ?>
      <?php while ($group = $groups_result->fetch_assoc()) { ?>
        <tr>
          <td><?php echo htmlspecialchars($group['name']); ?></td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="join_group_id" value="<?php echo $group['id']; ?>">
              <button type="submit" class="btn btn-success btn-sm">Join Group</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include "../includes/footer.php"; ?>