<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$course_id = intval($_GET['id'] ?? 0);
if ($course_id) {
    $stmt = $db->prepare('DELETE FROM enrollments WHERE student_id = ? AND course_id = ?');
    $stmt->execute([$_SESSION['user_id'], $course_id]);
}
header('Location: my_courses.php');
exit();
