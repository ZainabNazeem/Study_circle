<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

// Run a COUNT query safely (returns 0 if a table or column is missing)
function count_q($conn, $sql) {
    try {
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return 0;
        }
        $row = mysqli_fetch_row($res);
        return (int)$row[0];
    } catch (mysqli_sql_exception $e) {
        return 0;
    }
}

// Run a SELECT safely (returns an empty list if something is missing)
function rows_q($conn, $sql) {
    try {
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return [];
        }
        return mysqli_fetch_all($res, MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $e) {
        return [];
    }
}

// ---------- Summary numbers ----------
$userStats = [
    "Total users"            => count_q($conn, "SELECT COUNT(*) FROM users"),
    "Students"               => count_q($conn, "SELECT COUNT(*) FROM users WHERE role='student'"),
    "Blocked users"          => count_q($conn, "SELECT COUNT(*) FROM users WHERE status='blocked'"),
    "New users (last 7 days)" => count_q($conn, "SELECT COUNT(*) FROM users WHERE created_at >= (NOW() - INTERVAL 7 DAY)"),
];

$contentStats = [
    "Subjects"       => count_q($conn, "SELECT COUNT(*) FROM subjects"),
    "Notes uploaded" => count_q($conn, "SELECT COUNT(*) FROM materials"),
    "Approved notes" => count_q($conn, "SELECT COUNT(*) FROM materials WHERE status='approved'"),
    "Pending notes"  => count_q($conn, "SELECT COUNT(*) FROM materials WHERE status='pending'"),
    "Rejected notes" => count_q($conn, "SELECT COUNT(*) FROM materials WHERE status='rejected'"),
    "Reviews"        => count_q($conn, "SELECT COUNT(*) FROM reviews"),
];

$communityStats = [
    "Questions asked"   => count_q($conn, "SELECT COUNT(*) FROM questions"),
    "Answers posted"    => count_q($conn, "SELECT COUNT(*) FROM answers"),
    "Study groups"      => count_q($conn, "SELECT COUNT(*) FROM study_groups"),
    "Study sessions"    => count_q($conn, "SELECT COUNT(*) FROM study_sessions"),
    "Contact messages"  => count_q($conn, "SELECT COUNT(*) FROM contact_messages"),
    "Unread messages"   => count_q($conn, "SELECT COUNT(*) FROM contact_messages WHERE status='new'"),
];

// ---------- Tables ----------
$notesPerSubject = rows_q($conn,
    "SELECT s.name, COUNT(m.id) AS total, COALESCE(SUM(m.status='approved'), 0) AS approved
     FROM subjects s
     LEFT JOIN materials m ON m.subject_id = s.id
     GROUP BY s.id, s.name
     ORDER BY total DESC, s.name");

$questionsPerSubject = rows_q($conn,
    "SELECT s.name, COUNT(q.id) AS total
     FROM subjects s
     LEFT JOIN questions q ON q.subject_id = s.id
     GROUP BY s.id, s.name
     ORDER BY total DESC, s.name");

$topRated = rows_q($conn,
    "SELECT m.title, s.name AS subject, ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.id) AS votes
     FROM materials m
     JOIN reviews r ON r.material_id = m.id
     LEFT JOIN subjects s ON s.id = m.subject_id
     GROUP BY m.id, m.title, s.name
     ORDER BY avg_rating DESC, votes DESC
     LIMIT 5");

$topUploaders = rows_q($conn,
    "SELECT u.full_name, COUNT(m.id) AS uploads
     FROM users u
     JOIN materials m ON m.user_id = u.id
     GROUP BY u.id, u.full_name
     ORDER BY uploads DESC
     LIMIT 5");

// ---------- CSV download (must run before any HTML is printed) ----------
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="studycircle_report_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');

    fputcsv($out, ["StudyCircle report", date('Y-m-d H:i')]);
    fputcsv($out, []);
    fputcsv($out, ["Summary", "Value"]);
    foreach ([$userStats, $contentStats, $communityStats] as $group) {
        foreach ($group as $label => $value) {
            fputcsv($out, [$label, $value]);
        }
    }
    fputcsv($out, []);
    fputcsv($out, ["Subject", "Notes uploaded", "Notes approved"]);
    foreach ($notesPerSubject as $row) {
        fputcsv($out, [$row['name'], $row['total'], $row['approved']]);
    }
    fclose($out);
    exit;
}

// Largest values, used to draw the bars
$maxNotes = 1;
foreach ($notesPerSubject as $row) {
    $maxNotes = max($maxNotes, (int)$row['total']);
}
$maxQuestions = 1;
foreach ($questionsPerSubject as $row) {
    $maxQuestions = max($maxQuestions, (int)$row['total']);
}

$page_title = "Reports - StudyCircle";
include "../includes/header.php";
?>
<style>
  .admin-card { border-top: 4px solid var(--nsbm-green); }
  .stat-number { font-size: 2rem; font-weight: 700; color: var(--nsbm-blue); line-height: 1.1; }
  .stat-label { color: #555; font-size: 0.9rem; }
  .bar-track { background: #e9efe9; border-radius: 6px; height: 14px; }
  .bar-fill { background: var(--nsbm-green); border-radius: 6px; height: 14px; }

  @media print {
    .no-print, nav, footer, .site-footer { display: none !important; }
    body { background: #ffffff !important; }
    body > .container h2, body > .container h3 { color: #000 !important; text-shadow: none !important; }
    body > .container > p.text-muted { color: #333 !important; text-shadow: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
  }
</style>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-1">
    <h2 class="mb-0">Reports</h2>
    <div class="no-print">
      <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">Print report</button>
      <a href="reports.php?export=csv" class="btn btn-outline-primary btn-sm">Download CSV</a>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
  </div>
  <p class="text-muted">Usage and engagement summary. Generated on <?php echo date('Y-m-d H:i'); ?>.</p>

  <!-- Summary cards -->
  <?php
  $sections = [
      "Users"             => $userStats,
      "Study materials"   => $contentStats,
      "Community"         => $communityStats,
  ];
  foreach ($sections as $title => $group): ?>
    <h3 class="mt-4 mb-3"><?php echo htmlspecialchars($title); ?></h3>
    <div class="row g-3">
      <?php foreach ($group as $label => $value): ?>
        <div class="col-6 col-md-3">
          <div class="card admin-card p-3 h-100">
            <div class="stat-number"><?php echo (int)$value; ?></div>
            <div class="stat-label"><?php echo htmlspecialchars($label); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <!-- Notes per subject -->
  <h3 class="mt-5 mb-3">Notes per subject</h3>
  <div class="card admin-card">
    <div class="card-body">
      <?php if (!$notesPerSubject): ?>
        <p class="text-muted mb-0">No subjects yet.</p>
      <?php endif; ?>
      <?php foreach ($notesPerSubject as $row):
          $pct = round(((int)$row['total'] / $maxNotes) * 100); ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span><?php echo htmlspecialchars($row['name']); ?></span>
            <span class="text-muted"><?php echo (int)$row['total']; ?> uploaded, <?php echo (int)$row['approved']; ?> approved</span>
          </div>
          <div class="bar-track"><div class="bar-fill" style="width: <?php echo $pct; ?>%;"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Questions per subject -->
  <h3 class="mt-5 mb-3">Questions per subject</h3>
  <div class="card admin-card">
    <div class="card-body">
      <?php if (!$questionsPerSubject): ?>
        <p class="text-muted mb-0">No subjects yet.</p>
      <?php endif; ?>
      <?php foreach ($questionsPerSubject as $row):
          $pct = round(((int)$row['total'] / $maxQuestions) * 100); ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span><?php echo htmlspecialchars($row['name']); ?></span>
            <span class="text-muted"><?php echo (int)$row['total']; ?> questions</span>
          </div>
          <div class="bar-track"><div class="bar-fill" style="width: <?php echo $pct; ?>%;"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Top rated notes and top uploaders -->
  <div class="row g-3 mt-2">
    <div class="col-lg-7">
      <h3 class="mt-3 mb-3">Top rated notes</h3>
      <div class="card admin-card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead><tr><th>Title</th><th>Subject</th><th>Rating</th><th>Votes</th></tr></thead>
              <tbody>
                <?php foreach ($topRated as $row): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject'] ?? '-'); ?></td>
                    <td><span class="badge bg-success"><?php echo htmlspecialchars($row['avg_rating']); ?> / 5</span></td>
                    <td><?php echo (int)$row['votes']; ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$topRated): ?>
                  <tr><td colspan="4" class="text-center text-muted">No ratings yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <h3 class="mt-3 mb-3">Top uploaders</h3>
      <div class="card admin-card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead><tr><th>Student</th><th>Notes</th></tr></thead>
              <tbody>
                <?php foreach ($topUploaders as $row): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo (int)$row['uploads']; ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$topUploaders): ?>
                  <tr><td colspan="2" class="text-center text-muted">No uploads yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<?php include "../includes/footer.php"; ?>