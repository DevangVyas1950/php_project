<?php
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/recommendation_engine.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$uid = intval($_SESSION['user_id']);
$user = get_user($uid);

$stmt = $conn->prepare("SELECT * FROM user_intake WHERE user_id = ?");
$stmt->bind_param('i', $uid);
$stmt->execute();
$intake = $stmt->get_result()->fetch_assoc();
$stmt->close();

$recommendation = build_recommendation($conn, $user, $intake ?: null);

echo json_encode(['status' => 'success'] + $recommendation);
