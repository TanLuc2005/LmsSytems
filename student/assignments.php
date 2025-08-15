<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get assignments for enrolled courses
$query = "SELECT a.*, c.title as course_title, c.course_code,
          CASE WHEN s.id IS NOT NULL THEN 'submitted' ELSE 'pending' END as status,
          s.grade, s.submitted_at
          FROM assignments a
          JOIN courses c ON a.course_id = c.id
          JOIN enrollments e ON c.id = e.course_id
          LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
          WHERE e.student_id = ? AND e.status = 'enrolled'
          ORDER BY a.due_date ASC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function statusBadge($status) {
    if ($status === 'submitted') {
        return '<span class="badge bg-success"><i class="fas fa-check"></i> Submitted</span>';
    } else {
        return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half"></i> Pending</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h2 class="mb-0"><i class="fas fa-tasks"></i> Assignments</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-tasks fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">No assignments found.</p>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-info">
                                    <tr>
                                        <th><i class="fas fa-file-alt"></i> Title</th>
                                        <th><i class="fas fa-book"></i> Course</th>
                                        <th><i class="fas fa-calendar-day"></i> Due</th>
                                        <th><i class="fas fa-flag"></i> Status</th>
                                        <th><i class="fas fa-cogs"></i> Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($assignments as $a): ?>
                                    <tr>
                                        <td><i class="fas fa-file-alt text-secondary"></i> <?= htmlspecialchars($a['title']) ?></td>
                                        <td><?= htmlspecialchars($a['course_title']) ?></td>
                                        <td><?= formatDate($a['due_date']) ?></td>
                                        <td><?= statusBadge($a['status']) ?></td>
                                        <td>
                                            <?php if ($a['status'] === 'pending'): ?>
                                                <a href="assignment_submit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-upload"></i> Submit</a>
                                            <?php else: ?>
                                                <span class="text-success"><i class="fas fa-check-circle"></i> Submitted</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
