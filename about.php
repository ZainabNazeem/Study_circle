<?php
include "config/db.php";

$page_title = "About Us - StudyCircle";

// Live numbers from the database
function count_rows($conn, $sql) {
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        return 0;
    }
    $row = mysqli_fetch_row($res);
    return (int)$row[0];
}

$total_users    = count_rows($conn, "SELECT COUNT(*) FROM users");
$total_subjects = count_rows($conn, "SELECT COUNT(*) FROM subjects");
$total_notes    = count_rows($conn, "SELECT COUNT(*) FROM materials WHERE status='approved'");

$features = [
    ["Share Notes", "Upload your lecture notes and study materials so other students can use them."],
    ["Download by Subject", "Find approved notes quickly by choosing a subject."],
    ["Discussion Forum", "Ask academic questions and answer your friends' questions."],
    ["Study Groups", "Create or join study groups and schedule group study sessions."],
    ["Rate and Comment", "Rate shared resources and leave comments to help others find the best notes."],
    ["Moderated and Safe", "Admins approve notes and moderate the forum to keep everything useful."],
];

$steps = [
    ["Register", "Create a free StudyCircle account with your name and email."],
    ["Share and Learn", "Upload your notes, download notes by subject and rate them."],
    ["Join the Community", "Ask questions, answer others and join study groups."],
];


$team = [
    ["Zainab Nazeem", "Register, login, layout and CSS"],
    ["Fathima Nufna", "Admin: manage users and subjects (CRUD), About and Contact pages"],
    ["Jeyatharani", "Student: upload notes and see own uploads"],
    ["Yatheesha", "Admin: approve or reject notes "],
    ["Sanjana", "Rate and comment on resources"],
    ["Kumarasiri", "Questions and answers,admin moderation"],
    ["Teena", "Study groups and sessions"],
];

$tech = ["HTML", "CSS", "JavaScript", "PHP", "MySQL", "Git and GitHub"];

include "includes/header.php";
?>

<style>
  .stat-number { font-size: 2.2rem; font-weight: 700; color: var(--nsbm-blue); }
  .step-circle {
    width: 48px; height: 48px; border-radius: 50%;
    background: var(--nsbm-green); color: #fff; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px auto;
  }
  .team-avatar {
    width: 56px; height: 56px; border-radius: 50%;
    background: var(--nsbm-blue); color: #fff; font-weight: 700; font-size: 1.3rem;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
  }
</style>

<div class="container py-4">

  <h2>About StudyCircle</h2>
  <p class="text-muted">A student community for sharing notes, asking questions and studying together.</p>

  <!-- Live stats from the database -->
  <div class="row g-3 mt-1">
    <div class="col-md-4">
      <div class="card dashboard-card p-3 text-center">
        <div class="stat-number"><?php echo $total_users; ?></div>
        <div>Students and admins</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3 text-center">
        <div class="stat-number"><?php echo $total_subjects; ?></div>
        <div>Subjects</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3 text-center">
        <div class="stat-number"><?php echo $total_notes; ?></div>
        <div>Approved notes</div>
      </div>
    </div>
  </div>

  <!-- What is StudyCircle -->
  <div class="card dashboard-card p-4 mt-4">
    <h4>What is StudyCircle?</h4>
    <p class="mb-2">
      StudyCircle is a collaborative web platform built by NSBM Green University students.
      It helps students share lecture notes and study materials, ask and answer academic
      questions, and form study groups across different modules.
    </p>
    <p class="mb-0">
      Our goal is simple: nobody should study alone. When students help each other,
      everyone learns faster.
    </p>
  </div>

  <!-- Features -->
  <h3 class="mt-5 mb-3">What you can do</h3>
  <div class="row g-3">
    <?php foreach ($features as $f): ?>
      <div class="col-md-4">
        <div class="card dashboard-card p-3 h-100">
          <h5><?php echo htmlspecialchars($f[0]); ?></h5>
          <p class="mb-0"><?php echo htmlspecialchars($f[1]); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- How it works -->
  <h3 class="mt-5 mb-3">How it works</h3>
  <div class="row g-3">
    <?php foreach ($steps as $i => $s): ?>
      <div class="col-md-4">
        <div class="card p-3 h-100 text-center">
          <div class="step-circle"><?php echo $i + 1; ?></div>
          <h5><?php echo htmlspecialchars($s[0]); ?></h5>
          <p class="mb-0"><?php echo htmlspecialchars($s[1]); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Team -->
  <h3 class="mt-5 mb-3">Our team</h3>
  <div class="row g-3">
    <?php foreach ($team as $m): ?>
      <div class="col-md-4">
        <div class="card p-3 h-100">
          <div class="team-avatar"><?php echo htmlspecialchars(strtoupper(substr($m[0], 0, 1))); ?></div>
          <h5 class="mb-1"><?php echo htmlspecialchars($m[0]); ?></h5>
          <p class="mb-0 text-muted"><?php echo htmlspecialchars($m[1]); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Technologies -->
  <h3 class="mt-5 mb-3">Built with</h3>
  <div class="card p-3">
    <div>
      <?php foreach ($tech as $t): ?>
        <span class="badge bg-primary me-1 mb-1"><?php echo htmlspecialchars($t); ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Call to action -->
  <div class="card dashboard-card p-4 mt-5 text-center">
    <h4>Ready to study together?</h4>
    <div class="mt-2">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="/studycircle/auth/register.php" class="btn btn-primary">Join StudyCircle</a>
      <?php elseif (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="/studycircle/admin/dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
      <?php else: ?>
        <a href="/studycircle/student/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
      <?php endif; ?>
      <a href="/studycircle/contact.php" class="btn btn-outline-secondary">Contact us</a>
    </div>
  </div>

</div>

<?php include "includes/footer.php"; ?>
