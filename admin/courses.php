<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get all courses
$stmt = $db->prepare('SELECT c.*, u.first_name, u.last_name FROM courses c JOIN users u ON c.instructor_id = u.id ORDER BY c.created_at DESC');
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><i class="fas fa-book"></i> Manage Courses</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> ID</th>
                                        <th><i class="fas fa-book"></i> Title</th>
                                        <th><i class="fas fa-user-tie"></i> Instructor</th>
                                        <th><i class="fas fa-calendar-alt"></i> Created</th>
                                        <th><i class="fas fa-cogs"></i> Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($course['id']) ?></td>
                                        <td><?= htmlspecialchars($course['title']) ?></td>
                                        <td><?= htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) ?></td>
                                        <td><?= htmlspecialchars($course['created_at']) ?></td>
                                        <td>
                                            <a href="course_edit.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="course_delete.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?');"><i class="fas fa-trash"></i> Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
