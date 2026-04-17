<?php require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_event_id'])) {
        $deleteId = (int) $_POST['delete_event_id'];
        $mysqli->query("DELETE FROM registrations WHERE event_id = $deleteId");
        $mysqli->query("DELETE FROM events WHERE event_id = $deleteId");
        header('Location: admin_dashboard.php');
        exit;
    }

    $title = $mysqli->real_escape_string(trim($_POST['title'] ?? ''));
    $date = $mysqli->real_escape_string(trim($_POST['date'] ?? ''));
    $capacity = (int) ($_POST['capacity'] ?? 0);

    if ($title === '' || $date === '' || $capacity <= 0) {
        $message = 'Please enter valid event details.';
    } else {
        $mysqli->query("INSERT INTO events (title, date, capacity, available_seats) VALUES ('$title', '$date', $capacity, $capacity)");
        $message = 'Event created successfully.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Create Event</h3>
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <form method="post" action="create_event.php">
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Event</button>
                        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>