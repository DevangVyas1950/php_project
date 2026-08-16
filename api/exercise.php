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

if ($action === 'library' || $action === 'list') {
    $category = $_GET['category'] ?? '';
    $allowed = ['cardio', 'yoga', 'strength', 'flexibility'];

    if (in_array($category, $allowed, true)) {
        $stmt = $conn->prepare('SELECT id, title, category, calories_per_min, media_url, instructions FROM exercise_library WHERE category = ? ORDER BY title');
        $stmt->bind_param('s', $category);
    } else {
        $stmt = $conn->prepare('SELECT id, title, category, calories_per_min, media_url, instructions FROM exercise_library ORDER BY category, title');
    }

    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    $stmt->close();
}
elseif ($action === 'log') {
    $exercise_id = intval($_POST['exercise_id'] ?? 0);
    $duration = intval($_POST['duration_mins'] ?? 0);

    if ($exercise_id <= 0 || $duration <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Exercise and duration are required']);
        exit;
    }

    $stmt = $conn->prepare('SELECT title, category, calories_per_min FROM exercise_library WHERE id = ?');
    $stmt->bind_param('i', $exercise_id);
    $stmt->execute();
    $exercise = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$exercise) {
        echo json_encode(['status' => 'error', 'message' => 'Exercise not found']);
        exit;
    }

    $user_stmt = $conn->prepare('SELECT weight_kg FROM users WHERE id = ?');
    $user_stmt->bind_param('i', $uid);
    $user_stmt->execute();
    $user_row = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();

    $weight = $user_row['weight_kg'] ?? 70;
    $calories = round($duration * floatval($exercise['calories_per_min']) * ($weight / 70));
    $today = date('Y-m-d');

    $insert = $conn->prepare('INSERT INTO user_exercise_log (user_id, exercise_id, category, duration_mins, calories_burned, log_date) VALUES (?, ?, ?, ?, ?, ?)');
    $insert->bind_param('iisiis', $uid, $exercise_id, $exercise['category'], $duration, $calories, $today);
    $insert->execute();
    $insert->close();

    echo json_encode(['status' => 'success', 'calories_burned' => $calories]);
}
elseif ($action === 'today_totals') {
    $today = date('Y-m-d');
    $stmt = $conn->prepare('SELECT uel.category, SUM(uel.calories_burned) AS calories FROM user_exercise_log uel WHERE uel.user_id = ? AND uel.log_date = ? GROUP BY uel.category');
    $stmt->bind_param('is', $uid, $today);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total = 0;
    $by_category = [];
    foreach ($rows as $row) {
        $total += (int) $row['calories'];
        $by_category[] = [
            'category' => ucfirst($row['category']),
            'calories' => (int) $row['calories']
        ];
    }

    $daily_goal = max(400, (int) round(($conn->query("SELECT weight_kg FROM users WHERE id=$uid")->fetch_assoc()['weight_kg'] ?? 70) * 6));

    echo json_encode([
        'total_calories' => $total,
        'daily_goal' => $daily_goal,
        'by_category' => $by_category
    ]);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
