<?php
// forum/post_edit.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$post_id = intval($_GET['id'] ?? 0);
if (!$post_id) {
    echo 'Bài viết không hợp lệ.';
    exit();
}

// Lấy thông tin bài viết
$stmt = $conn->prepare('SELECT * FROM forum_posts WHERE id = ?');
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    echo 'Không tìm thấy bài viết.';
    exit();
}

// Chỉ cho phép owner hoặc admin sửa
$is_owner = ($post['user_id'] == $_SESSION['user_id']);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
if (!$is_owner && !$is_admin) {
    echo 'Bạn không có quyền sửa bài viết này.';
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'CSRF token không hợp lệ.';
    }
    if (empty($content)) {
        $errors[] = 'Nội dung không được để trống.';
    }
    if (!$errors) {
        $stmt = $conn->prepare('UPDATE forum_posts SET content = ? WHERE id = ?');
        $stmt->bind_param('si', $content, $post_id);
        if ($stmt->execute()) {
            $success = 'Đã cập nhật bài viết!';
        } else {
            $errors[] = 'Lỗi khi cập nhật.';
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
    <title>Sửa bài viết</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Sửa bài viết</h2>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;"><p><?= $success ?></p></div>
    <?php endif; ?>
    <form method="post">
        <label>Nội dung:<br>
            <textarea name="content" rows="5" cols="50" required><?= htmlspecialchars($post['content']) ?></textarea>
        </label><br>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit">Cập nhật</button>
    </form>
    <p><a href="view_topic.php?id=<?= $post['topic_id'] ?>">Quay lại chủ đề</a></p>
</body>
</html>
