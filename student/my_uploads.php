
<?php
include "../includes/auth.php";
requireLogin();

require_once "../config/db.php";

$page_title = "My Uploaded Notes";
$user_id = (int)$_SESSION["user_id"];

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$message_type = "info";

// Delete note
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_note_id"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $message = "Invalid request. Please try again.";
        $message_type = "danger";
    } else {
        $note_id = (int)$_POST["delete_note_id"];

        // Check that this note belongs to the logged-in student
        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT file_name, status FROM materials
             WHERE id = ? AND user_id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($check_stmt, "ii", $note_id, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $note = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);

        if (!$note) {
            $message = "Note not found.";
            $message_type = "danger";
        } elseif ($note["status"] === "approved") {
            $message = "Approved notes cannot be deleted.";
            $message_type = "danger";
        } else {
            // Delete only this student's non-approved note
            $delete_stmt = mysqli_prepare(
                $conn,
                "DELETE FROM materials
                 WHERE id = ? AND user_id = ? AND status <> 'approved'"
            );

            mysqli_stmt_bind_param($delete_stmt, "ii", $note_id, $user_id);
            mysqli_stmt_execute($delete_stmt);

            if (mysqli_stmt_affected_rows($delete_stmt) === 1) {
                $file_path = __DIR__ . "/../uploads/" . basename($note["file_name"]);

                if (is_file($file_path)) {
                    unlink($file_path);
                }

                $message = "Note deleted successfully!";
                $message_type = "success";
            } else {
                $message = "Could not delete the note. Please try again.";
                $message_type = "danger";
            }

            mysqli_stmt_close($delete_stmt);
        }
    }
}

// Get student's uploaded notes
$stmt = mysqli_prepare(
    $conn,
    "SELECT materials.id, materials.title, materials.description,
            materials.file_name, materials.status, materials.created_at,
            subjects.name AS subject_name
     FROM materials
     LEFT JOIN subjects ON materials.subject_id = subjects.id
     WHERE materials.user_id = ?
     ORDER BY materials.created_at DESC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>My Uploaded Notes</h2>
    <p class="text-muted">View the study materials you have uploaded.</p>

    <a href="upload.php" class="btn btn-primary mb-3">Upload New Notes</a>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Uploaded Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($note = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($note["title"]); ?></td>

                            <td>
                                <?php echo htmlspecialchars($note["subject_name"] ?? "Unknown"); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($note["description"] ?? ""); ?>
                            </td>

                            <td>
                                <a href="../uploads/<?php echo rawurlencode($note["file_name"]); ?>" target="_blank" rel="noopener">
                                    View File
                                </a>
                            </td>

                            <td>
                                <?php
                                $status = $note["status"];
                                $badge_class = "secondary";

                                if ($status === "approved") {
                                    $badge_class = "success";
                                } elseif ($status === "pending") {
                                    $badge_class = "warning";
                                } elseif ($status === "rejected") {
                                    $badge_class = "danger";
                                }
                                ?>

                                <span class="badge bg-<?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                                </span>
                            </td>

                            <td><?php echo htmlspecialchars($note["created_at"]); ?></td>

                            <td>
                                <?php if ($status !== "approved"): ?>
                                    <form method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this note?');">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                        <input type="hidden"
                                               name="delete_note_id"
                                               value="<?php echo (int)$note["id"]; ?>">

                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Not allowed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You have not uploaded any notes yet.
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php
mysqli_stmt_close($stmt);
include "../includes/footer.php";
?>
