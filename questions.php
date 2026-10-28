<?php
session_start();
// after submit the questions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_question'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'] ?? 1; 

    $sql = "INSERT INTO questions (user_id, title, content) VALUES ('$user_id', '$title', '$content')";
    // mysqli_query($conn, $sql); 
}

// after submit the answers
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_answer'])) {
    $question_id = $_POST['question_id'];
    $content = $_POST['answer_content'];
    $user_id = $_SESSION['user_id'] ?? 1;

    $sql = "INSERT INTO answers (question_id, user_id, content) VALUES ('$question_id', '$user_id', '$content')";
    // mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Circle - Q&A</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="padding: 20px;">
        <h2>Study Questions & Answers</h2>

        <!-- Question form -->
        <div style="background: #f3f0f0; padding: 15px; margin-bottom: 20px;">
            <h3>Ask a Question</h3>
            <form method="POST" action="">
                <input type="text" name="title" placeholder="Question Title" required style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
                <textarea name="content" placeholder="Write your question details here..." required style="width: 100%; padding: 8px; margin-bottom: 10px;"></textarea><br>
                <button type="submit" name="submit_question" style="padding: 10px 15px;">Post Question</button>
            </form>
        </div>

        <hr>

        <h3>All Questions</h3>
                <div class="question-list">
                        <div style="border: 1px solid #efe9e9; padding: 15px; margin-bottom: 15px;">
                <h4>Sample Question Title?</h4>
                <p>This is the content of the question asked by a student.</p>
                
                                <form method="POST" action="" style="margin-top: 10px;">
                    <input type="hidden" name="question_id" value="1">
                    <input type="text" name="answer_content" placeholder="Write an answer..." required style="padding: 5px; width: 70%;">
                    <button type="submit" name="submit_answer" style="padding: 5px 10px;">Reply</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>