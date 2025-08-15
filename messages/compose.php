<?php
// messages/compose.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$errors = [];
$success = '';

// Lấy danh sách user (trừ chính mình)

$users = [];
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare('SELECT id, username FROM users WHERE id != ?');
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to_id = intval($_POST['to_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }
    if (!$to_id || empty($subject) || empty($body)) {
        $errors[] = 'Please fill in all fields.';
    }
    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO messages (sender_id, recipient_id, subject, content, sent_at) VALUES (?, ?, ?, ?, NOW())');
        if ($stmt->execute([$_SESSION['user_id'], $to_id, $subject, $body])) {
            $success = 'Message sent successfully!';
        } else {
            $errors[] = 'Error sending message.';
        }
    }
}
// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compose Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <h2 class="mb-4 text-primary"><i class="fas fa-paper-plane"></i> Compose New Message</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo '<p class="mb-0">' . htmlspecialchars($e) . '</p>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><p class="mb-0"><?= $success ?></p></div>
    <?php endif; ?>
    <div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
        <form method="post" class="card shadow p-4 bg-white rounded w-100" style="max-width: 600px;">
        <div class="mb-3">
            <label for="to_id" class="form-label fw-bold"><i class="fas fa-user"></i> Recipient:</label>
            <select name="to_id" id="to_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label fw-bold"><i class="fas fa-heading"></i> Subject:</label>
            <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter subject" required>
        </div>
        <div class="mb-3">
            <label for="body" class="form-label fw-bold"><i class="fas fa-align-left"></i> Message:</label>
            <textarea name="body" id="body" rows="5" class="form-control" placeholder="Type your message..." required></textarea>
        </div>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Send</button>
            <a href="inbox.php" class="btn btn-link text-decoration-none ms-2"><i class="fas fa-arrow-left"></i> Back to Inbox</a>
        </form>
    </div>
</body>
</html>
