<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare('SELECT username, email, first_name, last_name, profile_image, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2><i class="fas fa-user"></i> My Profile</h2>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="<?= $user['profile_image'] ? htmlspecialchars($user['profile_image']) : '../assets/img/default-profile.png' ?>" class="rounded-circle" width="120" height="120" alt="Profile Picture">
                        </div>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th><i class="fas fa-user"></i> Username</th>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-id-card"></i> Name</th>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar-alt"></i> Joined</th>
                                <td><?= formatDate($user['created_at']) ?></td>
                            </tr>
                        </table>
                        <div class="text-center mt-3">
                            <a href="profile_edit.php" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
