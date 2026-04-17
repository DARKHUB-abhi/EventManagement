<?php require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$events = $mysqli->query("SELECT event_id, title, date, capacity, available_seats FROM events ORDER BY date ASC");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Admin Panel</h2>
            <p class="text-muted">Create and manage events.</p>
        </div>
        <div>
            <a href="create_event.php" class="btn btn-primary me-2">Create Event</a>
            <a href="view_events.php" class="btn btn-outline-secondary me-2">View Registrations</a>
            <a href="attendance_report.php" class="btn btn-outline-success me-2">Attendance Reports</a>
            <a href="view_feedback.php" class="btn btn-outline-info me-2">View Feedback</a>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Current Events</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Capacity</th>
                            <th>Seats left</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($events && $events->num_rows > 0): ?>
                            <?php while ($event = $events->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo htmlspecialchars($event['date']); ?></td>
                                    <td><?php echo htmlspecialchars($event['capacity']); ?></td>
                                    <td><?php echo htmlspecialchars($event['available_seats']); ?></td>
                                    <td>
                                        <form method="post" action="create_event.php" class="d-inline">
                                            <input type="hidden" name="delete_event_id" value="<?php echo $event['event_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No events have been created yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>