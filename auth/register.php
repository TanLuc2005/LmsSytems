<?php
// auth/register.php
session_start();
require_once '../config/database.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = 'student';

    // Validate
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    $database = new Database();
    $db = $database->getConnection();

    // Check if username or email exists
    if (!$errors) {
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $errors[] = 'Username or email already exists.';
        }
    }

    // Insert user
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$username, $email, $hash, $role])) {
            $success = 'Registration successful! You can now log in.';
        } else {
            $errors[] = 'Error during registration.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<style>
    body {
        min-height: 100vh;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        background-attachment: fixed;
    }
</style>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2><i class="fas fa-user-plus"></i> Register Account</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= $success ?>
                            </div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-user"></i> Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <!-- Role is now fixed to Student and hidden from the form -->
                            <input type="hidden" name="role" value="student">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-user-plus"></i> Register</button>
                        </form>
                        <p class="mt-3 text-center">
                            <a href="login.php" class="text-primary"><i class="fas fa-sign-in-alt"></i> Already have an account? Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
