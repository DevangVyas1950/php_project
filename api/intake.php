<?php
require_once '../config/constants.php';
require_once '../config/db.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$uid    = intval($_SESSION['user_id']);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$ACTIVITY_LEVELS = ['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extremely_active'];
$EQUIPMENT       = ['none', 'home_basic', 'full_gym'];
$DIET_PREFS      = ['none', 'vegetarian', 'vegan', 'eggetarian', 'pescatarian', 'keto', 'gluten_free'];
$STRESS_LEVELS   = ['low', 'moderate', 'high'];

function clamp_enum($value, array $allowed, string $default) {
    return in_array($value, $allowed, true) ? $value : $default;
}

if ($action === 'get') {
    $stmt = $conn->prepare("SELECT * FROM user_intake WHERE user_id = ?");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        echo json_encode(['status' => 'success', 'exists' => true, 'data' => $row]);
    } else {
        echo json_encode(['status' => 'success', 'exists' => false, 'data' => null]);
    }
    exit;
}

if ($action === 'save') {
    $activity_level  = clamp_enum($_POST['activity_level'] ?? '', $ACTIVITY_LEVELS, 'moderately_active');
    $workout_days     = max(0, min(7, intval($_POST['workout_days_per_week'] ?? 3)));
    $workout_duration = max(0, min(300, intval($_POST['workout_duration_min'] ?? 30)));
    $equipment        = clamp_enum($_POST['equipment_access'] ?? '', $EQUIPMENT, 'none');
    $diet_pref        = clamp_enum($_POST['dietary_preference'] ?? '', $DIET_PREFS, 'none');
    $allergies        = trim(substr($_POST['allergies'] ?? '', 0, 255));
    $health_conditions = trim(substr($_POST['health_conditions'] ?? '', 0, 255));
    $injuries         = trim(substr($_POST['injuries'] ?? '', 0, 255));
    $sleep_hours      = max(0, min(24, floatval($_POST['sleep_hours'] ?? 7)));
    $stress_level     = clamp_enum($_POST['stress_level'] ?? '', $STRESS_LEVELS, 'moderate');
    $water_liters     = max(0, min(15, floatval($_POST['water_intake_liters'] ?? 2)));

    $stmt = $conn->prepare("INSERT INTO user_intake
            (user_id, activity_level, workout_days_per_week, workout_duration_min, equipment_access,
             dietary_preference, allergies, health_conditions, injuries, sleep_hours, stress_level, water_intake_liters)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            activity_level = VALUES(activity_level),
            workout_days_per_week = VALUES(workout_days_per_week),
            workout_duration_min = VALUES(workout_duration_min),
            equipment_access = VALUES(equipment_access),
            dietary_preference = VALUES(dietary_preference),
            allergies = VALUES(allergies),
            health_conditions = VALUES(health_conditions),
            injuries = VALUES(injuries),
            sleep_hours = VALUES(sleep_hours),
            stress_level = VALUES(stress_level),
            water_intake_liters = VALUES(water_intake_liters)");

    $stmt->bind_param(
        'isiisssssdsd',
        $uid, $activity_level, $workout_days, $workout_duration, $equipment,
        $diet_pref, $allergies, $health_conditions, $injuries, $sleep_hours, $stress_level, $water_liters
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Your fitness profile has been saved.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unable to save your fitness profile.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
