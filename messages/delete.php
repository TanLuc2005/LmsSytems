<?php
// messages/delete.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        // Chỉ cho phép xóa nếu là người nhận
        $stmt = $conn->prepare('DELETE FROM messages WHERE id = ? AND to_id = ?');
        $stmt->bind_param('ii', $id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    }
}
header('Location: inbox.php');
exit();
