<?php
$page_title = "Home";
include "includes/header.php";
?>

<div class="hero text-center">
  <div class="container">
    <img src="/studycircle/assets/images/nsbm-logo.png" alt="NSBM" height="90" class="logo-box mb-3">
    <h1>Welcome to StudyCircle</h1>
    <p class="lead">Share notes, ask questions and study together with your NSBM classmates.</p>

    <?php if (!isset($_SESSION['user_id'])) { ?>
      <a href="/studycircle/auth/register.php" class="btn btn-primary btn-lg me-2">Register</a>
      <a href="/studycircle/auth/login.php" class="btn btn-outline-primary btn-lg">Login</a>
    <?php } elseif ($_SESSION['role'] == 'admin') { ?>
      <a href="/studycircle/admin/dashboard.php" class="btn btn-primary btn-lg">Go to Admin Dashboard</a>
    <?php } else { ?>
      <a href="/studycircle/student/dashboard.php" class="btn btn-primary btn-lg">Go to My Dashboard</a>
    <?php } ?>
  </div>
</div>

<div class="container my-5">
  <h2 class="text-center mb-4">What you can do here</h2>

  <div class="row g-4">
    <div class="col-md-3">
      <div class="card dashboard-card p-3 h-100">
        <h5>Share Notes</h5>
        <p>Upload your lecture notes and study materials for others.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card dashboard-card p-3 h-100">
        <h5>Download Materials</h5>
        <p>Find notes by subject and rate the ones that helped you.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card dashboard-card p-3 h-100">
        <h5>Ask Questions</h5>
        <p>Post academic questions and answer your classmates.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card dashboard-card p-3 h-100">
        <h5>Study Groups</h5>
        <p>Create or join a group and schedule study sessions.</p>
      </div>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>