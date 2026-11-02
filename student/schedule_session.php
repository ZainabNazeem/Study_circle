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
    $meeting_link = trim($_POST['meeting_link']);

    if ($group_id > 0 && $session_title != "" && $session_date != "" && $session_time != "" && $meeting_link != "") {

        // Link must start with http:// or https://
        if (!filter_var($meeting_link, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $meeting_link)) {
            $message = "Please enter a valid meeting link (starting with https://).";
            $message_type = "danger";
        } else {
            $stmt = $conn->prepare("INSERT INTO study_sessions (group_id, topic, session_date, session_time, meeting_link) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $group_id, $session_title, $session_date, $session_time, $meeting_link);

            if ($stmt->execute()) {
                $message = "Study session scheduled successfully!";
            } else {
                $message = "Error scheduling session.";
                $message_type = "danger";
            }
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

      <div class="mb-3">
        <label class="form-label">Meeting Link (Zoom / Google Meet)</label>
        <input type="url" name="meeting_link" id="meeting_link" class="form-control" placeholder="https://zoom.us/j/123456789" required>
        <button type="button" class="btn btn-sm mt-2 text-white" style="background-color:#2D8CFF; border-color:#2D8CFF;" onclick="joinMeeting()">Join Meeting</button>
        <small class="text-muted d-block mt-1">Create the meeting in Zoom or Google Meet, then paste the link here.</small>
      </div>

      <button type="submit" class="btn btn-primary">Schedule Session</button>
      <a href="groups.php" class="btn btn-outline-secondary">Back to Groups</a>
    </form>
  </div>

  <h3>Scheduled Sessions</h3>
  <table class="table table-bordered table-hover bg-white">
    <thead>
      <tr><th>Group</th><th>Title</th><th>Date</th><th>Time</th><th>Link</th></tr>
    </thead>
    <tbody>
      <?php if ($sessions_result->num_rows == 0) { ?>
        <tr><td colspan="5" class="text-center">No sessions scheduled yet.</td></tr>
      <?php } ?>
      <?php while ($session = $sessions_result->fetch_assoc()) { ?>
        <tr>
          <td><?php echo htmlspecialchars($session['group_name']); ?></td>
          <td><?php echo htmlspecialchars($session['topic']); ?></td>
          <td><?php echo $session['session_date']; ?></td>
          <td><?php echo substr($session['session_time'], 0, 5); ?></td>
          <td>
            <?php if (!empty($session['meeting_link'])) { ?>
              <a href="<?php echo htmlspecialchars($session['meeting_link']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success">Join</a>
            <?php } else { ?>
              -
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
// Do not allow dates in the past
document.getElementById("session_date").min = new Date().toISOString().split("T")[0];

// Open the pasted meeting link in a new tab
function joinMeeting() {
  var link = document.getElementById("meeting_link").value.trim();
  if (/^https?:\/\//i.test(link)) {
    window.open(link, "_blank", "noopener");
  } else {
    alert("Please paste a valid link starting with https://");
  }
}
</script>

<?php include "../includes/footer.php"; ?>