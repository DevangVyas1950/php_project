<?php
define('SITE_NAME', 'FitLife');
define('BASE_URL', 'http://localhost/fitlife/');

// Goal calorie targets
define('CALORIES_LOSE', 1500);
define('CALORIES_MAINTAIN', 2000);
define('CALORIES_GAIN', 2500);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function calculate_bmi($weight_kg, $height_cm) {
    $height_m = $height_cm / 100;
    return round($weight_kg / ($height_m * $height_m), 2);
}

function calculateBMI($weight_kg, $height_cm) {
    return calculate_bmi($weight_kg, $height_cm);
}

function bmi_category($bmi) {
    if ($bmi < 18.5) return ['label' => 'Underweight', 'color' => 'info'];
    if ($bmi < 25.0) return ['label' => 'Normal', 'color' => 'success'];
    if ($bmi < 30.0) return ['label' => 'Overweight', 'color' => 'warning'];
    return ['label' => 'Obese', 'color' => 'danger'];
}
