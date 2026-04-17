<?php require_once 'config.php';
if (!isLoggedIn() || !isStudent()) {
    header('Location: login.php');
    exit;
}

$eventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
if ($eventId <= 0) {
    header('Location: student_dashboard.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

$eventResult = $mysqli->query("SELECT event_id, title, date, available_seats FROM events WHERE event_id = $eventId LIMIT 1");
$event = $eventResult ? $eventResult->fetch_assoc() : null;
if (!$event) {
    header('Location: student_dashboard.php');
    exit;
}

$registrationCheck = $mysqli->query("SELECT registration_id FROM registrations WHERE user_id = $userId AND event_id = $eventId LIMIT 1");
if ($registrationCheck && $registrationCheck->num_rows > 0) {
    $message = 'You are already registered for this event.';
} elseif ($event['available_seats'] <= 0) {
    $message = 'This event is full.';
} else {
    $mysqli->query("INSERT INTO registrations (user_id, event_id) VALUES ($userId, $eventId)");
    $mysqli->query("UPDATE events SET available_seats = available_seats - 1 WHERE event_id = $eventId");
    $message = 'Registration successful. Good luck at the event!';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register for Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Register for Event</h3>
                    <p><strong>Event:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                    <p><strong>Seats left:</strong> <?php echo htmlspecialchars($event['available_seats']); ?></p>

                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                    <a href="student_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>