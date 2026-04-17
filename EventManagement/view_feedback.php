<?php require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

// Get feedback with event and user details
$feedbackQuery = "SELECT f.feedback_id, f.rating, f.comment, f.submitted_at,
                         e.title as event_title, e.date as event_date,
                         u.name as student_name, u.email as student_email
                  FROM feedback f
                  INNER JOIN events e ON f.event_id = e.event_id
                  INNER JOIN users u ON f.user_id = u.user_id
                  ORDER BY f.submitted_at DESC";
$feedback = $mysqli->query($feedbackQuery);

// Get feedback statistics
$statsQuery = "SELECT
    COUNT(*) as total_feedback,
    AVG(rating) as avg_rating,
    COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_feedback,
    COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_feedback
    FROM feedback";
$stats = $mysqli->query($statsQuery)->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Student Feedback</h2>
            <p class="text-muted">View and analyze feedback from event participants.</p>
        </div>
        <div>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>

    <!-- Feedback Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?php echo $stats['total_feedback'] ?? 0; ?></h3>
                    <p class="card-text">Total Feedback</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?php echo $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '0.0'; ?></h3>
                    <p class="card-text">Average Rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?php echo $stats['positive_feedback'] ?? 0; ?></h3>
                    <p class="card-text">Positive (4-5 stars)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?php echo $stats['negative_feedback'] ?? 0; ?></h3>
                    <p class="card-text">Needs Improvement (1-2 stars)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">All Feedback</h5>
            <?php if ($feedback && $feedback->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Student</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $feedback->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['event_title']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['event_date']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['student_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['student_email']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $rating = (int)$row['rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rating ? '★' : '☆';
                                            }
                                            ?>
                                            <span class="ms-2">(<?php echo $rating; ?>/5)</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row['comment']): ?>
                                            <?php echo htmlspecialchars($row['comment']); ?>
                                        <?php else: ?>
                                            <em class="text-muted">No comment provided</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($row['submitted_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted">No feedback has been submitted yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>