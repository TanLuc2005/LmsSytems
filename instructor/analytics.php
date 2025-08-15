<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

// Get stats
$stats = [
    'courses' => 0,
    'students' => 0,
    'assignments' => 0,
    'submissions' => 0
];

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare('SELECT COUNT(*) FROM courses WHERE instructor_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$stats['courses'] = $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.instructor_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$stats['students'] = $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.instructor_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$stats['assignments'] = $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM assignment_submissions s JOIN assignments a ON s.assignment_id = a.id JOIN courses c ON a.course_id = c.id WHERE c.instructor_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$stats['submissions'] = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Course Analytics</h2>
    <ul>
        <li>Total Courses: <?= $stats['courses'] ?></li>
        <li>Total Students: <?= $stats['students'] ?></li>
        <li>Total Assignments: <?= $stats['assignments'] ?></li>
        <li>Total Submissions: <?= $stats['submissions'] ?></li>
    </ul>
</body>
</html>
