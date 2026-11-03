
<?php
include "../includes/auth.php";
requireLogin();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    exit("Access denied. Students only.");
}

include "../config/db.php";

$page_title = "Academic Calendar";
include "../includes/header.php";

$categories = [
    'Exam' => 'Examinations',
    'Assignment' => 'Assignments',
    'Academic Event' => 'Academic Events',
    'Important Date' => 'Important Dates'
];

$sql = "SELECT * FROM academic_events ORDER BY event_date ASC";
$result = mysqli_query($conn, $sql);

$events = [];
if ($result) {
    while ($event = mysqli_fetch_assoc($result)) {
        $events[$event['event_type']][] = $event;
    }
}
?>

<div class="container mt-4">
    <h2>Academic Calendar</h2>
    <p class="text-muted">Your important academic dates and events.</p>

    <div class="row g-3 mt-2">

        <?php foreach ($categories as $type => $heading): ?>
            <div class="col-md-6">
                <div class="card dashboard-card p-3 h-100">

                    <h5><?php echo htmlspecialchars($heading); ?></h5>

                    <?php if (!empty($events[$type])): ?>

                        <?php foreach ($events[$type] as $event): ?>
                            <div class="border rounded p-2 mt-2">
                                <strong>
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </strong>

                                <p class="mb-1">
                                    <strong>Date:</strong>
                                    <?php echo date("d M Y", strtotime($event['event_date'])); ?>
                                </p>

                                <p class="mb-0">
                                    <?php echo htmlspecialchars($event['description'] ?? ''); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <p class="text-muted mt-2 mb-0">
                            No upcoming events.
                        </p>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">
        Back to Dashboard
    </a>
</div>

<?php include "../includes/footer.php"; ?>
