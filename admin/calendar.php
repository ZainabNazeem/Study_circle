
<?php
include "../includes/auth.php";
include "../config/db.php";
requireAdmin();

$message = "";
$messageType = "success";

$eventTypes = [
    "Exam",
    "Assignment",
    "Academic Event",
    "Important Date"
];

// ADD, UPDATE, DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'add' || $action === 'update') {

        $title = trim($_POST['title'] ?? '');
        $eventType = $_POST['event_type'] ?? '';
        $eventDate = $_POST['event_date'] ?? '';
        $description = trim($_POST['description'] ?? '');

        $dateParts = explode('-', $eventDate);
        $validDate = count($dateParts) === 3
            && checkdate(
                (int)$dateParts[1],
                (int)$dateParts[2],
                (int)$dateParts[0]
            );

        if ($title === '' || !in_array($eventType, $eventTypes)
            || !$validDate) {

            $message = "Please enter valid event details.";
            $messageType = "danger";

        } else {

            if ($action === 'add') {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO academic_events
                    (title, event_type, event_date, description)
                    VALUES (?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt, "ssss",
                    $title, $eventType, $eventDate, $description
                );

            } else {

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE academic_events
                    SET title=?, event_type=?, event_date=?, description=?
                    WHERE id=?"
                );

                mysqli_stmt_bind_param(
                    $stmt, "ssssi",
                    $title, $eventType, $eventDate, $description, $id
                );
            }

            if (mysqli_stmt_execute($stmt)) {
                $message = $action === 'add'
                    ? "Event added successfully."
                    : "Event updated successfully.";
            } else {
                $message = "Could not save the event.";
                $messageType = "danger";
            }

            mysqli_stmt_close($stmt);
        }
    }

    if ($action === 'delete') {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM academic_events WHERE id=?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Event deleted successfully.";
        } else {
            $message = "Could not delete the event.";
            $messageType = "danger";
        }

        mysqli_stmt_close($stmt);
    }
}

// LOAD EVENT FOR EDITING
$editEvent = null;

if (isset($_GET['edit'])) {

    $editId = (int)$_GET['edit'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM academic_events WHERE id=?"
    );

    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);

    $editEvent = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);
}

// DISPLAY ALL EVENTS
$events = mysqli_query(
    $conn,
    "SELECT * FROM academic_events ORDER BY event_date ASC"
);

$page_title = "Manage Academic Calendar";
include "../includes/header.php";
?>

<style>
.admin-card {
    border-top: 4px solid var(--nsbm-green);
}
</style>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Academic Calendar</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- ADD / EDIT EVENT FORM -->
    <div class="card admin-card mb-4">
        <div class="card-body">

            <h5>
                <?php echo $editEvent ? "Edit Event" : "Add New Event"; ?>
            </h5>

            <form method="post" action="calendar.php" class="row g-3">

                <input type="hidden"
                       name="action"
                       value="<?php echo $editEvent ? 'update' : 'add'; ?>">

                <?php if ($editEvent): ?>
                    <input type="hidden" name="id"
                           value="<?php echo (int)$editEvent['id']; ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="title" class="form-control"
                           value="<?php echo htmlspecialchars($editEvent['title'] ?? ''); ?>"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Event Type</label>
                    <select name="event_type" class="form-select" required>
                        <option value="">Select Category</option>

                        <?php foreach ($eventTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"
                                <?php echo (($editEvent['event_type'] ?? '') === $type) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-control"
                           value="<?php echo htmlspecialchars($editEvent['event_date'] ?? ''); ?>"
                           required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"
                              rows="3"><?php echo htmlspecialchars($editEvent['description'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editEvent ? "Save Changes" : "Add Event"; ?>
                    </button>

                    <?php if ($editEvent): ?>
                        <a href="calendar.php"
                           class="btn btn-outline-secondary">Cancel</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>

    <!-- EVENT LIST -->
    <div class="card admin-card">
        <div class="card-body">

            <h5 class="mb-3">All Academic Events</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($events && mysqli_num_rows($events) > 0): ?>

                            <?php while ($event = mysqli_fetch_assoc($events)): ?>

                                <tr>
                                    <td><?php echo (int)$event['id']; ?></td>

                                    <td><?php echo htmlspecialchars($event['title']); ?></td>

                                    <td><?php echo htmlspecialchars($event['event_type']); ?></td>

                                    <td>
                                        <?php echo date("d M Y", strtotime($event['event_date'])); ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($event['description'] ?? ''); ?></td>

                                    <td>
                                        <a href="calendar.php?edit=<?php echo (int)$event['id']; ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <form method="post" action="calendar.php"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this event?');">

                                            <input type="hidden"
                                                   name="action"
                                                   value="delete">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?php echo (int)$event['id']; ?>">

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="text-center">
                                    No academic events found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
