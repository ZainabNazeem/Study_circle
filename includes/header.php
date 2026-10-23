<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($page_title) ? $page_title : "StudyCircle"; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/studycircle/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-nsbm">
<div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/studycircle/index.php">
    <img src="/studycircle/assets/images/nsbm-logo.png" alt="NSBM Green University" height="38" class="logo-box me-2">
    StudyCircle
    </a>    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">

        <?php if (!isset($_SESSION['user_id'])) { ?>
          <li class="nav-item"><a class="nav-link" href="/studycircle/auth/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/studycircle/auth/register.php">Register</a></li>

        <?php } elseif ($_SESSION['role'] == 'admin') { ?>
          <li class="nav-item"><a class="nav-link" href="/studycircle/admin/dashboard.php">Admin Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/studycircle/auth/logout.php">Logout</a></li>

        <?php } else { ?>
          <li class="nav-item"><a class="nav-link" href="/studycircle/student/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/studycircle/auth/logout.php">Logout</a></li>
        <?php } ?>

      </ul>
    </div>
  </div>
</nav>