<?php
require_once '../config/constants.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!is_logged_in()) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uid = intval($_SESSION['user_id']);

if ($action === 'log') {
    $weight = floatval($_POST['weight_kg']);
    $height = floatval($_POST['height_cm'] ?? 0);
    $user   = (object)get_user($uid);
    $h      = $height ?: $user->height_cm;
    $bmi    = calculate_bmi($weight, $h);
    $notes  = $conn->real_escape_string(trim($_POST['notes'] ?? ''));
    $today  = date('Y-m-d');
    $conn->query("INSERT INTO progress_log (user_id,weight_kg,bmi,log_date,notes) VALUES ($uid,$weight,$bmi,'$today','$notes')");
    $conn->query("UPDATE users SET weight_kg=$weight, bmi=$bmi WHERE id=$uid");
    echo json_encode(['status'=>'success','bmi'=>$bmi]);
}

elseif ($action === 'history') {
    $res = $conn->query("SELECT * FROM progress_log WHERE user_id=$uid ORDER BY log_date ASC LIMIT 30");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}
