<?php
session_start();

// To Delete the question
if (isset($_GET['delete_question'])) {
    $q_id = $_GET['delete_question'];
    $sql = "DELETE FROM questions WHERE id = $q_id";
    // mysqli_query($conn, $sql);
    header("Location: moderate_qa.php");
}

// To delete the answer
if (isset($_GET['delete_answer'])) {
    $a_id = $_GET['delete_answer'];
    $sql = "DELETE FROM answers WHERE id = $a_id";
    // mysqli_query($conn, $sql);
    header("Location: moderate_qa.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Moderation - Q&A</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div style="padding: 20px;">
        <h2>Admin Moderation: Manage Questions & Answers</h2>

        <h3>Questions List</h3>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-bottom: 20px;">
            <tr style="background: #fefefe;">
                <th>ID</th>
                <th>Title</th>
                <th>Content</th>
                <th>Action</th>
            </tr>
            
            <tr>
                <td>1</td>
                <td>Sample Question?</td>
                <td>Sample content details...</td>
                <td>
                    <a href="moderate_qa.php?delete_question=1" onclick="return confirm('Are you sure you want to delete this question?');" style="color: red;">Delete</a>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>