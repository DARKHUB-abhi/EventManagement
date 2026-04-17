<?php require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $mysqli->real_escape_string(trim($_POST['name'] ?? ''));
    $email = $mysqli->real_escape_string(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please use a valid email address.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } else {
        $check = $mysqli->query("SELECT user_id FROM users WHERE email = '$email' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $message = 'Email is already registered.';
        } else {
                $role = 'student';
            $insert = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
            if ($mysqli->query($insert)) {
                header('Location: login.php');
                exit;
            }
            $message = 'Registration failed. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - College Event Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Student Registration</h3>
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <form method="post" action="register.php">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a href="login.php" class="btn btn-link">Already have an account?</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>