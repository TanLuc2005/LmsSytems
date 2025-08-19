<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';

$db = (new Database())->getConnection();

if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit();
}
$course_id = intval($_GET['id']);

// Delete course
$stmt = $db->prepare('DELETE FROM courses WHERE id = ?');
$stmt->execute([$course_id]);

header('Location: courses.php?msg=deleted');
exit();
