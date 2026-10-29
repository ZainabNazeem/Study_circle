<?php
include "../includes/auth.php";
requireLogin();

require_once "../config/db.php";

$page_title = "Upload Notes";
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $subject_id = (int)($_POST["subject_id"] ?? 0);

    if (
        $title === "" ||
        $subject_id <= 0 ||
        !isset($_FILES["note_file"]) ||
        $_FILES["note_file"]["error"] !== UPLOAD_ERR_OK
    ) {
        $message = "Please fill all required fields and select a file.";
        $message_type = "danger";
    } else {
        $original_name = basename($_FILES["note_file"]["name"]);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_extensions = ["pdf", "doc", "docx", "ppt", "pptx"];

        if (!in_array($extension, $allowed_extensions, true)) {
            $message = "Allowed file types: PDF, DOC, DOCX, PPT, PPTX.";
            $message_type = "danger";
        } elseif ($_FILES["note_file"]["size"] > 10 * 1024 * 1024) {
            $message = "File size must be 10 MB or less.";
            $message_type = "danger";
        } else {
            $upload_dir = __DIR__ . "/uploads/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $stored_name = bin2hex(random_bytes(16)) . "." . $extension;
            $target_path = $upload_dir . $stored_name;

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO materials (user_id, subject_id, title, description, file_name, status)
                 VALUES (?, ?, ?, ?, ?, 'pending')"
            );

            $user_id = (int)$_SESSION["user_id"];

            mysqli_stmt_bind_param(
                $stmt,
                "iisss",
                $user_id,
                $subject_id,
                $title,
                $description,
                $stored_name
            );

            if (move_uploaded_file($_FILES["note_file"]["tmp_name"], $target_path)) {
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Notes uploaded successfully! They are waiting for approval.";
                    $message_type = "success";
                } else {
                    unlink($target_path);
                    $message = "Could not save the note details. Please try again.";
                    $message_type = "danger";
                }
            } else {
                $message = "Could not upload the file. Please try again.";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

$subjects_result = mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name");

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Upload Notes</h2>
    <p class="text-muted">Share your study materials with other students.</p>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 mt-3">
        <div class="mb-3">
            <label for="title" class="form-label">Note Title *</label>
            <input type="text" name="title" id="title" class="form-control"
                   maxlength="255" required>
        </div>

        <div class="mb-3">
            <label for="subject_id" class="form-label">Subject *</label>
            <select name="subject_id" id="subject_id" class="form-select" required>
                <option value="">Select a subject</option>
                <?php if ($subjects_result): ?>
                    <?php while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                        <option value="<?php echo (int)$subject["id"]; ?>">
                            <?php echo htmlspecialchars($subject["name"]); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control"
                      rows="3" maxlength="2000"></textarea>
        </div>

        <div class="mb-3">
            <label for="note_file" class="form-label">Choose Notes File *</label>
            <input type="file" name="note_file" id="note_file" class="form-control"
                   accept=".pdf,.doc,.docx,.ppt,.pptx" required>
            <small class="text-muted">PDF, DOC, DOCX, PPT or PPTX — maximum 10 MB.</small>
        </div>

        <button type="submit" class="btn btn-primary">Upload Notes</button>
        <a href="dashboard.php" class="btn btn-secondary mt-2">Back to Dashboard</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
