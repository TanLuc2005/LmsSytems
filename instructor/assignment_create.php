<?php
// instructor/assignment_create.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get instructor's courses
$courses = [];
$stmt = $db->prepare('SELECT id, title FROM courses WHERE instructor_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }
    if (!$course_id || empty($title) || empty($due_date)) {
        $errors[] = 'Please fill in all required fields.';
    }
    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO assignments (course_id, title, description, due_date, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$course_id, $title, $description, $due_date, $_SESSION['user_id']]);
        if ($stmt) {
            $success = 'Assignment created successfully!';
        } else {
            $errors[] = 'Error creating assignment.';
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
    <title>Create Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h2><i class="fas fa-tasks"></i> Create New Assignment</h2>
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
                                <label class="form-label"><i class="fas fa-book"></i> Course</label>
                                <select name="course_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-file-alt"></i> Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                                <textarea name="description" rows="4" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-calendar-day"></i> Due Date</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus-circle"></i> Create Assignment</button>
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
