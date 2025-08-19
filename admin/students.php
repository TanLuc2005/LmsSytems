<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Handle delete student
if (isset($_GET['delete'])) {
    $student_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$student_id]);
    header('Location: students.php?msg=deleted');
    exit();
}

// Handle add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = 'student';
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $password, $first_name, $last_name, $role]);
    header('Location: students.php?msg=added');
    exit();
}

// Handle edit student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $student_id = intval($_POST['student_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, first_name=?, last_name=? WHERE id=? AND role='student'");
    $stmt->execute([$username, $email, $first_name, $last_name, $student_id]);
    header('Location: students.php?msg=updated');
    exit();
}

// Get all students
$stmt = $conn->query("SELECT * FROM users WHERE role = 'student' ORDER BY id DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include '../includes/admin_navbar.php'; ?>
<div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-user-graduate text-primary me-2"></i>Manage Students</h2>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php if ($_GET['msg'] === 'added') echo 'Student added successfully!'; ?>
            <?php if ($_GET['msg'] === 'updated') echo 'Student updated successfully!'; ?>
            <?php if ($_GET['msg'] === 'deleted') echo 'Student deleted successfully!'; ?>
        </div>
    <?php endif; ?>
    <!-- Add Student Form -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white"><i class="fas fa-plus-circle me-2"></i>Add Student</div>
        <div class="card-body">
            <form method="post">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" name="add_student" class="btn btn-success w-100"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Students Table -->
    <div class="card">
        <div class="card-header bg-primary text-white"><i class="fas fa-list me-2"></i>Students List</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                        <td>
                            <!-- Edit Button trigger modal -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $student['id']; ?>"><i class="fas fa-edit"></i></button>
                            <a href="students.php?delete=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $student['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $student['id']; ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="post">
                            <div class="modal-header">
                              <h5 class="modal-title" id="editModalLabel<?php echo $student['id']; ?>">Edit Student</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                              <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($student['username']); ?>" required>
                              </div>
                              <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                              </div>
                              <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                              </div>
                              <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" name="edit_student" class="btn btn-primary">Save Changes</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
