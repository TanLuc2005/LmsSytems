<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get all active courses not already enrolled by this student
$query = "SELECT c.*, u.first_name, u.last_name FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.status = 'active' AND c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ? AND status = 'enrolled') ORDER BY c.title ASC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-search"></i> Browse Courses</h1>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-book"></i> Available Courses
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">No available courses to enroll.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($courses as $course): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-left-primary">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                <p class="card-text text-muted small">
                                                    <?php echo htmlspecialchars($course['course_code']); ?> - 
                                                    <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
                                                </p>
                                                <p class="card-text small"><?php echo htmlspecialchars($course['description']); ?></p>
                                                <form method="post" action="enroll.php" class="d-inline">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">Enroll</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
