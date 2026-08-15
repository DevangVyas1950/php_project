<?php
require_once '../config/constants.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!is_logged_in() || !is_admin()) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$action = $_POST['action'] ?? '';

if ($action === 'add_food') {
    $name  = $conn->real_escape_string(trim($_POST['name']));
    $cat   = $conn->real_escape_string($_POST['category']);
    $cal   = floatval($_POST['calories_per_100g']);
    $prot  = floatval($_POST['protein_g']);
    $carbs = floatval($_POST['carbs_g']);
    $fat   = floatval($_POST['fat_g']);
    $conn->query("INSERT INTO foods (name,calories_per_100g,protein_g,carbs_g,fat_g,category) VALUES ('$name',$cal,$prot,$carbs,$fat,'$cat')");
    echo json_encode(['status'=>'success','id'=>$conn->insert_id]);
}

elseif ($action === 'add_exercise') {
    $name  = $conn->real_escape_string(trim($_POST['name']));
    $cat   = $conn->real_escape_string($_POST['category']);
    $dur   = intval($_POST['duration_min']);
    $cal   = intval($_POST['calories_burned']);
    $diff  = $conn->real_escape_string($_POST['difficulty']);
    $inst  = $conn->real_escape_string(trim($_POST['instructions']));
    $conn->query("INSERT INTO exercises (name,category,duration_min,calories_burned,difficulty,instructions) VALUES ('$name','$cat',$dur,$cal,'$diff','$inst')");
    echo json_encode(['status'=>'success','id'=>$conn->insert_id]);
}

elseif ($action === 'add_diet_chart') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $goal  = $conn->real_escape_string($_POST['goal']);
    $cal   = intval($_POST['total_calories']);
    $desc  = $conn->real_escape_string(trim($_POST['description']));
    $uid   = intval($_SESSION['user_id']);
    $conn->query("INSERT INTO diet_charts (title,goal,total_calories,description,created_by) VALUES ('$title','$goal',$cal,'$desc',$uid)");
    echo json_encode(['status'=>'success','id'=>$conn->insert_id]);
}

elseif ($action === 'assign_diet') {
    $uid = intval($_POST['user_id']);
    $cid = intval($_POST['chart_id']);
    $today = date('Y-m-d');
    $conn->query("DELETE FROM user_diet_assignments WHERE user_id=$uid");
    $conn->query("INSERT INTO user_diet_assignments (user_id,chart_id,assigned_date) VALUES ($uid,$cid,'$today')");
    echo json_encode(['status'=>'success']);
}
