<?php
// student/grades.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get all graded assignments for the student
$query = "SELECT a.title AS assignment_title, c.title AS course_title, s.grade, s.feedback, s.submitted_at, a.due_date
          FROM assignment_submissions s
          JOIN assignments a ON s.assignment_id = a.id
          JOIN courses c ON a.course_id = c.id
          WHERE s.student_id = ? AND s.grade IS NOT NULL
          ORDER BY a.due_date DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Grades</title>
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
                    <div class="card-header bg-success text-white">
                        <h2 class="mb-0"><i class="fas fa-graduation-cap"></i> My Grades</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($grades)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-graduation-cap fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">No grades available yet.</p>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th><i class="fas fa-file-alt"></i> Assignment</th>
                                        <th><i class="fas fa-book"></i> Course</th>
                                        <th><i class="fas fa-calendar-day"></i> Due Date</th>
                                        <th><i class="fas fa-upload"></i> Submitted At</th>
                                        <th><i class="fas fa-star"></i> Grade</th>
                                        <th><i class="fas fa-comment"></i> Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($grades as $g): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($g['assignment_title']) ?></td>
                                        <td><?= htmlspecialchars($g['course_title']) ?></td>
                                        <td><?= htmlspecialchars($g['due_date']) ?></td>
                                        <td><?= htmlspecialchars($g['submitted_at']) ?></td>
                                        <td><span class="badge bg-primary fs-6"> <?= htmlspecialchars($g['grade']) ?> </span></td>
                                        <td><?= htmlspecialchars($g['feedback']) ?></td>
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
