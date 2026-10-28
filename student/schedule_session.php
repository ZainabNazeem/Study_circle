<?php
include "../includes/auth.php";
include "../config/db.php";
requireLogin();

$message = "";
$message_type = "success";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = (int)$_POST['group_id'];
    $session_title = trim($_POST['session_title']);
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];

    if ($group_id > 0 && $session_title != "" && $session_date != "" && $session_time != "") {
        $stmt = $conn->prepare("INSERT INTO study_sessions (group_id, topic, session_date, session_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $group_id, $session_title, $session_date, $session_time);

        if ($stmt->execute()) {
            $message = "Study session scheduled successfully!";
        } else {
            $message = "Error scheduling session.";
            $message_type = "danger";
        }
    } else {
        $message = "Please fill in all fields.";
        $message_type = "danger";
    }
}

$groups_result = $conn->query("SELECT id, name FROM study_groups ORDER BY name");

$sessions_result = $conn->query("SELECT s.*, g.name AS group_name FROM study_sessions s JOIN study_groups g ON s.group_id = g.id ORDER BY s.session_date ASC, s.session_time ASC");

$page_title = "Schedule Session";
include "../includes/header.php";
?>

<div class="container mt-4">
  <h2>Schedule a Study Session</h2>

  <?php if ($message != "") { ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
  <?php } ?>

  <div class="card p-4 mb-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Select group</label>
        <select name="group_id" class="form-select" required>
          <option value="">-- Choose Group --</option>
          <?php while ($group = $groups_result->fetch_assoc()) { ?>
            <option value="<?php echo $group['id']; ?>"><?php echo htmlspecialchars($group['name']); ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Session title</label>
        <input type="text" name="session_title" class="form-control" required>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Date</label>
          <input type="date" name="session_date" id="session_date" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Time</label>
          <input type="time" name="session_time" class="form-control" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Schedule Session</button>
      <a href="groups.php" class="btn btn-outline-secondary">Back to Groups</a>
    </form>
  </div>

  <h3>Scheduled Sessions</h3>
  <table class="table table-bordered table-hover bg-white">
    <thead>
      <tr><th>Group</th><th>Title</th><th>Date</th><th>Time</th></tr>
    </thead>
    <tbody>
      <?php if ($sessions_result->num_rows == 0) { ?>
        <tr><td colspan="4" class="text-center">No sessions scheduled yet.</td></tr>
      <?php } ?>
      <?php while ($session = $sessions_result->fetch_assoc()) { ?>
        <tr>
          <td><?php echo htmlspecialchars($session['group_name']); ?></td>
          <td><?php echo htmlspecialchars($session['topic']); ?></td>
          <td><?php echo $session['session_date']; ?></td>
          <td><?php echo substr($session['session_time'], 0, 5); ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
// Do not allow dates in the past
document.getElementById("session_date").min = new Date().toISOString().split("T")[0];
</script>

<?php include "../includes/footer.php"; ?>