<?php
require_once '../config/constants.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$uid = intval($_SESSION['user_id']);
$UPLOAD_DIR = __DIR__ . '/../uploads/progress_photos/';
$ALLOWED_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$MAX_BYTES = 5 * 1024 * 1024; // 5MB

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $conn->prepare("SELECT id, taken_date, weight_kg, notes, created_at FROM progress_photos WHERE user_id = ? ORDER BY taken_date DESC, id DESC");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $photos = [];
    while ($row = $res->fetch_assoc()) {
        $photos[] = $row;
    }
    $stmt->close();
    echo json_encode(['status' => 'success', 'photos' => $photos]);
    exit;
}

if ($action === 'upload') {
    if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $msg = 'No image was received.';
        if (!empty($_FILES['photo']['error'])) {
            $msg = match ((int) $_FILES['photo']['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That image is too large for this server\'s upload limit.',
                UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
                default => 'Something went wrong receiving the image.',
            };
        }
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit;
    }

    $file = $_FILES['photo'];

    if ($file['size'] > $MAX_BYTES) {
        echo json_encode(['status' => 'error', 'message' => 'Image must be under 5MB.']);
        exit;
    }

    // Verify the real MIME type server-side rather than trusting the client.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $real_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($ALLOWED_TYPES[$real_type])) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPEG, PNG, or WebP images are allowed.']);
        exit;
    }

    $taken_date = $_POST['taken_date'] ?? date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $taken_date)) {
        $taken_date = date('Y-m-d');
    }
    $weight_kg = isset($_POST['weight_kg']) && $_POST['weight_kg'] !== '' ? floatval($_POST['weight_kg']) : null;
    $notes = trim(substr($_POST['notes'] ?? '', 0, 500));

    if (!is_dir($UPLOAD_DIR)) {
        mkdir($UPLOAD_DIR, 0755, true);
    }

    $ext = $ALLOWED_TYPES[$real_type];
    $filename = 'u' . $uid . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['status' => 'error', 'message' => 'Unable to save the image.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO progress_photos (user_id, filename, taken_date, weight_kg, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issds', $uid, $filename, $taken_date, $weight_kg, $notes);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Photo saved.', 'id' => $stmt->insert_id]);
    } else {
        @unlink($dest);
        echo json_encode(['status' => 'error', 'message' => 'Unable to save photo record.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);

    $stmt = $conn->prepare("SELECT filename FROM progress_photos WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Photo not found.']);
        exit;
    }

    $del = $conn->prepare("DELETE FROM progress_photos WHERE id = ? AND user_id = ?");
    $del->bind_param('ii', $id, $uid);
    $del->execute();
    $del->close();

    $path = $UPLOAD_DIR . $row['filename'];
    if (is_file($path)) {
        @unlink($path);
    }

    echo json_encode(['status' => 'success', 'message' => 'Photo deleted.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
