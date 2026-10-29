<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config/db.php";


$contact_email = "study-circle-email@gmail.com";

$errors = [];
$name = "";
$email = "";
$subject = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = "Please enter your name (maximum 100 characters).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $errors[] = "Please enter a valid email address.";
    }
    if ($subject === '' || mb_strlen($subject) > 150) {
        $errors[] = "Please enter a subject (maximum 150 characters).";
    }
    if (mb_strlen($message) < 10 || mb_strlen($message) > 2000) {
        $errors[] = "Your message must be between 10 and 2000 characters.";
    }

    // Save to database
    if (!$errors) {
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
            mysqli_stmt_execute($stmt);
            header("Location: /studycircle/contact.php?sent=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            $errors[] = "Sorry, we could not save your message. Please try again later.";
        }
    }
} else {
    // Pre-fill the name for logged-in users
    if (isset($_SESSION['full_name'])) {
        $name = $_SESSION['full_name'];
    }
}

$sent = isset($_GET['sent']);
$page_title = "Contact Us - StudyCircle";
include "includes/header.php";
?>

<div class="container py-4">

  <h2>Contact Us</h2>
  <p class="text-muted">Have a question, suggestion or problem? Send us a message and we will get back to you.</p>

  <?php if ($sent): ?>
    <div class="alert alert-success">Thank you! Your message has been sent.</div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
          <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="row g-3">

    <!-- Contact form -->
    <div class="col-lg-7">
      <div class="card dashboard-card p-4 h-100">
        <h4>Send us a message</h4>
        <form method="post" action="/studycircle/contact.php" id="contactForm" class="needs-validation" novalidate>

          <div class="mb-3">
            <label class="form-label" for="name">Your name</label>
            <input type="text" class="form-control" id="name" name="name" maxlength="100"
                   value="<?php echo htmlspecialchars($name); ?>" required>
            <div class="invalid-feedback">Please enter your name.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" maxlength="100"
                   value="<?php echo htmlspecialchars($email); ?>" required>
            <div class="invalid-feedback">Please enter a valid email address.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="subject">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject" maxlength="150"
                   value="<?php echo htmlspecialchars($subject); ?>" required>
            <div class="invalid-feedback">Please enter a subject.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="message">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5"
                      minlength="10" maxlength="2000" required><?php echo htmlspecialchars($message); ?></textarea>
            <div class="invalid-feedback">Your message must be at least 10 characters.</div>
          </div>

          <button type="submit" class="btn btn-primary">Send message</button>
        </form>
      </div>
    </div>

    <!-- Contact details -->
    <div class="col-lg-5">
      <div class="card dashboard-card p-4 h-100">
        <h4>Contact details</h4>
        <p class="mb-2"><strong>University</strong><br>NSBM Green University</p>
        <p class="mb-2"><strong>Address</strong><br>Pitipana, Homagama, Sri Lanka</p>
        <p class="mb-2"><strong>Phone</strong><br>+94 11 544 5000</p>
        <p class="mb-2"><strong>Project email</strong><br><?php echo htmlspecialchars($contact_email); ?></p>
        <p class="mb-0"><strong>Reply time</strong><br>We usually reply within 2 working days.</p>
      </div>
    </div>

  </div>

  <!-- Map -->
  <div class="card p-3 mt-4">
    <h5>Find us</h5>
    <div class="ratio ratio-21x9">
      <iframe src="https://www.google.com/maps?q=NSBM+Green+University+Homagama&amp;output=embed"
              style="border:0;" allowfullscreen loading="lazy" title="NSBM Green University map"></iframe>
    </div>
  </div>

  <!-- FAQ -->
  <h3 class="mt-5 mb-3">Frequently asked questions</h3>
  <div class="accordion" id="faq">
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
          How do I upload notes?
        </button>
      </h2>
      <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faq">
        <div class="accordion-body">Log in as a student, open Upload Notes, choose a subject and upload your file.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
          Why is my note not visible yet?
        </button>
      </h2>
      <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faq">
        <div class="accordion-body">Every note is checked by an admin first. It appears after it is approved.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
          My account is blocked. What should I do?
        </button>
      </h2>
      <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faq">
        <div class="accordion-body">Send us a message using the form above with your registered email, and an admin will review it.</div>
      </div>
    </div>
  </div>

</div>

<script>
  // Client-side form validation (Bootstrap style)
  (function () {
    var form = document.getElementById('contactForm');
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  })();
</script>

<?php include "includes/footer.php"; ?>
