<?php
require_once '../config/constants.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$uid = intval($_SESSION['user_id']);

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$age = intval($_POST['age'] ?? 0);
$gender = $_POST['gender'] ?? 'other';
$weight = floatval($_POST['weight_kg'] ?? 0);
$height = floatval($_POST['height_cm'] ?? 0);
$goal = $_POST['goal'] ?? 'stay_healthy';

if ($name === '' || $email === '') {
    echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
    exit;
}

if (!in_array($gender, ['male', 'female', 'other'], true)) {
    $gender = 'other';
}

if (!in_array($goal, ['lose_weight', 'gain_muscle', 'stay_healthy'], true)) {
    $goal = 'stay_healthy';
}

$bmi = calculate_bmi($weight, $height);

$stmt = $conn->prepare("UPDATE users SET name=?, email=?, age=?, gender=?, weight_kg=?, height_cm=?, goal=?, bmi=? WHERE id=?");
$stmt->bind_param('ssissssdi', $name, $email, $age, $gender, $weight, $height, $goal, $bmi, $uid);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully', 'bmi' => $bmi]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unable to update profile']);
}

$stmt->close();
