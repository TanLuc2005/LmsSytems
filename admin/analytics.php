<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get total users
$stmt = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total students
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total instructors
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'instructor'");
$total_instructors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total courses

$stmt = $conn->query("SELECT COUNT(*) as total FROM courses");
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total assignments

$stmt = $conn->query("SELECT COUNT(*) as total FROM assignments");
$total_assignments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total messages

$stmt = $conn->query("SELECT COUNT(*) as total FROM messages");
$total_messages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total submissions

$stmt = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions");
$total_submissions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .card { border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .icon { font-size: 2.5rem; }
        .analytics-title { font-weight: bold; font-size: 2rem; margin-bottom: 2rem; }
    </style>
</head>
<body>
<?php include '../includes/admin_navbar.php'; ?>
<div class="container py-5">
    <div class="analytics-title text-center mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>Admin Analytics Dashboard</div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-bg-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-users icon me-3"></i>
                    <div>
                        <h5 class="card-title mb-0">Total Users</h5>
                        <h2 class="mb-0"><?php echo $total_users; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-user-graduate icon me-3"></i>
                    <div>
                        <h5 class="card-title mb-0">Total Students</h5>
                        <h2 class="mb-0"><?php echo $total_students; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-warning h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-chalkboard-teacher icon me-3"></i>
                    <div>
                        <h5 class="card-title mb-0">Total Instructors</h5>
                        <h2 class="mb-0"><?php echo $total_instructors; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-info h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-book icon me-3"></i>
                    <div>
                        <h5 class="card-title mb-0">Total Courses</h5>
                        <h2 class="mb-0"><?php echo $total_courses; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-secondary h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-tasks icon me-3"></i>
                    <div>
                        <h5 class="card-title mb-0">Total Assignments</h5>
                        <h2 class="mb-0"><?php echo $total_assignments; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-danger h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-envelope icon me-3"></i>
                    <div>
                        <h5 class="card-title mb-0">Total Messages</h5>
                        <h2 class="mb-0"><?php echo $total_messages; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-file-upload icon me-3"></i>
                    <div>
                        <h5 class="card-title mb-0">Total Submissions</h5>
                        <h2 class="mb-0"><?php echo $total_submissions; ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
