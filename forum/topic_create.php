<?php
// forum/topic_create.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$db = (new Database())->getConnection();
$course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) {
    echo 'Invalid course.';
    exit();
}

// Get categories by course
$categories = [];
$stmt = $db->prepare('SELECT id, name FROM forum_categories WHERE course_id = ?');
$stmt->execute([$course_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }
    if (!$category_id || empty($title) || empty($content)) {
        $errors[] = 'Please fill in all required fields.';
    }
    if (!$errors) {
        // Create topic
        $stmt = $db->prepare('INSERT INTO forum_topics (category_id, user_id, title, created_at) VALUES (?, ?, ?, NOW())');
        if ($stmt->execute([$category_id, $_SESSION['user_id'], $title])) {
            $topic_id = $db->lastInsertId();
            // Create first post
            $stmt2 = $db->prepare('INSERT INTO forum_posts (topic_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())');
            $stmt2->execute([$topic_id, $_SESSION['user_id'], $content]);
            $success = 'Topic created successfully!';
        } else {
            $errors[] = 'Error creating topic.';
        }
    }
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Topic</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Create New Topic</h2>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;"><p><?= $success ?></p></div>
    <?php endif; ?>
    <form method="post">
        <label>Category:
            <select name="category_id" required>
                <option value="">--Select--</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Title: <input type="text" name="title" required></label><br>
        <label>Content:<br>
            <textarea name="content" rows="5" cols="50" required></textarea>
        </label><br>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit">Create Topic</button>
    </form>
    <p><a href="index.php">Back to forum</a></p>
</body>
</html>
