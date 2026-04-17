<?php require_once 'config.php';
if (!isLoggedIn() || !isStudent()) {
    header('Location: login.php');
    exit;
}

$studentId = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = (int)$_POST['event_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $message = '<div class="alert alert-danger">Please select a valid rating (1-5 stars).</div>';
    } else {
        // Check if student is registered for this event and event has passed
        $checkQuery = "SELECT e.event_id, e.title, e.date
                      FROM events e
                      INNER JOIN registrations r ON e.event_id = r.event_id
                      WHERE r.user_id = $studentId AND e.event_id = $eventId AND e.date <= CURDATE()";
        $checkResult = $mysqli->query($checkQuery);

        if ($checkResult && $checkResult->num_rows > 0) {
            $event = $checkResult->fetch_assoc();

            // Insert or update feedback
            $stmt = $mysqli->prepare("INSERT INTO feedback (user_id, event_id, rating, comment)
                                    VALUES (?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)");
            $stmt->bind_param("iiis", $studentId, $eventId, $rating, $comment);

            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Thank you for your feedback!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error submitting feedback. Please try again.</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger">You can only provide feedback for events you\'ve registered for and that have already occurred.</div>';
        }
    }
}

// Get events student can provide feedback for (registered and past events)
$eventsQuery = "SELECT e.event_id, e.title, e.date,
                COALESCE(f.rating, 0) as current_rating,
                f.comment as current_comment
                FROM events e
                INNER JOIN registrations r ON e.event_id = r.event_id
                LEFT JOIN feedback f ON e.event_id = f.event_id AND f.user_id = $studentId
                WHERE r.user_id = $studentId AND e.date <= CURDATE()
                ORDER BY e.date DESC";
$events = $mysqli->query($eventsQuery);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Event Feedback</h2>
            <p class="text-muted">Share your experience with events you've attended.</p>
        </div>
        <div>
            <a href="student_dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>

    <?php echo $message; ?>

    <?php if ($events && $events->num_rows > 0): ?>
        <div class="row">
            <?php while ($event = $events->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted">Date: <?php echo htmlspecialchars($event['date']); ?></small>
                            </p>

                            <form method="post">
                                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Rating (1-5 stars)</label>
                                    <div class="star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?php echo $i; ?>_<?php echo $event['event_id']; ?>"
                                                   name="rating" value="<?php echo $i; ?>"
                                                   <?php echo ($event['current_rating'] == $i) ? 'checked' : ''; ?>>
                                            <label for="star<?php echo $i; ?>_<?php echo $event['event_id']; ?>">&#9733;</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="comment_<?php echo $event['event_id']; ?>" class="form-label">Comments (optional)</label>
                                    <textarea class="form-control" id="comment_<?php echo $event['event_id']; ?>"
                                              name="comment" rows="3"
                                              placeholder="Share your thoughts about this event..."><?php echo htmlspecialchars($event['current_comment'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">No Feedback Available</h5>
                <p class="card-text">You haven't attended any events yet, or no past events are available for feedback.</p>
                <a href="student_dashboard.php" class="btn btn-primary">Browse Events</a>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>