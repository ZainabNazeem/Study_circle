<?php
include "../includes/auth.php";
include "../config/db.php";
requireLogin();

$message = "";
$message_type = "success";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = trim($_POST['group_name']);
    $user_id = $_SESSION['user_id'];

    if ($group_name != "") {
        $stmt = $conn->prepare("INSERT INTO study_groups (name, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $group_name, $user_id);

        if ($stmt->execute()) {
            // The creator joins their own group automatically
            $new_group_id = $conn->insert_id;
            $member = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $member->bind_param("ii", $new_group_id, $user_id);
            $member->execute();

            $message = "Study group created successfully!";
        } else {
            $message = "Error creating group.";
            $message_type = "danger";
        }
    } else {
        $message = "Please enter a group name.";
        $message_type = "danger";
    }
}

$page_title = "Create Group";
include "../includes/header.php";
?>

<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card p-4">
        <h3>Create a New Study Group</h3>

        <?php if ($message != "") { ?>
          <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php } ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Group name</label>
            <input type="text" name="group_name" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Create Group</button>
          <a href="groups.php" class="btn btn-outline-secondary">View All Groups</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>