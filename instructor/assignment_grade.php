<?php
// instructor/assignment_grade.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$submission_id = intval($_GET['id'] ?? 0);
if (!$submission_id) {
    echo 'Invalid submission.';
    exit();
}

// Get submission info
$stmt = $conn->prepare('SELECT s.*, a.title as assignment_title, u.username as student_name FROM assignment_submissions s JOIN assignments a ON s.assignment_id = a.id JOIN users u ON s.student_id = u.id WHERE s.id = ?');
$stmt->bind_param('i', $submission_id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$submission) {
    echo 'Submission not found.';
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = floatval($_POST['grade'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }
    if ($grade < 0 || $grade > 10) {
        $errors[] = 'Grade must be between 0 and 10.';
    }
    if (!$errors) {
        $stmt = $conn->prepare('UPDATE assignment_submissions SET grade = ? WHERE id = ?');
        $stmt->bind_param('di', $grade, $submission_id);
        if ($stmt->execute()) {
            $success = 'Graded successfully!';
        } else {
            $errors[] = 'Error grading.';
        }
        $stmt->close();
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
    <title>Grade Assignment</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Grade Assignment</h2>
    <p><b>Student:</b> <?= htmlspecialchars($submission['student_name']) ?></p>
    <p><b>Assignment:</b> <?= htmlspecialchars($submission['assignment_title']) ?></p>
    <p><b>Submission:</b><br><?= nl2br(htmlspecialchars($submission['content'])) ?></p>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;"><p><?= $success ?></p></div>
    <?php endif; ?>
    <form method="post">
        <label>Grade (0-10): <input type="number" name="grade" min="0" max="10" step="0.1" value="<?= htmlspecialchars($submission['grade']) ?>" required></label><br>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit">Submit Grade</button>
    </form>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
