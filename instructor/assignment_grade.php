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


$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare('SELECT s.*, a.title as assignment_title, u.username as student_name FROM assignment_submissions s JOIN assignments a ON s.assignment_id = a.id JOIN users u ON s.student_id = u.id WHERE s.id = ?');
$stmt->execute([$submission_id]);
$submission = $stmt->fetch();
if (!$submission) {
    echo 'Submission not found.';
    exit();
}

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
        $stmt = $db->prepare('UPDATE assignment_submissions SET grade = ? WHERE id = ?');
        if ($stmt->execute([$grade, $submission_id])) {
            $success = 'Graded successfully!';
        } else {
            $errors[] = 'Error grading.';
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
    <title>Grade Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body { background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-0"><i class="fas fa-marker"></i> Grade Assignment</h2>
                    </div>
                    <div class="card-body">
                        <p><b><i class="fas fa-user"></i> Student:</b> <?= htmlspecialchars($submission['student_name']) ?></p>
                        <p><b><i class="fas fa-file-alt"></i> Assignment:</b> <?= htmlspecialchars($submission['assignment_title']) ?></p>
                        <p><b><i class="fas fa-file-signature"></i> Submission:</b><br>
                            <?php 
                            $submission_text = trim($submission['submission_text'] ?? '');
                            $file_path = $submission['file_path'] ?? '';
                            if ($submission_text) {
                                echo nl2br(htmlspecialchars($submission_text));
                            } elseif ($file_path) {
                                $file_name = basename($file_path);
                                echo '<a href="' . htmlspecialchars($file_path) . '" class="btn btn-outline-info" download><i class="fas fa-download"></i> ' . htmlspecialchars($file_name) . '</a>';
                            } else {
                                echo '<span class="badge bg-secondary">No submission</span>';
                            }
                            ?>
                        </p>
                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
                        <?php endif; ?>
                        <form method="post" class="mt-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-star"></i> Grade (0-10):</label>
                                <input type="number" name="grade" min="0" max="10" step="0.1" value="<?= htmlspecialchars($submission['grade'] ?? '') ?>" class="form-control w-50 d-inline-block" required>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Submit Grade</button>
                            <a href="dashboard.php" class="btn btn-secondary ms-2"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
