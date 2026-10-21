<?php
include "../includes/auth.php";
requireAdmin();
?>
<!DOCTYPE html>
<html>
<head><title>Admin Dashboard</title></head>
<body>
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Admin)</h2>
  <a href="../auth/logout.php">Logout</a>
</body>
</html>