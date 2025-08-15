<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
// Use correct field for submission text
$stmt = $db->prepare('SELECT s.*, a.title as assignment_title, u.username as student_name FROM assignment_submissions s JOIN assignments a ON s.assignment_id = a.id JOIN users u ON s.student_id = u.id JOIN courses c ON a.course_id = c.id WHERE c.instructor_id = ? AND s.grade IS NULL');
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grading</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow border-0">
                    <div class="card-header bg-gradient bg-info text-white text-center">
                        <h2 class="mb-0"><i class="fas fa-clipboard-list"></i> Pending Grading</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-info align-middle text-center">
                                    <tr>
                                        <th class="bg-primary text-white"><i class="fas fa-user"></i> Student</th>
                                        <th class="bg-success text-white"><i class="fas fa-file-alt"></i> Assignment</th>
                                        <th class="bg-warning text-dark"><i class="fas fa-file-signature"></i> Submission</th>
                                        <th class="bg-secondary text-white"><i class="fas fa-cogs"></i> Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($result as $row): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-primary"><?= htmlspecialchars($row['student_name']) ?></td>
                                        <td class="text-center fw-bold text-success"><?= htmlspecialchars($row['assignment_title']) ?></td>
                                        <td class="text-center text-dark">
                                            <?php if (!empty($row['submission_text'])): ?>
                                                <?= nl2br(htmlspecialchars($row['submission_text'])) ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No submission</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="assignment_grade.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success px-3"><i class="fas fa-pen"></i> Grade</a>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        }
        .card-header.bg-gradient {
            background: linear-gradient(90deg, #36d1c4 0%, #5b86e5 100%) !important;
        }
        .table thead th {
            vertical-align: middle;
        }
    </style>
</body>
</html>
