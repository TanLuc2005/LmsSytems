<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get students enrolled in instructor's courses
$stmt = $db->prepare('SELECT DISTINCT u.id, u.username, u.email, u.role, c.title as course_title FROM users u JOIN enrollments e ON u.id = e.student_id JOIN courses c ON e.course_id = c.id WHERE c.instructor_id = ? AND e.status = "enrolled" ORDER BY u.username');
$stmt->execute([$_SESSION['user_id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4"><i class="fas fa-users"></i> Students in Your Courses</h2>
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-success">
                            <tr>
                                <th><i class="fas fa-user"></i> Username</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-user-graduate"></i> Role</th>
                                <th><i class="fas fa-book"></i> Course</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><i class="fas fa-user text-primary"></i> <?= htmlspecialchars($student['username']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><i class="fas fa-user-graduate text-info"></i> <?= htmlspecialchars($student['role']) ?></td>
                                <td><i class="fas fa-book-open text-success"></i> <?= htmlspecialchars($student['course_title']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
