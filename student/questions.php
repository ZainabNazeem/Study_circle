<?php
include "../includes/auth.php";
include "../config/db.php";
requireLogin();

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "success";

// Post a new question
if (isset($_POST['submit_question'])) {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);
    $subject_id = (int)$_POST['subject_id'];

    if ($title != "" && $body != "" && $subject_id > 0) {
        $stmt = $conn->prepare("INSERT INTO questions (user_id, subject_id, title, body) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $subject_id, $title, $body);
        $stmt->execute();
        $message = "Question posted!";
    } else {
        $message = "Please fill in all fields.";
        $message_type = "danger";
    }
}

// Post an answer
if (isset($_POST['submit_answer'])) {
    $question_id = (int)$_POST['question_id'];
    $answer_body = trim($_POST['answer_body']);

    if ($question_id > 0 && $answer_body != "") {
        $stmt = $conn->prepare("INSERT INTO answers (question_id, user_id, body) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $question_id, $user_id, $answer_body);
        $stmt->execute();
        $message = "Answer posted!";
    } else {
        $message = "Please write an answer.";
        $message_type = "danger";
    }
}

$subjects = $conn->query("SELECT id, name FROM subjects ORDER BY name");

$questions = $conn->query("SELECT q.id, q.title, q.body, q.created_at, u.full_name, s.name AS subject
                           FROM questions q
                           JOIN users u ON q.user_id = u.id
                           JOIN subjects s ON q.subject_id = s.id
                           ORDER BY q.created_at DESC");

// One prepared query, used for each question's answers
$answer_stmt = $conn->prepare("SELECT a.body, a.created_at, u.full_name
                               FROM answers a
                               JOIN users u ON a.user_id = u.id
                               WHERE a.question_id = ?
                               ORDER BY a.created_at ASC");
$qid = 0;
$answer_stmt->bind_param("i", $qid);

$page_title = "Questions and Answers";
include "../includes/header.php";
?>

<div class="container mt-4">
  <h2>Study Questions and Answers</h2>

  <?php if ($message != "") { ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
  <?php } ?>

  <div class="card p-4 mb-4">
    <h4>Ask a Question</h4>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Subject</label>
        <select name="subject_id" class="form-select" required>
          <option value="">-- Choose subject --</option>
          <?php while ($s = $subjects->fetch_assoc()) { ?>
            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Details</label>
        <textarea name="body" id="body" class="form-control" rows="3" maxlength="500" required></textarea>
        <small class="text-muted"><span id="count">0</span> / 500 characters</small>
      </div>

      <button type="submit" name="submit_question" class="btn btn-primary">Post Question</button>
    </form>
  </div>

  <h3>All Questions</h3>

  <?php if ($questions->num_rows == 0) { ?>
    <p class="text-muted">No questions yet. Be the first to ask!</p>
  <?php } ?>

  <?php while ($q = $questions->fetch_assoc()) { ?>
    <div class="card p-3 mb-3">
      <h5><?php echo htmlspecialchars($q['title']); ?></h5>
      <small class="text-muted">
        <?php echo htmlspecialchars($q['subject']); ?> |
        Asked by <?php echo htmlspecialchars($q['full_name']); ?> |
        <?php echo date('Y-m-d', strtotime($q['created_at'])); ?>
      </small>
      <p class="mt-2"><?php echo nl2br(htmlspecialchars($q['body'])); ?></p>

      <?php
      $qid = $q['id'];
      $answer_stmt->execute();
      $answers = $answer_stmt->get_result();
      ?>

      <?php while ($a = $answers->fetch_assoc()) { ?>
        <div class="border-start border-success border-3 ps-3 mb-2">
          <?php echo nl2br(htmlspecialchars($a['body'])); ?><br>
          <small class="text-muted">
            <?php echo htmlspecialchars($a['full_name']); ?> |
            <?php echo date('Y-m-d', strtotime($a['created_at'])); ?>
          </small>
        </div>
      <?php } ?>

      <form method="POST" class="mt-2">
        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
        <div class="input-group">
          <input type="text" name="answer_body" class="form-control" placeholder="Write an answer..." required>
          <button type="submit" name="submit_answer" class="btn btn-success">Reply</button>
        </div>
      </form>
    </div>
  <?php } ?>
</div>

<script>
// Show how many characters have been typed
document.getElementById("body").addEventListener("input", function () {
  document.getElementById("count").innerText = this.value.length;
});
</script>

<?php include "../includes/footer.php"; ?>