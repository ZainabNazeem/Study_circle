<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle joining a group
if (isset($_POST['join_group_id'])) {
    $group_id = $_POST['join_group_id'];
    
    // Check if already joined
    $check_stmt = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $group_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        $join_stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $join_stmt->bind_param("ii", $group_id, $user_id);
        if ($join_stmt->execute()) {
            $message = "<div style='color: green;'>Successfully joined the group!</div>";
        } else {
            $message = "<div style='color: red;'>Error joining group.</div>";
        }
    } else {
        $message = "<div style='color: orange;'>You are already a member of this group.</div>";
    }
}

// Fetch all study groups
$groups_result = $conn->query("SELECT * FROM study_groups");
?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Groups</title>
</head>
<body>
    <h2>Available Study Groups</h2>
    <?php echo $message; ?>
    
    <p>
        <a href="create_group.php">Create a New Study Group</a> |
        <a href="schedule_session.php">Schedule a Study Session</a>
    </p>    
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Group Name</th>
            <th>Action</th>
        </tr>
        <?php while ($group = $groups_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($group['name']); ?></td></td>
            <td>
                <form action="groups.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="join_group_id" value="<?php echo $group['id']; ?>">
                    <button type="submit">Join Group</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>