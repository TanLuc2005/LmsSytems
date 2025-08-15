<?php
// messages/view.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo 'Invalid message.';
    exit();
}



$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare('SELECT m.*, u1.username as from_user, u2.username as to_user FROM messages m JOIN users u1 ON m.sender_id = u1.id JOIN users u2 ON m.recipient_id = u2.id WHERE m.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)');
$stmt->execute([$id, $_SESSION['user_id'], $_SESSION['user_id']]);
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    echo 'Message not found or you do not have permission to view it.';
    exit();
}

// Mark as read if recipient
if ($msg['recipient_id'] == $_SESSION['user_id'] && is_null($msg['read_at'])) {
    $stmt = $db->prepare('UPDATE messages SET read_at = NOW() WHERE id = ?');
    $stmt->execute([$id]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Message</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2>Message Details</h2>
    <p><b>From:</b> <?= htmlspecialchars($msg['from_user']) ?></p>
    <p><b>To:</b> <?= htmlspecialchars($msg['to_user']) ?></p>
    <p><b>Subject:</b> <?= htmlspecialchars($msg['subject']) ?></p>
    <p><b>Content:</b><br><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
    <p><b>Sent At:</b> <?= htmlspecialchars($msg['sent_at']) ?></p>
    <p><a href="inbox.php">Back to Inbox</a></p>
    <?php if ($msg['recipient_id'] == $_SESSION['user_id']): ?>
        <form method="post" action="delete.php" onsubmit="return confirm('Are you sure you want to delete this message?');">
            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
            <button type="submit">Delete Message</button>
        </form>
    <?php endif; ?>
</body>
</html>
