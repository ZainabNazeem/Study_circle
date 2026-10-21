<?php
session_start();
include "../config/db.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($name == "" || $email == "" || $password == "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password != $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed);
            mysqli_stmt_execute($stmt);
            $success = "Registered! You can now log in.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register - StudyCircle</title></head>
<body>
  <h2>Register</h2>
  <p style="color:red;"><?php echo $error; ?></p>
  <p style="color:green;"><?php echo $success; ?></p>

  <form method="POST">
    <input type="text" name="full_name" placeholder="Full name"><br><br>
    <input type="email" name="email" placeholder="Email"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm password"><br><br>
    <button type="submit" name="register">Register</button>
  </form>
  <p>Already have an account? <a href="login.php">Login</a></p>

  <script>
  document.querySelector("form").addEventListener("submit", function (e) {
    var password = document.querySelector("[name=password]").value;
    var confirm = document.querySelector("[name=confirm_password]").value;

    if (password.length < 6) {
      alert("Password must be at least 6 characters.");
      e.preventDefault();
    } else if (password != confirm) {
      alert("Passwords do not match.");
      e.preventDefault();
    }
  });
  </script>
</body>
</html>