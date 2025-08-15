<?php
// forum/post_delete.php
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

// Chỉ cho phép owner hoặc admin xóa
$is_owner = ($post['user_id'] == $_SESSION['user_id']);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
if (!$is_owner && !$is_admin) {
    echo 'Bạn không có quyền xóa bài viết này.';
    exit();
}

// Xóa bài viết
$stmt = $conn->prepare('DELETE FROM forum_posts WHERE id = ?');
$stmt->bind_param('i', $post_id);
$stmt->execute();
$stmt->close();

header('Location: view_topic.php?id=' . $post['topic_id']);
exit();
