<?php
include '../includes/auth.php';
include '../config/db.php';
requireLogin();

$sql = "SELECT m.id, m.title, m.description, m.created_at,
               s.name AS subject_name, u.full_name AS uploader,
               AVG(r.rating) AS avg_rating, COUNT(r.id) AS total_reviews
        FROM materials m
        LEFT JOIN subjects s ON m.subject_id = s.id
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN reviews r ON r.material_id = m.id
        WHERE m.status = 'approved'
        GROUP BY m.id, s.name, u.full_name
        ORDER BY m.created_at DESC";
$result = mysqli_query($conn, $sql);

$subjects = mysqli_query($conn, "SELECT name FROM subjects ORDER BY name");

$page_title = "Find Materials";
include '../includes/header.php';
?>

<div class="container mt-4">
  <h2>Find Materials</h2>
  <p class="text-muted">Browse approved study materials, then open one to rate and comment.</p>

  <div class="row g-2 mb-3">
    <div class="col-md-8">
      <input type="text" id="search" class="form-control" placeholder="Search by title...">
    </div>
    <div class="col-md-4">
      <select id="subjectFilter" class="form-select">
        <option value="">All subjects</option>
        <?php while ($s = mysqli_fetch_assoc($subjects)) { ?>
          <option value="<?php echo htmlspecialchars(strtolower($s['name'])); ?>">
            <?php echo htmlspecialchars($s['name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>
  </div>

  <?php if (mysqli_num_rows($result) == 0) { ?>
    <p class="text-muted">No approved materials yet.</p>
  <?php } ?>

  <div class="row g-3">
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <?php $desc = $row['description'] ?? ''; ?>
      <div class="col-md-4 material-item"
           data-title="<?php echo htmlspecialchars(strtolower($row['title'])); ?>"
           data-subject="<?php echo htmlspecialchars(strtolower($row['subject_name'] ?? '')); ?>">
        <div class="card p-3 h-100">
          <h5><?php echo htmlspecialchars($row['title']); ?></h5>
          <p>
            <b>Subject:</b> <?php echo htmlspecialchars($row['subject_name'] ?? ''); ?><br>
            <b>By:</b> <?php echo htmlspecialchars($row['uploader'] ?? ''); ?><br>
            <b>Rating:</b>
            <?php
            if ($row['total_reviews'] > 0) {
                echo round($row['avg_rating'], 1) . " / 5 (" . $row['total_reviews'] . " reviews)";
            } else {
                echo "No ratings yet";
            }
            ?>
          </p>
          <p>
            <?php echo htmlspecialchars(substr($desc, 0, 100)); ?>
            <?php echo strlen($desc) > 100 ? "..." : ""; ?>
          </p>
          <a href="view_material.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary">View &amp; Rate</a>
        </div>
      </div>
    <?php } ?>
  </div>
</div>

<script>
// Filter the cards by title and subject while the student types
function filterMaterials() {
  var text = document.getElementById("search").value.toLowerCase();
  var subject = document.getElementById("subjectFilter").value;
  var items = document.querySelectorAll(".material-item");

  for (var i = 0; i < items.length; i++) {
    var titleOk = items[i].dataset.title.includes(text);
    var subjectOk = subject == "" || items[i].dataset.subject == subject;
    items[i].style.display = (titleOk && subjectOk) ? "" : "none";
  }
}

document.getElementById("search").addEventListener("keyup", filterMaterials);
document.getElementById("subjectFilter").addEventListener("change", filterMaterials);
</script>

<?php include '../includes/footer.php'; ?>