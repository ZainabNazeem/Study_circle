<?php
session_start();
include "../config/db.php";

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, full_name, password, role, status FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] == 'blocked') {
            $error = "Your account is blocked.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../student/dashboard.php");
            }
            exit();
        }
    } else {
        $error = "Wrong email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login - StudyCircle</title></head>
<body>
  <h2>Login</h2>
  <p style="color:red;"><?php echo $error; ?></p>

  <form method="POST">
    <input type="email" name="email" placeholder="Email"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <button type="submit" name="login">Login</button>
  </form>
  <p>No account? <a href="register.php">Register</a></p>
</body>
</html>