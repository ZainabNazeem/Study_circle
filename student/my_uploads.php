
<?php
include "../includes/auth.php";
requireLogin();

require_once "../config/db.php";

$page_title = "My Uploaded Notes";

$user_id = (int)$_SESSION["user_id"];

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
                    </tr>
                </thead>
                <tbody>
                    <?php while ($note = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($note["title"]); ?></td>
                            <td><?php echo htmlspecialchars($note["subject_name"] ?? "Unknown"); ?></td>
                            <td><?php echo htmlspecialchars($note["description"] ?? ""); ?></td>
                            <td>
                                <a href="uploads/<?php echo rawurlencode($note["file_name"]); ?>"
                                   target="_blank" rel="noopener">
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
