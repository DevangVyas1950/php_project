<?php
require_once '../config/constants.php';
require_once '../config/db.php';

if (!is_logged_in()) {
    http_response_code(403);
    exit('Forbidden');
}

$uid = intval($_SESSION['user_id']);
$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT filename FROM progress_photos WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    exit('Not found');
}

$path = __DIR__ . '/../uploads/progress_photos/' . $row['filename'];

if (!is_file($path)) {
    http_response_code(404);
    exit('Not found');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = match ($ext) {
    'png'  => 'image/png',
    'webp' => 'image/webp',
    default => 'image/jpeg',
};

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
readfile($path);
