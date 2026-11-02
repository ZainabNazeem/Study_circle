<?php
include "../includes/auth.php";
include "../config/db.php";
requireLogin();
$page_title = "My Profile";
$user_id = (int)$_SESSION['user_id'];

$errors = [];
$success = "";

// Existing profile load
$stmt = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$p) {
    $p = [
        'full_name' => $_SESSION['full_name'] ?? '', 'student_id' => '', 'degree' => '',
        'academic_year' => '', 'bio' => '', 'photo' => '', 'phone' => '',
        'interests' => '', 'skills' => '', 'profile_link' => ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name     = trim($_POST['full_name'] ?? '');
    $student_id    = trim($_POST['student_id'] ?? '');
    $degree        = trim($_POST['degree'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $bio           = trim($_POST['bio'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $interests     = trim($_POST['interests'] ?? '');
    $skills        = trim($_POST['skills'] ?? '');
    $profile_link  = trim($_POST['profile_link'] ?? '');
    $photo         = $p['photo'];

    // Required fields
    if ($full_name === '')     $errors[] = "Full name is required.";
    if ($student_id === '')    $errors[] = "Student ID is required.";
    if ($degree === '')        $errors[] = "Degree / Faculty is required.";
    if ($academic_year === '') $errors[] = "Year / Semester is required.";

    // Optional field checks
    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) {
        $errors[] = "Phone number is not valid.";
    }
    if ($profile_link !== '' && !filter_var($profile_link, FILTER_VALIDATE_URL)) {
        $errors[] = "Link must be a valid URL (https://...).";
    }
    if (strlen($bio) > 500) {
        $errors[] = "Bio must be under 500 characters.";
    }

    // Photo upload (optional)
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Photo upload failed.";
        } else {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $errors[] = "Photo must be JPG or PNG.";
            } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $errors[] = "Photo must be under 2MB.";
            } else {
                $dir = __DIR__ . "/uploads/profiles/";
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $newName = bin2hex(random_bytes(16)) . "." . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $newName)) {
                    $photo = $newName;
                } else {
                    $errors[] = "Could not save the photo.";
                }
            }
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO student_profiles
                (user_id, full_name, student_id, degree, academic_year, bio, photo, phone, interests, skills, profile_link)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                full_name=VALUES(full_name), student_id=VALUES(student_id), degree=VALUES(degree),
                academic_year=VALUES(academic_year), bio=VALUES(bio), photo=VALUES(photo),
                phone=VALUES(phone), interests=VALUES(interests), skills=VALUES(skills),
                profile_link=VALUES(profile_link)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssss", $user_id, $full_name, $student_id, $degree,
            $academic_year, $bio, $photo, $phone, $interests, $skills, $profile_link);
        if ($stmt->execute()) {
            $success = "Profile saved successfully!";
        } else {
            $errors[] = "Database error. Please try again.";
        }
        $stmt->close();
    }

    // Show what the user typed
    $p = [
        'full_name' => $full_name, 'student_id' => $student_id, 'degree' => $degree,
        'academic_year' => $academic_year, 'bio' => $bio, 'photo' => $photo, 'phone' => $phone,
        'interests' => $interests, 'skills' => $skills, 'profile_link' => $profile_link
    ];
}

include "../includes/header.php";
?>

<div class="container mt-4 mb-5">
  <div class="card p-4">
    <h3>My Profile</h3>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <?php if (!empty($p['photo'])): ?>
        <img src="/studycircle/student/uploads/profiles/<?php echo htmlspecialchars($p['photo']); ?>"
             alt="Profile photo" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full Name *</label>
          <input type="text" name="full_name" class="form-control" maxlength="100"
                 value="<?php echo htmlspecialchars($p['full_name']); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Student ID *</label>
          <input type="text" name="student_id" class="form-control" maxlength="30"
                 value="<?php echo htmlspecialchars($p['student_id']); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Degree / Faculty *</label>
          <input type="text" name="degree" class="form-control" maxlength="100"
                 value="<?php echo htmlspecialchars($p['degree']); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Year / Semester *</label>
          <select name="academic_year" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach (['Year 1','Year 2','Year 3','Year 4'] as $y): ?>
              <option value="<?php echo $y; ?>" <?php echo $p['academic_year'] === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Bio</label>
          <textarea name="bio" class="form-control" rows="3" maxlength="500"><?php echo htmlspecialchars($p['bio']); ?></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label">Profile Photo (JPG/PNG, max 2MB)</label>
          <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" maxlength="20"
                 value="<?php echo htmlspecialchars($p['phone']); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Interests / Favourite Subjects</label>
          <input type="text" name="interests" class="form-control" maxlength="255"
                 value="<?php echo htmlspecialchars($p['interests']); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Skills / Strong Subjects</label>
          <input type="text" name="skills" class="form-control" maxlength="255"
                 value="<?php echo htmlspecialchars($p['skills']); ?>">
        </div>
        <div class="col-12">
          <label class="form-label">LinkedIn / GitHub Link</label>
          <input type="text" name="profile_link" class="form-control" maxlength="255"
                 placeholder="https://github.com/yourname"
                 value="<?php echo htmlspecialchars($p['profile_link']); ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary mt-4">Save Profile</button>
      <a href="dashboard.php" class="btn btn-outline-secondary mt-4">Back</a>
    </form>
  </div>
</div>

<?php include "../includes/footer.php"; ?>