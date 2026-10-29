<?php
include '../includes/auth.php';
requireLogin();
include '../config/db.php';
$page_title = "Find Materials";
include '../includes/header.php';
$sql = "SELECT m.*, s.name AS subject_name, u.full_name AS uploader,
        AVG(r.rating) AS avg_rating, COUNT(r.id) AS total_reviews
        FROM materials m
        LEFT JOIN subjects s ON m.subject_id = s.id
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN reviews r ON r.material_id = m.id
        WHERE m.status = 'approved'
        GROUP BY m.id ORDER BY m.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="container mt-4">
<h2>Find Materials</h2>
<p>Resources balala rate + comment karanna.</p>
<div class="row g-3">
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<div class="col-md-4">
<div class="card p-3 h-100">
<h5><?php echo htmlspecialchars($row['title']); ?></h5>
<p><b>Subject:</b> <?php echo htmlspecialchars($row['subject_name']); ?><br>
<b>By:</b> <?php echo htmlspecialchars($row['uploader']); ?><br>
<b>Rating:</b> <?php echo $row['total_reviews'] > 0 ? round($row['avg_rating'],1) . " / 5 (" . $row['total_reviews'] . " reviews)" : "No ratings yet"; ?></p>
<p><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?></p>
<a href="view_material.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View & Rate</a>
</div>
</div>
<?php } ?>
</div>
</div>
<?php include '../includes/footer.php'; ?>
