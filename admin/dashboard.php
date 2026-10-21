<?php
include "../includes/auth.php";
requireAdmin();
$page_title = "Admin Dashboard";
include "../includes/header.php";
?>

<div class="container mt-4">
  <h2>Admin Panel</h2>
  <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>

  <div class="row g-3 mt-2">
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Manage Users</h5>
        <p>View, block or delete users.</p>
        <a href="/studycircle/admin/users.php" class="btn btn-primary">Open</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Manage Subjects</h5>
        <p>Add, edit or delete subjects.</p>
        <a href="/studycircle/admin/subjects.php" class="btn btn-primary">Open</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Approve Materials</h5>
        <p>Approve or reject uploaded notes.</p>
        <a href="#" class="btn btn-primary">Open</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Moderate Forum</h5>
        <p>Review questions and answers.</p>
        <a href="#" class="btn btn-primary">Open</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card p-3">
        <h5>Reports</h5>
        <p>Usage and engagement reports.</p>
        <a href="#" class="btn btn-primary">Open</a>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>