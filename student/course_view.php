<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">Invalid course ID.</div>';
    exit();
}

$course_id = (int)$_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Check if student is enrolled in this course
$stmt = $db->prepare('SELECT c.*, u.first_name, u.last_name, e.enrollment_date FROM courses c JOIN enrollments e ON c.id = e.course_id AND e.student_id = ? JOIN users u ON c.instructor_id = u.id WHERE c.id = ?');
$stmt->execute([$_SESSION['user_id'], $course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo '<div class="alert alert-danger">Course not found or you are not enrolled in this course.</div>';
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-book"></i> <?php echo htmlspecialchars($course['title']); ?></h1>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Course Code: <?php echo htmlspecialchars($course['course_code']); ?></h5>
                        <p class="card-text">Instructor: <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
                        <p class="card-text">Description: <?php echo htmlspecialchars($course['description']); ?></p>
                        <p class="card-text">Enrolled on: <?php echo formatDate($course['enrollment_date']); ?></p>
                    </div>
                </div>
                <!-- You can add more course details, assignments, materials, etc. here -->
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
