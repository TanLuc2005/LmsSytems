<?php
// student/assignment_submit.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$assignment_id = intval($_GET['id'] ?? 0);
if (!$assignment_id) {
    echo 'Invalid assignment.';
    exit();
}

// Get assignment info

$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare('SELECT a.*, c.title as course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ?');
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    echo 'Assignment not found.';
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }
    if (empty($content)) {
        $errors[] = 'Please enter your submission.';
    }
    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, submitted_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE submission_text = VALUES(submission_text), submitted_at = NOW()');
        if ($stmt->execute([$assignment_id, $_SESSION['user_id'], $content])) {
            $success = 'Assignment submitted successfully!';
        } else {
            $errors[] = 'Error submitting assignment.';
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
    <title>Submit Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
        <div class="card shadow p-4 w-100" style="max-width: 600px;">
            <h2 class="mb-4 text-primary"><i class="fas fa-upload"></i> Submit Assignment</h2>
            <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item"><b><i class="fas fa-book"></i> Course:</b> <?= htmlspecialchars($assignment['course_title']) ?></li>
                <li class="list-group-item"><b><i class="fas fa-tasks"></i> Assignment:</b> <?= htmlspecialchars($assignment['title']) ?></li>
                <li class="list-group-item"><b><i class="fas fa-calendar-alt"></i> Due Date:</b> <?= htmlspecialchars($assignment['due_date']) ?></li>
            </ul>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo '<p class="mb-0">' . htmlspecialchars($e) . '</p>'; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><p class="mb-0"><?= $success ?></p></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="content" class="form-label fw-bold"><i class="fas fa-file-alt"></i> Your Submission:</label>
                    <textarea name="content" id="content" rows="6" class="form-control" required></textarea>
                </div>
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Submit Assignment</button>
                <a href="dashboard.php" class="btn btn-link text-decoration-none ms-2"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </form>
        </div>
    </div>
</body>
</html>
