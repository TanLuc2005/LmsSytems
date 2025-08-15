<?php
// forum/topic_create.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) {
    echo 'Khóa học không hợp lệ.';
    exit();
}

// Lấy danh sách category theo course
$categories = [];
$stmt = $conn->prepare('SELECT id, name FROM forum_categories WHERE course_id = ?');
$stmt->bind_param('i', $course_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$stmt->close();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'CSRF token không hợp lệ.';
    }
    if (!$category_id || empty($title) || empty($content)) {
        $errors[] = 'Vui lòng nhập đầy đủ thông tin.';
    }
    if (!$errors) {
        // Tạo topic
        $stmt = $conn->prepare('INSERT INTO forum_topics (category_id, user_id, title, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iis', $category_id, $_SESSION['user_id'], $title);
        if ($stmt->execute()) {
            $topic_id = $stmt->insert_id;
            // Tạo post đầu tiên
            $stmt2 = $conn->prepare('INSERT INTO forum_posts (topic_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())');
            $stmt2->bind_param('iis', $topic_id, $_SESSION['user_id'], $content);
            $stmt2->execute();
            $stmt2->close();
            $success = 'Tạo chủ đề thành công!';
        } else {
            $errors[] = 'Lỗi khi tạo chủ đề.';
        }
        $stmt->close();
    }
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo chủ đề mới</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Tạo chủ đề mới</h2>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;"><p><?= $success ?></p></div>
    <?php endif; ?>
    <form method="post">
        <label>Chuyên mục:
            <select name="category_id" required>
                <option value="">--Chọn--</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Tiêu đề: <input type="text" name="title" required></label><br>
        <label>Nội dung:<br>
            <textarea name="content" rows="5" cols="50" required></textarea>
        </label><br>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit">Tạo chủ đề</button>
    </form>
    <p><a href="index.php">Quay lại diễn đàn</a></p>
</body>
</html>
