<?php
include '../includes/auth.php';
requireLogin();
include '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Rating + Comment save karana eka
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    // Kalin rate karala thiyenawa nam update karanna, nathnam aluthen danna
    $check = mysqli_query($conn, "SELECT id FROM reviews WHERE material_id=$id AND user_id=$user_id");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE reviews SET rating=$rating, comment='$comment' WHERE material_id=$id AND user_id=$user_id");
    } else {
        mysqli_query($conn, "INSERT INTO reviews (material_id, user_id, rating, comment) VALUES ($id, $user_id, $rating, '$comment')");
    }
    header("Location: view_material.php?id=$id&saved=1");
    exit;
}

$m = mysqli_fetch_assoc(mysqli_query($conn, "SELECT m.*, s.name AS subject_name, u.full_name AS uploader FROM materials m LEFT JOIN subjects s ON m.subject_id=s.id LEFT JOIN users u ON m.user_id=u.id WHERE m.id=$id"));
if (!$m) { echo "Material not found"; exit; }
$page_title = $m['title'];
include '../includes/header.php';
$reviews = mysqli_query($conn, "SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.material_id=$id ORDER BY r.id DESC");
?>
<div class="container mt-4">
<a href="materials.php" class="btn btn-secondary btn-sm">Back</a>
<?php if(isset($_GET['saved'])) echo '<div class="alert alert-success mt-2">Saved! Thank you.</div>'; ?>
<h2 class="mt-2"><?php echo htmlspecialchars($m['title']); ?></h2>
<p><b>Subject:</b> <?php echo htmlspecialchars($m['subject_name']); ?> | <b>By:</b> <?php echo htmlspecialchars($m['uploader']); ?></p>
<p><?php echo nl2br(htmlspecialchars($m['description'])); ?></p>
<?php if(!empty($m['file_path'])) { ?>
<a href="../<?php echo htmlspecialchars($m['file_path']); ?>" target="_blank" class="btn btn-success">Download / Open File</a>
<?php } ?>
<hr>
<h4>Rate & Comment</h4>
<form method="POST">
<label>Rating (1-5):</label>
<select name="rating" class="form-control" required>
<option value="5">5 - Excellent</option>
<option value="4">4 - Good</option>
<option value="3">3 - Average</option>
<option value="2">2 - Poor</option>
<option value="1">1 - Bad</option>
</select>
<label class="mt-2">Comment:</label>
<textarea name="comment" class="form-control" placeholder="Me material eka kohomada?" required></textarea>
<button class="btn btn-primary mt-2">Submit Review</button>
</form>
<hr>
<h4>Reviews</h4>
<?php while($r = mysqli_fetch_assoc($reviews)) { ?>
<div class="card p-3 mb-2"><b><?php echo htmlspecialchars($r['full_name']); ?> - <?php echo $r['rating']; ?> / 5</b><br><?php echo htmlspecialchars($r['comment']); ?></div>
<?php } ?>
</div>
<?php include '../includes/footer.php'; ?>
