<?php
require_once '../config/constants.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uid = intval($_SESSION['user_id']);

if ($action === 'add_log') {
    $food_name = trim((string) ($_POST['food_name'] ?? ''));
    $meal_type = trim((string) ($_POST['meal_type'] ?? 'Breakfast'));
    $calories = (float) ($_POST['calories'] ?? 0);
    $protein_g = (float) ($_POST['protein_g'] ?? 0);
    $carbs_g = (float) ($_POST['carbs_g'] ?? 0);
    $fat_g = (float) ($_POST['fat_g'] ?? 0);
    $logged_date = trim((string) ($_POST['logged_date'] ?? date('Y-m-d')));

    if ($food_name === '' || $calories <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a meal name and calorie amount.']);
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO food_log (user_id, food_name, meal_type, calories, protein_g, carbs_g, fat_g, logged_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('issdddds', $uid, $food_name, $meal_type, $calories, $protein_g, $carbs_g, $fat_g, $logged_date);
    $stmt->execute();
    $stmt->close();

    $summary_stmt = $conn->prepare('SELECT SUM(calories) AS total_calories, SUM(protein_g) AS total_protein, SUM(carbs_g) AS total_carbs, SUM(fat_g) AS total_fat FROM food_log WHERE user_id = ? AND logged_date = ?');
    $summary_stmt->bind_param('is', $uid, $logged_date);
    $summary_stmt->execute();
    $summary = $summary_stmt->get_result()->fetch_assoc();
    $summary_stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Meal logged successfully.',
        'totals' => [
            'calories' => round((float) ($summary['total_calories'] ?? 0), 1),
            'protein' => round((float) ($summary['total_protein'] ?? 0), 1),
            'carbs' => round((float) ($summary['total_carbs'] ?? 0), 1),
            'fat' => round((float) ($summary['total_fat'] ?? 0), 1),
        ],
    ]);
}
elseif ($action === 'list_today') {
    $logged_date = trim((string) ($_GET['logged_date'] ?? date('Y-m-d')));

    $stmt = $conn->prepare('SELECT id, food_name, meal_type, calories, protein_g, carbs_g, fat_g, logged_date FROM food_log WHERE user_id = ? AND logged_date = ? ORDER BY FIELD(meal_type, "Breakfast", "Lunch", "Dinner", "Snack"), id DESC');
    $stmt->bind_param('is', $uid, $logged_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $summary_stmt = $conn->prepare('SELECT SUM(calories) AS total_calories, SUM(protein_g) AS total_protein, SUM(carbs_g) AS total_carbs, SUM(fat_g) AS total_fat FROM food_log WHERE user_id = ? AND logged_date = ?');
    $summary_stmt->bind_param('is', $uid, $logged_date);
    $summary_stmt->execute();
    $summary = $summary_stmt->get_result()->fetch_assoc();
    $summary_stmt->close();

    echo json_encode([
        'status' => 'success',
        'items' => $rows,
        'totals' => [
            'calories' => round((float) ($summary['total_calories'] ?? 0), 1),
            'protein' => round((float) ($summary['total_protein'] ?? 0), 1),
            'carbs' => round((float) ($summary['total_carbs'] ?? 0), 1),
            'fat' => round((float) ($summary['total_fat'] ?? 0), 1),
        ],
    ]);
}
elseif ($action === 'delete_log') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid entry.']);
        exit;
    }

    $stmt = $conn->prepare('DELETE FROM food_log WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Entry removed.']);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
}
