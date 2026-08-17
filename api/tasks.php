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

if ($action === 'toggle') {
    $id = intval($_POST['task_id'] ?? 0);
    $status = intval($_POST['status'] ?? 0);

    $conn->query("UPDATE tasks SET is_completed=$status WHERE id=$id AND user_id=$uid");
    $conn->query("UPDATE user_tasks SET is_completed=$status WHERE id=$id AND user_id=$uid");

    echo json_encode(['status' => 'success']);
}
elseif ($action === 'today') {
    $today = date('Y-m-d');
    $res = $conn->query("SELECT * FROM tasks WHERE user_id=$uid AND due_date='$today' ORDER BY is_completed ASC, id DESC");
    if ($res) {
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    } else {
        echo json_encode([]);
    }
}
elseif ($action === 'add') {
    $title = $conn->real_escape_string(trim($_POST['task_title'] ?? ''));
    $type = $conn->real_escape_string($_POST['task_type'] ?? 'general');
    $date = $conn->real_escape_string($_POST['due_date'] ?? date('Y-m-d'));

    if ($title === '') {
        echo json_encode(['status' => 'error', 'message' => 'Task title is required']);
        exit;
    }

    $conn->query("INSERT INTO tasks (user_id, task_title, task_type, due_date) VALUES ($uid, '$title', '$type', '$date')");
    $conn->query("INSERT INTO user_tasks (user_id, task_title, task_type, due_date) VALUES ($uid, '$title', '$type', '$date')");

    echo json_encode(['status' => 'success', 'id' => $conn->insert_id]);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
