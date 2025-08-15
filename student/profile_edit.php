<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Fetch current user info
$stmt = $db->prepare('SELECT username, email, first_name, last_name, profile_image FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $profile_image = $user['profile_image'];

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $new_name = '../assets/img/profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            if (move_uploaded_file($tmp_name, $new_name)) {
                $profile_image = $new_name;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        } else {
            $errors[] = 'Invalid image format.';
        }
    }

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $errors[] = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (!$errors) {
        $stmt = $db->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, profile_image = ? WHERE id = ?');
        if ($stmt->execute([$first_name, $last_name, $email, $profile_image, $_SESSION['user_id']])) {
            $success = 'Profile updated successfully!';
            // Refresh user info
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
            $user['profile_image'] = $profile_image;
        } else {
            $errors[] = 'Error updating profile.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
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
                    <div class="card-header bg-warning text-white text-center">
                        <h2><i class="fas fa-edit"></i> Edit Profile</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= $success ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?= $user['profile_image'] ? htmlspecialchars($user['profile_image']) : '../assets/img/default-profile.png' ?>" class="rounded-circle" width="120" height="120" alt="Profile Picture">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-user"></i> Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-id-card"></i> First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-id-card"></i> Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-image"></i> Profile Image</label>
                                <input type="file" name="profile_image" class="form-control">
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Save Changes</button>
                                <a href="profile.php" class="btn btn-secondary ms-2"><i class="fas fa-arrow-left"></i> Back to Profile</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
