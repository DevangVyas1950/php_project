<?php
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = get_user($_SESSION['user_id']);
if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$recommendation = get_diet_recommendation($user);

echo json_encode([
    'status' => 'success',
    'data' => [
        'target_calories' => $recommendation['target_calories'],
        'target_macros' => $recommendation['target_macros'],
        'meal_plan' => $recommendation['meal_plan'],
        'goal_label' => $recommendation['goal_label'],
        'focus' => $recommendation['focus'],
    ]
]);
