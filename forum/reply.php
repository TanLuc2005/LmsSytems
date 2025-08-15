<?php
// forum/reply.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$topic_id = intval($_GET['topic_id'] ?? 0);
if (!$topic_id) {
    echo 'Chủ đề không hợp lệ.';
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
        $errors[] = 'Vui lòng nhập nội dung.';
    }
    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO forum_posts (topic_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iis', $topic_id, $_SESSION['user_id'], $content);
        if ($stmt->execute()) {
            $success = 'Đã trả lời chủ đề!';
        } else {
            $errors[] = 'Lỗi khi trả lời.';
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
    <title>Trả lời chủ đề</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Trả lời chủ đề</h2>
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
            <textarea name="content" rows="5" cols="50" required></textarea>
        </label><br>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit">Gửi trả lời</button>
    </form>
    <p><a href="view_topic.php?id=<?= $topic_id ?>">Quay lại chủ đề</a></p>
</body>
</html>
