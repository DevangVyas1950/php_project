<?php
require_once '../config/constants.php';
require_once '../config/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $res = $conn->query("SELECT * FROM users WHERE email = '$email'");
    $user = $res->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        echo json_encode(['status'=>'success','role'=>$user['role']]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Invalid email or password.']);
    }
}

elseif ($action === 'register') {
    $name     = $conn->real_escape_string(trim($_POST['name']));
    $email    = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $age      = intval($_POST['age']);
    $gender   = $conn->real_escape_string($_POST['gender']);
    $weight   = floatval($_POST['weight_kg']);
    $height   = floatval($_POST['height_cm']);
    $goal     = $conn->real_escape_string($_POST['goal']);
    $bmi      = calculate_bmi($weight, $height);

    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        echo json_encode(['status'=>'error','message'=>'Email already registered.']);
        exit;
    }
    $conn->query("INSERT INTO users (name,email,password,age,gender,weight_kg,height_cm,goal,bmi)
                  VALUES ('$name','$email','$password',$age,'$gender',$weight,$height,'$goal',$bmi)");
    echo json_encode(['status'=>'success','message'=>'Registration successful!']);
}

elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['status'=>'success']);
}
