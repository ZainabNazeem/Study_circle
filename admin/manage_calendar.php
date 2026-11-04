<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

$message = "";
$message_type = "success";

// 1. Handle Adding an Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = trim($_POST['title']);
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $description = trim($_POST['description']);

    if ($title !== "" && $event_date !== "") {
        $stmt = $conn->prepare("INSERT INTO academic_events (title, event_type, event_date, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $event_type, $event_date, $description);
        if ($stmt->execute()) {
            $message = "Event added successfully!";
        } else {
            $message = "Failed to add event.";
            $message_type = "danger";
        }
    } else {
        $message = "Title and Date are required.";
        $message_type = "danger";
    }
}

// 2. Handle Deleting an Event
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM academic_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "Event deleted.";
}

// 3. Fetch all events
$events = $conn->query("SELECT * FROM academic_events ORDER BY event_date ASC");

$page_title = "Manage Calendar";
include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Manage Academic Calendar</h2>
    <p class="text-muted">Add or remove events that will show up on the student calendar.</p>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Add Event Form -->
    <div class="card p-4 mb-4">
        <h4>Add New Event</h4>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Event Title *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Event Type *</label>
                    <select name="event_type" class="form-select" required>
                        <option value="Exam">Exam</option>
                        <option value="Assignment">Assignment</option>
                        <option value="Academic Event">Academic Event</option>
                        <option value="Important Date">Important Date</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date *</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                <div class="col-12 mt-3">
                    <label class="form-label">Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <button type="submit" name="add_event" class="btn btn-primary mt-3">Add Event</button>
        </form>
    </div>

    <!-- Existing Events Table -->
    <h4>Current Events</h4>
    <div class="table-responsive">
        <table class="table table-bordered bg-white">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events->num_rows === 0): ?>
                    <tr><td colspan="5" class="text-center">No events added yet.</td></tr>
                <?php else: ?>
                    <?php while ($row = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($row['event_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['event_type']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                            <td>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <a href="dashboard.php" class="btn btn-secondary mt-3 mb-5">Back to Admin Dashboard</a>
</div>

<?php include "../includes/footer.php"; ?>