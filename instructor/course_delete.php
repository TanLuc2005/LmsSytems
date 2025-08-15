<?php
// instructor/course_delete.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$course_id = intval($_GET['id'] ?? 0);
if (!$course_id) {
    echo 'Invalid course.';
    exit();
}

// Check ownership
$stmt = $conn->prepare('SELECT * FROM courses WHERE id = ? AND instructor_id = ?');
$stmt->bind_param('ii', $course_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    echo 'Not found or you do not have permission to delete this course.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Delete course
    $stmt = $conn->prepare('DELETE FROM courses WHERE id = ? AND instructor_id = ?');
    $stmt->bind_param('ii', $course_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Delete Course</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Delete Course</h2>
    <p>Are you sure you want to delete the course <b><?= htmlspecialchars($course['title']) ?></b>?</p>
    <form method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit">Confirm Delete</button>
        <a href="dashboard.php">Cancel</a>
    </form>
</body>
</html>
