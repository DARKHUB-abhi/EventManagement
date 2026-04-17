<?php require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;
$reportData = null;

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $selectedEventId) {
    $exportQuery = "SELECT u.name, u.email, e.title as event_title, e.date as event_date,
                           COALESCE(f.rating, '') as feedback_rating,
                           COALESCE(f.comment, '') as feedback_comment,
                           COALESCE(f.submitted_at, '') as feedback_date
                           FROM users u
                           INNER JOIN registrations r ON u.user_id = r.user_id
                           LEFT JOIN feedback f ON u.user_id = f.user_id AND f.event_id = r.event_id
                           INNER JOIN events e ON r.event_id = e.event_id
                           WHERE r.event_id = $selectedEventId
                           ORDER BY u.name ASC";
    $exportResult = $mysqli->query($exportQuery);

    if ($exportResult && $exportResult->num_rows > 0) {
        // Get event info for filename
        $eventInfoQuery = "SELECT title, date FROM events WHERE event_id = $selectedEventId";
        $eventInfo = $mysqli->query($eventInfoQuery)->fetch_assoc();

        $filename = 'attendance_report_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $eventInfo['title']) . '_' . $eventInfo['date'] . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['Student Name', 'Email', 'Event Title', 'Event Date', 'Feedback Rating', 'Feedback Comment', 'Feedback Date']);

        // CSV data
        while ($row = $exportResult->fetch_assoc()) {
            fputcsv($output, [
                $row['name'],
                $row['email'],
                $row['event_title'],
                $row['event_date'],
                $row['feedback_rating'],
                $row['feedback_comment'],
                $row['feedback_date']
            ]);
        }

        fclose($output);
        exit;
    }
}

if ($selectedEventId) {
    // Get event details
    $eventQuery = "SELECT * FROM events WHERE event_id = $selectedEventId";
    $eventResult = $mysqli->query($eventQuery);
    $event = $eventResult->fetch_assoc();

    if ($event) {
        // Get registered students with feedback
        $attendanceQuery = "SELECT u.user_id, u.name, u.email, r.registration_id,
                           COALESCE(f.rating, 0) as feedback_rating,
                           f.comment as feedback_comment,
                           f.submitted_at as feedback_date
                           FROM users u
                           INNER JOIN registrations r ON u.user_id = r.user_id
                           LEFT JOIN feedback f ON u.user_id = f.user_id AND f.event_id = r.event_id
                           WHERE r.event_id = $selectedEventId
                           ORDER BY u.name ASC";
        $attendance = $mysqli->query($attendanceQuery);

        // Get attendance statistics
        $statsQuery = "SELECT
            COUNT(*) as total_registered,
            COUNT(CASE WHEN f.rating IS NOT NULL THEN 1 END) as feedback_provided,
            AVG(CASE WHEN f.rating IS NOT NULL THEN f.rating END) as avg_rating
            FROM registrations r
            LEFT JOIN feedback f ON r.user_id = f.user_id AND r.event_id = f.event_id
            WHERE r.event_id = $selectedEventId";
        $stats = $mysqli->query($statsQuery)->fetch_assoc();

        $reportData = [
            'event' => $event,
            'attendance' => $attendance,
            'stats' => $stats
        ];
    }
}

// Get all events for the dropdown
$eventsQuery = "SELECT event_id, title, date, capacity, (capacity - available_seats) as registered_count
                FROM events ORDER BY date DESC";
$events = $mysqli->query($eventsQuery);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <script>
        function printReport() {
            window.print();
        }
    </script>
    <style>
        @media print {
            .no-print { display: none; }
            .card { border: 1px solid #000 !important; }
            body { background: white !important; }
            .container { background: white !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Attendance Report</h2>
            <p class="text-muted">Generate detailed attendance reports for events.</p>
        </div>
        <div>
            <button onclick="printReport()" class="btn btn-outline-secondary me-2 no-print">Print Report</button>
            <?php if ($reportData && $reportData['attendance'] && $reportData['attendance']->num_rows > 0): ?>
                <a href="?event_id=<?php echo $selectedEventId; ?>&export=csv" class="btn btn-outline-primary me-2 no-print">Export CSV</a>
            <?php endif; ?>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary no-print">Back to Dashboard</a>
        </div>
    </div>

    <!-- Event Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Select Event</h5>
            <form method="get" class="row g-3">
                <div class="col-md-8">
                    <select name="event_id" class="form-select" required>
                        <option value="">Choose an event...</option>
                        <?php if ($events && $events->num_rows > 0): ?>
                            <?php while ($event = $events->fetch_assoc()): ?>
                                <option value="<?php echo $event['event_id']; ?>"
                                        <?php echo ($selectedEventId == $event['event_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($event['title']); ?> -
                                    <?php echo htmlspecialchars($event['date']); ?> -
                                    <?php echo $event['registered_count']; ?>/<?php echo $event['capacity']; ?> registered
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($reportData): ?>
        <!-- Report Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($reportData['event']['title']); ?></h4>
                        <p class="mb-1"><strong>Date:</strong> <?php echo htmlspecialchars($reportData['event']['date']); ?></p>
                        <p class="mb-1"><strong>Capacity:</strong> <?php echo htmlspecialchars($reportData['event']['capacity']); ?> seats</p>
                        <p class="mb-0"><strong>Generated on:</strong> <?php echo date('F j, Y \a\t g:i A'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <div class="text-end">
                            <div class="h5 text-primary"><?php echo $reportData['stats']['total_registered']; ?></div>
                            <div class="text-muted">Total Registered</div>
                            <div class="h6 text-success mt-2"><?php echo $reportData['stats']['feedback_provided']; ?></div>
                            <div class="text-muted small">Feedback Provided</div>
                            <?php if ($reportData['stats']['avg_rating']): ?>
                                <div class="h6 text-info mt-2"><?php echo number_format($reportData['stats']['avg_rating'], 1); ?> ★</div>
                                <div class="text-muted small">Average Rating</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Details -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Registered Students</h5>
                <?php if ($reportData['attendance'] && $reportData['attendance']->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Registration Status</th>
                                    <th>Feedback Rating</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $reportData['attendance']->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td>
                                            <span class="badge bg-success">Registered</span>
                                        </td>
                                        <td>
                                            <?php if ($student['feedback_rating'] > 0): ?>
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                    $rating = (int)$student['feedback_rating'];
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo $i <= $rating ? '★' : '☆';
                                                    }
                                                    ?>
                                                    <span class="ms-2">(<?php echo $rating; ?>/5)</span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['feedback_comment']): ?>
                                                <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($student['feedback_comment']); ?>">
                                                    <?php echo htmlspecialchars($student['feedback_comment']); ?>
                                                </div>
                                            <?php else: ?>
                                                <em class="text-muted">No comments</em>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No students have registered for this event yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($selectedEventId): ?>
        <div class="alert alert-warning">
            Event not found or no data available.
        </div>
    <?php endif; ?>
</div>
</body>
</html>