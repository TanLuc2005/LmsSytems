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
    <title>Course Analytics</title>
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
            <div class="col-lg-8">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-success text-white text-center">
                        <h2 class="mb-0"><i class="fas fa-chart-bar"></i> Course Analytics</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="card border-primary shadow-sm text-center">
                                    <div class="card-body">
                                        <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                        <h5 class="card-title">Courses</h5>
                                        <p class="display-6 fw-bold text-primary mb-0"><?= $stats['courses'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="card border-info shadow-sm text-center">
                                    <div class="card-body">
                                        <i class="fas fa-users fa-2x text-info mb-2"></i>
                                        <h5 class="card-title">Students</h5>
                                        <p class="display-6 fw-bold text-info mb-0"><?= $stats['students'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="card border-warning shadow-sm text-center">
                                    <div class="card-body">
                                        <i class="fas fa-tasks fa-2x text-warning mb-2"></i>
                                        <h5 class="card-title">Assignments</h5>
                                        <p class="display-6 fw-bold text-warning mb-0"><?= $stats['assignments'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="card border-success shadow-sm text-center">
                                    <div class="card-body">
                                        <i class="fas fa-file-upload fa-2x text-success mb-2"></i>
                                        <h5 class="card-title">Submissions</h5>
                                        <p class="display-6 fw-bold text-success mb-0"><?= $stats['submissions'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
