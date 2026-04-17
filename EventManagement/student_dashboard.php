<?php require_once 'config.php';
if (!isLoggedIn() || !isStudent()) {
    header('Location: login.php');
    exit;
}

$studentId = $_SESSION['user_id'];
$message = '';

$eventsQuery = "SELECT e.event_id, e.title, e.date, e.capacity, e.available_seats,
    IF(r.registration_id IS NULL, 0, 1) AS registered
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = $studentId
    ORDER BY e.date ASC";
$events = $mysqli->query($eventsQuery);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <p class="text-muted">Browse events and register for available seats.</p>
        </div>
        <div>
            <a href="feedback.php" class="btn btn-outline-info me-2">Give Feedback</a>
            <a href="logout.php" class="btn btn-outline-secondary">Logout</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Available Events</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
                            <?php while ($row = $events->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['capacity']); ?></td>
                                    <td><?php echo htmlspecialchars($row['available_seats']); ?></td>
                                    <td>
                                        <?php if ($row['registered']): ?>
                                            <span class="badge bg-success">Registered</span>
                                        <?php elseif ($row['available_seats'] <= 0): ?>
                                            <span class="badge bg-danger">Full</span>
                                        <?php else: ?>
                                            <a href="register_event.php?event_id=<?php echo $row['event_id']; ?>" class="btn btn-sm btn-primary">Register</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No events available yet.</td>
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