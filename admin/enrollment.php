<?php

// admin/enrollment.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'instructor'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get students
$students = [];
$result = $conn->query("SELECT id, username FROM users WHERE role = 'student'");
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Get courses
$courses = [];
if ($_SESSION['role'] === 'admin') {
    $result = $conn->query('SELECT id, title FROM courses');
} else {
    $stmt = $conn->prepare('SELECT id, title FROM courses WHERE instructor_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
}
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $course_id = intval($_POST['course_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }
    if (!$student_id || !$course_id) {
        $errors[] = 'Please select student and course.';
    }
    if (!$errors) {
        if ($action === 'enroll') {
            // Enroll
            $stmt = $conn->prepare('INSERT IGNORE INTO enrollments (student_id, course_id, status) VALUES (?, ?, "enrolled")');
            $stmt->bind_param('ii', $student_id, $course_id);
            if ($stmt->execute()) {
                $success = 'Enrollment successful!';
            } else {
                $errors[] = 'Error enrolling.';
            }
            $stmt->close();
        } elseif ($action === 'unenroll') {
            // Unenroll
            $stmt = $conn->prepare('DELETE FROM enrollments WHERE student_id = ? AND course_id = ?');
            $stmt->bind_param('ii', $student_id, $course_id);
            if ($stmt->execute()) {
                $success = 'Unenrolled successfully!';
            } else {
                $errors[] = 'Error unenrolling.';
            }
            $stmt->close();
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
    <title>Enrollment Management</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Enrollment Management</h2>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;"><p><?= $success ?></p></div>
    <?php endif; ?>
    <form method="post">
        <label>Student:
            <select name="student_id" required>
                <option value="">--Select--</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Course:
            <select name="course_id" required>
                <option value="">--Select--</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit" name="action" value="enroll">Enroll</button>
        <button type="submit" name="action" value="unenroll">Unenroll</button>
    </form>
    <p><a href="../instructor/dashboard.php">Back to Dashboard</a></p>
</body>
</html>
