<?php
include '../includes/auth.php';
requireLogin();
include '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

// Handle Rating & Comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    // Check if the user has already reviewed this material
    $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE material_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing review
        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE material_id = ? AND user_id = ?");
        $stmt->bind_param("isii", $rating, $comment, $id, $user_id);
        $stmt->execute();
    } else {
        // Insert new review
        $stmt = $conn->prepare("INSERT INTO reviews (material_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $id, $user_id, $rating, $comment);
        $stmt->execute();
    }
    
    header("Location: view_material.php?id=$id&saved=1");
    exit;
}

// Fetch material details
$stmt = $conn->prepare("SELECT m.*, s.name AS subject_name, u.full_name AS uploader 
                        FROM materials m 
                        LEFT JOIN subjects s ON m.subject_id = s.id 
                        LEFT JOIN users u ON m.user_id = u.id 
                        WHERE m.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();

if (!$m) { 
    echo "Material not found"; 
    exit; 
}

$page_title = $m['title'];
include '../includes/header.php';

// Fetch reviews
$review_stmt = $conn->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.material_id = ? ORDER BY r.id DESC");
$review_stmt->bind_param("i", $id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();
?>

<div class="container mt-4">
    <a href="materials.php" class="btn btn-secondary btn-sm">Back</a>
    
    <?php if(isset($_GET['saved'])) echo '<div class="alert alert-success mt-2">Saved! Thank you.</div>'; ?>
    
    <h2 class="mt-2"><?php echo htmlspecialchars($m['title']); ?></h2>
    <p><b>Subject:</b> <?php echo htmlspecialchars($m['subject_name']); ?> | <b>By:</b> <?php echo htmlspecialchars($m['uploader']); ?></p>
    <p><?php echo nl2br(htmlspecialchars($m['description'])); ?></p>
    
    <!-- FIXED BUG: Changed file_path to file_name and added uploads/ folder -->
    <?php if(!empty($m['file_name'])): ?>
        <a href="uploads/<?php echo htmlspecialchars($m['file_name']); ?>" target="_blank" class="btn btn-success">Download / Open File</a>
    <?php endif; ?>
    
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
        <textarea name="comment" class="form-control" placeholder="How is this material?" required></textarea>
        
        <button class="btn btn-primary mt-2">Submit Review</button>
    </form>
    
    <hr>
    <h4>Reviews</h4>
    <?php while($r = $reviews->fetch_assoc()): ?>
        <div class="card p-3 mb-2">
            <b><?php echo htmlspecialchars($r['full_name']); ?> - <?php echo $r['rating']; ?> / 5</b><br>
            <?php echo htmlspecialchars($r['comment']); ?>
        </div>
    <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>