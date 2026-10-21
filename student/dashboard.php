<?php
include "../includes/auth.php";
requireLogin();
?>
<!DOCTYPE html>
<html>
<head><title>Student Dashboard</title></head>
<body>
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Student)</h2>
  <a href="../auth/logout.php">Logout</a>
</body>
</html>