<?php
// instructor/course_edit.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$course_id = intval($_GET['id'] ?? 0);
if (!$course_id) {
    echo 'Invalid course.';
    exit();
}

// Get course info, only allow owner instructor
$stmt = $db->prepare('SELECT * FROM courses WHERE id = ? AND instructor_id = ?');
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo 'Not found or you do not have permission to edit this course.';
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $course_code = trim($_POST['course_code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    $errors[] = 'Invalid CSRF token.';
    }
    if (empty($title) || empty($course_code)) {
    $errors[] = 'Please fill in all required fields.';
    }
    if (!$errors) {
        $stmt = $db->prepare('UPDATE courses SET title = ?, course_code = ?, description = ? WHERE id = ? AND instructor_id = ?');
        $stmt->execute([$title, $course_code, $description, $course_id, $_SESSION['user_id']]);
    $success = 'Course updated successfully!';
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
    <title>Edit Course</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Edit Course</h2>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;"><p><?= $success ?></p></div>
    <?php endif; ?>
    <form method="post">
        <label>Course Title: <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" required></label><br>
        <label>Course Code: <input type="text" name="course_code" value="<?= htmlspecialchars($course['course_code']) ?>" required></label><br>
        <label>Description:<br>
            <textarea name="description" rows="4" cols="50"><?= htmlspecialchars($course['description']) ?></textarea>
        </label><br>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit">Update</button>
    </form>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
