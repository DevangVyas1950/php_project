<?php
require_once 'config/db.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
session_start();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = sanitize($_POST['name']);
    $email     = sanitize($_POST['email']);
    $password  = $_POST['password'];
    $age       = (int)$_POST['age'];
    $gender    = sanitize($_POST['gender']);
    $weight    = (float)$_POST['weight_kg'];
    $height    = (float)$_POST['height_cm'];
    $goal      = sanitize($_POST['goal']);
    $bmi       = calculateBMI($weight, $height);
    $hash      = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,age,gender,weight_kg,height_cm,goal,bmi) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssisddsd", $name, $email, $hash, $age, $gender, $weight, $height, $goal, $bmi);
    if ($stmt->execute()) {
        $success = 'Account created! <a href="index.php">Login here</a>';
    } else {
        $error = 'Email already exists.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FitLife - Register</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Nunito Sans', sans-serif; background: linear-gradient(135deg, #e4381c, #b8290e); min-height: 100vh; padding: 40px 0; }
h3 { font-family: 'Oswald', sans-serif; letter-spacing: .5px; text-transform: uppercase; font-weight: 600; }
.card { border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2) !important; }
.btn-fitlife { background: linear-gradient(to right, #e4381c, #e16521); color: #fff; font-family: 'Oswald', sans-serif; letter-spacing: 1px; text-transform: uppercase; font-weight: 600; border: none; transition: all .25s ease; }
.btn-fitlife:hover { background: linear-gradient(to right, #e16521, #e4381c); color: #fff; box-shadow: 0 6px 20px rgba(228,56,28,.35); transform: translateY(-1px); }
</style>
</head>
<body>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card p-4 shadow">
        <h3 class="text-center mb-3">🥦 FitLife — Register</h3>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <form method="POST">
          <div class="row g-3">
            <div class="col-12"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
            <div class="col-12"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
            <div class="col-12"><input type="password" name="password" class="form-control" placeholder="Password (min 6 chars)" minlength="6" required></div>
            <div class="col-6"><input type="number" name="age" class="form-control" placeholder="Age" min="10" max="100" required></div>
            <div class="col-6">
              <select name="gender" class="form-select" required>
                <option value="">Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-6"><input type="number" step="0.1" name="weight_kg" class="form-control" placeholder="Weight (kg)" required></div>
            <div class="col-6"><input type="number" step="0.1" name="height_cm" class="form-control" placeholder="Height (cm)" required></div>
            <div class="col-12">
              <select name="goal" class="form-select" required>
                <option value="">Your Goal</option>
                <option value="lose_weight">Lose Weight</option>
                <option value="gain_muscle">Gain Muscle</option>
                <option value="stay_healthy">Stay Healthy</option>
              </select>
            </div>
            <div class="col-12"><button type="submit" class="btn btn-fitlife w-100">Create Account</button></div>
          </div>
        </form>
        <div class="text-center mt-3"><small><a href="index.php">Back to Login</a></small></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
