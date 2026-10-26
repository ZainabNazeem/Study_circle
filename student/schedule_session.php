<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'];
    $session_title = trim($_POST['session_title']);
    $session_date = $_POST['session_date'];

    if (!empty($group_id) && !empty($session_title) && !empty($session_date)) {
        $stmt = $conn->prepare("INSERT INTO study_sessions (group_id, session_title, session_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $group_id, $session_title, $session_date);
        
        if ($stmt->execute()) {
            $message = "<div style='color: green;'>Study Session scheduled successfully!</div>";
        } else {
            $message = "<div style='color: red;'>Error scheduling session.</div>";
        }
    } else {
        $message = "<div style='color: red;'>Please fill in all fields.</div>";
    }
}

// Fetch groups
$groups_result = $conn->query("SELECT * FROM studygroups");

// Fetch scheduled sessions
$sessions_result = $conn->query("SELECT s.*, g.group_name FROM study_sessions s JOIN studygroups g ON s.group_id = g.id ORDER BY s.session_date ASC");
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Study Session</title>
</head>
<body>
    <h2>Schedule a Study Session</h2>
    <?php echo $message; ?>
    
    <form action="schedule_session.php" method="POST">
        <label for="group_id">Select Group:</label><br>
        <select name="group_id" id="group_id" required>
            <option value="">-- Choose Group --</option>
            <?php while ($group = $groups_result->fetch_assoc()): ?>
                <option value="<?php echo $group['id']; ?>"><?php echo htmlspecialchars($group['group_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="session_title">Session Title:</label><br>
        <input type="text" id="session_title" name="session_title" required><br><br>

        <label for="session_date">Date & Time:</label><br>
        <input type="datetime-local" id="session_date" name="session_date" required><br><br>

        <button type="submit">Schedule Session</button>
    </form>

    <br><hr><br>

    <h3>Scheduled Sessions</h3>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Group</th>
            <th>Title</th>
            <th>Date & Time</th>
        </tr>
        <?php while ($session = $sessions_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($session['group_name']); ?></td>
            <td><?php echo htmlspecialchars($session['session_title']); ?></td>
            <td><?php echo $session['session_date']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="groups.php">Back to Groups</a>
</body>
</html>