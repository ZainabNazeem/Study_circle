<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = trim($_POST['group_name']);
    $user_id = $_SESSION['user_id'];

    if (!empty($group_name)) {
        $stmt = $conn->prepare("INSERT INTO StudyGroups (group_name, creator_user_id) VALUES (?, ?)");
        $stmt->bind_param("si", $group_name, $user_id);
        
        if ($stmt->execute()) {
            $message = "<div style='color: green;'>Study Group created successfully!</div>";
        } else {
            $message = "<div style='color: red;'>Error creating group.</div>";
        }
    } else {
        $message = "<div style='color: red;'>Please enter a group name.</div>";
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Study Group</title>
</head>
<body>
    <h2>Create a New Study Group</h2>
    <?php echo $message; ?>
    
    <form action="create_group.php" method="POST">
        <label for="group_name">Group Name:</label><br>
        <input type="text" id="group_name" name="group_name" required><br><br>
        
        <button type="submit">Create Group</button>
    </form>
    <br>
    <a href="groups.php">View All Groups</a>
</body>
</html>