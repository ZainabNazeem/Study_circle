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

$page_title = "Register";
include "../includes/header.php";
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4">

        <div class="text-center mb-3">
          <img src="/studycircle/assets/images/nsbm-logo.png" alt="NSBM" height="70">
          <h3 class="mt-3">Create your account</h3>
        </div>

        <?php if ($error != "") { ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <?php if ($success != "") { ?>
          <div class="alert alert-success">
            <?php echo $success; ?> <a href="login.php">Login here</a>
          </div>
        <?php } ?>

        <form method="POST" id="registerForm">
          <div class="mb-3">
            <label class="form-label">Full name</label>
            <input type="text" name="full_name" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
          </div>

          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="showPasswords">
            <label class="form-check-label" for="showPasswords">Show passwords</label>
          </div>

          <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
      </div>
    </div>
  </div>
</div>

<script>
// Check the passwords before sending the form
document.getElementById("registerForm").addEventListener("submit", function (e) {
  var password = document.getElementById("password").value;
  var confirm = document.getElementById("confirm_password").value;

  if (password.length < 6) {
    alert("Password must be at least 6 characters.");
    e.preventDefault();
  } else if (password != confirm) {
    alert("Passwords do not match.");
    e.preventDefault();
  }
});

// Show or hide both password boxes
document.getElementById("showPasswords").addEventListener("change", function () {
  var type = this.checked ? "text" : "password";
  document.getElementById("password").type = type;
  document.getElementById("confirm_password").type = type;
});
</script>

<?php include "../includes/footer.php"; ?>