<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    if ($course_id) {
        $stmt = $db->prepare('INSERT IGNORE INTO enrollments (student_id, course_id, status) VALUES (?, ?, "enrolled")');
        $stmt->execute([$_SESSION['user_id'], $course_id]);
        $success = 'Enrolled successfully!';
    } else {
        $errors[] = 'Please select a course.';
    }
}

// List all courses not yet enrolled
$stmt = $db->prepare('SELECT * FROM courses WHERE id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?)');
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enroll in Course</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Enroll in a Course</h2>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;"><p><?= $success ?></p></div>
    <?php endif; ?>
    <form method="post">
        <label>Course:
            <select name="course_id" required>
                <option value="">--Select--</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Enroll</button>
    </form>
    <p><a href="my_courses.php">Back to My Courses</a></p>
</body>
</html>
