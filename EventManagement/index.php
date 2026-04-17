<?php require_once 'config.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>College Event Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-4">
        <h1>College Event Registration</h1>
        <p class="text-muted">Student registration and admin event management.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (isLoggedIn()): ?>
                <div class="alert alert-success">
                    Logged in as <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>.
                </div>
            <?php endif; ?>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Get Started</h5>
                    <p class="card-text">Use the links below to register, login, or manage events.</p>
                    <a href="login.php" class="btn btn-primary me-2">Login</a>
                    <a href="register.php" class="btn btn-outline-primary">Register</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Available Actions</h5>
                    <ul>
                        <li>Students: Register, view events, and sign up for events.</li>
                        <li>Admins: Create events, delete events, and view registrations.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>