<?php
// instructor/course_create.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$errors = [];
$success = '';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $course_code = trim($_POST['course_code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }
    if (empty($title) || empty($course_code)) {
        $errors[] = 'Please fill in all fields.';
    }
    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO courses (title, course_code, description, instructor_id, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$title, $course_code, $description, $_SESSION['user_id']]);
        if ($stmt) {
            $success = 'Course created successfully!';
        } else {
            $errors[] = 'Error creating course.';
        }
    }
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2><i class="fas fa-plus-circle"></i> Create New Course</h2>
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
                                <label class="form-label"><i class="fas fa-book"></i> Course Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-code"></i> Course Code</label>
                                <input type="text" name="course_code" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                                <textarea name="description" rows="4" class="form-control"></textarea>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus-circle"></i> Create Course</button>
                        </form>
                        <p class="mt-3 text-center">
                            <a href="dashboard.php" class="text-primary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
