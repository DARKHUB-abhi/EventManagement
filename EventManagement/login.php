<?php require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $mysqli->real_escape_string(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $message = 'Please enter both email and password.';
    } else {
        $query = "SELECT user_id, name, email, password, role FROM users WHERE email = '$email' LIMIT 1";
        $result = $mysqli->query($query);

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                    exit;
                }

                header('Location: student_dashboard.php');
                exit;
            }
        }
        $message = 'Invalid email or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - College Event Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Login</h3>
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="register.php" class="btn btn-link">Register as student</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>