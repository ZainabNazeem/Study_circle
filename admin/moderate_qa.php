<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

// Delete a question (its answers are deleted automatically)
if (isset($_POST['delete_question'])) {
    $q_id = (int)$_POST['delete_question'];
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $q_id);
    $stmt->execute();
    header("Location: moderate_qa.php");
    exit();
}

// Delete an answer
if (isset($_POST['delete_answer'])) {
    $a_id = (int)$_POST['delete_answer'];
    $stmt = $conn->prepare("DELETE FROM answers WHERE id = ?");
    $stmt->bind_param("i", $a_id);
    $stmt->execute();
    header("Location: moderate_qa.php");
    exit();
}

$questions = $conn->query("SELECT q.id, q.title, q.body, q.created_at, u.full_name, s.name AS subject
                           FROM questions q
                           JOIN users u ON q.user_id = u.id
                           JOIN subjects s ON q.subject_id = s.id
                           ORDER BY q.created_at DESC");

$answers = $conn->query("SELECT a.id, a.body, a.created_at, u.full_name, q.title AS question_title
                         FROM answers a
                         JOIN users u ON a.user_id = u.id
                         JOIN questions q ON a.question_id = q.id
                         ORDER BY a.created_at DESC");

$page_title = "Moderate Q and A";
include "../includes/header.php";
?>

<div class="container mt-4">
  <h2>Moderate Questions and Answers</h2>

  <h4 class="mt-4">Questions</h4>
  <table class="table table-bordered table-hover bg-white">
    <thead>
      <tr><th>Title</th><th>Subject</th><th>Asked by</th><th>Date</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php if ($questions->num_rows == 0) { ?>
        <tr><td colspan="5" class="text-center">No questions.</td></tr>
      <?php } ?>
      <?php while ($q = $questions->fetch_assoc()) { ?>
        <tr>
          <td><?php echo htmlspecialchars($q['title']); ?></td>
          <td><?php echo htmlspecialchars($q['subject']); ?></td>
          <td><?php echo htmlspecialchars($q['full_name']); ?></td>
          <td><?php echo date('Y-m-d', strtotime($q['created_at'])); ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Delete this question and its answers?');">
              <button type="submit" name="delete_question" value="<?php echo $q['id']; ?>" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <h4 class="mt-4">Answers</h4>
  <table class="table table-bordered table-hover bg-white">
    <thead>
      <tr><th>Answer</th><th>Question</th><th>Answered by</th><th>Date</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php if ($answers->num_rows == 0) { ?>
        <tr><td colspan="5" class="text-center">No answers.</td></tr>
      <?php } ?>
      <?php while ($a = $answers->fetch_assoc()) { ?>
        <tr>
          <td><?php echo htmlspecialchars($a['body']); ?></td>
          <td><?php echo htmlspecialchars($a['question_title']); ?></td>
          <td><?php echo htmlspecialchars($a['full_name']); ?></td>
          <td><?php echo date('Y-m-d', strtotime($a['created_at'])); ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Delete this answer?');">
              <button type="submit" name="delete_answer" value="<?php echo $a['id']; ?>" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include "../includes/footer.php"; ?>