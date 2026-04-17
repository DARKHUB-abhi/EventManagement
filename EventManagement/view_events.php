<?php require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$registrations = $mysqli->query("SELECT r.registration_id, u.name AS student_name, u.email, e.title AS event_title, e.date
    FROM registrations r
    JOIN users u ON r.user_id = u.user_id
    JOIN events e ON r.event_id = e.event_id
    ORDER BY e.date ASC, u.name ASC");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Event Registrations</h2>
            <p class="text-muted">Review students who signed up for events.</p>
        </div>
        <div>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($registrations && $registrations->num_rows > 0): ?>
                            <?php while ($row = $registrations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No registrations yet.</td>
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