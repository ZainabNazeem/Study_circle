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

$page_title = "Login";
include "../includes/header.php";
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4">

        <div class="text-center mb-3">
          <img src="/studycircle/assets/images/nsbm-logo.png" alt="NSBM" height="70">
          <h3 class="mt-3">Login to StudyCircle</h3>
        </div>

        <?php if ($error != "") { ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="password" id="password" class="form-control" required>
              <button type="button" class="btn btn-outline-secondary" id="togglePassword">Show</button>
            </div>
          </div>

          <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3 mb-0">No account? <a href="register.php">Register</a></p>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById("togglePassword").addEventListener("click", function () {
  var box = document.getElementById("password");
  if (box.type == "password") {
    box.type = "text";
    this.innerText = "Hide";
  } else {
    box.type = "password";
    this.innerText = "Show";
  }
});
</script>

<?php include "../includes/footer.php"; ?>