<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare('SELECT a.*, c.title as course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.instructor_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4"><i class="fas fa-tasks"></i> Assignments</h2>
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-info">
                            <tr>
                                <th><i class="fas fa-file-alt"></i> Title</th>
                                <th><i class="fas fa-book"></i> Course</th>
                                <th><i class="fas fa-calendar-day"></i> Due Date</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><i class="fas fa-file-alt text-secondary"></i> <?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['course_title']) ?></td>
                                <td><?= htmlspecialchars($row['due_date']) ?></td>
                                <td>
                                    <a href="assignment_grade.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-marker"></i> Grade
                                    </a>
                                </td>
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
