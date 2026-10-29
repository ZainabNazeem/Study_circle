<?php
include "../includes/auth.php";
requireLogin();
$page_title = "Student Dashboard";
include "../includes/header.php";
?>

<div class="container mt-4">
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
  <p class="text-muted">What would you like to do today?</p>

  <div class="row g-3 mt-2">
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Upload Notes</h5>
        <p>Share your study materials.</p>
        <a href="upload.php" class="btn btn-primary">Open</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Download Notes</h5>
        <p>Find materials by subject.</p>
        <a href="#" class="btn btn-primary">Open</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Discussion Forum</h5>
        <p>Ask and answer questions.</p>
        <a href="/studycircle/student/questions.php" class="btn btn-primary">Open</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Study Groups</h5>
        <p>Create or join a group.</p>
        <a href="/studycircle/student/groups.php" class="btn btn-primary">Open</a>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>