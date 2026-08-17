<?php
require_once '../config/constants.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$uid = intval($_SESSION['user_id']);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add_weight') {
    $weight = isset($_POST['weight_kg']) ? (float) $_POST['weight_kg'] : 0;
    $recorded_date = trim((string) ($_POST['recorded_date'] ?? date('Y-m-d')));

    if ($weight <= 0 || $recorded_date === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid weight and date.']);
        exit;
    }

    $user_stmt = $conn->prepare('SELECT height_cm FROM users WHERE id = ?');
    $user_stmt->bind_param('i', $uid);
    $user_stmt->execute();
    $user_row = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();

    $height_cm = isset($user_row['height_cm']) ? (float) $user_row['height_cm'] : 0;
    $bmi = $height_cm > 0 ? calculate_bmi($weight, $height_cm) : null;

    $insert_stmt = $conn->prepare('INSERT INTO weight_history (user_id, weight_kg, recorded_date) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE weight_kg = VALUES(weight_kg)');
    $insert_stmt->bind_param('ids', $uid, $weight, $recorded_date);
    $insert_stmt->execute();
    $insert_stmt->close();

    if ($bmi !== null) {
        $update_stmt = $conn->prepare('UPDATE users SET weight_kg = ?, bmi = ? WHERE id = ?');
        $update_stmt->bind_param('dsi', $weight, $bmi, $uid);
        $update_stmt->execute();
        $update_stmt->close();
    }

    echo json_encode(['status' => 'success', 'message' => 'Weight updated!']);
}
elseif ($action === 'get_history') {
    $limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 30;

    $stmt = $conn->prepare('SELECT recorded_date, weight_kg FROM weight_history WHERE user_id = ? ORDER BY recorded_date ASC LIMIT ?');
    $stmt->bind_param('ii', $uid, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $labels = [];
    $values = [];

    foreach ($rows as $row) {
        $dateObj = new DateTime($row['recorded_date']);
        $labels[] = $dateObj->format('d M');
        $values[] = (float) $row['weight_kg'];
    }

    echo json_encode(['labels' => $labels, 'values' => $values]);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
