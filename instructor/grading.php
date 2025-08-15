<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare('SELECT s.*, a.title as assignment_title, u.username as student_name FROM assignment_submissions s JOIN assignments a ON s.assignment_id = a.id JOIN users u ON s.student_id = u.id JOIN courses c ON a.course_id = c.id WHERE c.instructor_id = ? AND s.grade IS NULL');
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grading</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Pending Grading</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Student</th>
            <th>Assignment</th>
            <th>Submission</th>
            <th>Action</th>
        </tr>
        <?php foreach ($result as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['assignment_title']) ?></td>
            <td><?= htmlspecialchars($row['content']) ?></td>
            <td>
                <a href="assignment_grade.php?id=<?= $row['id'] ?>">Grade</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
