<?php
require_once __DIR__ . '/../config/db.php';

function sanitize($value) {
    if (!is_string($value)) {
        return $value;
    }

    return trim(stripslashes($value));
}

function get_user($id) {
    global $conn;
    $id = intval($id);
    $res = $conn->query("SELECT * FROM users WHERE id = $id");
    return $res->fetch_assoc();
}

function get_age_bracket($age) {
    $age = (int) $age;

    if ($age >= 10 && $age <= 18) {
        return 'teens';
    }

    if ($age >= 19 && $age <= 45) {
        return 'adults';
    }

    return 'seniors';
}

function get_diet_recommendation($user) {
    $age = (int) ($user['age'] ?? 0);
    $weight = (float) ($user['weight_kg'] ?? 0);
    $height = (float) ($user['height_cm'] ?? 0);
    $goal = strtolower((string) ($user['goal'] ?? 'stay_healthy'));
    $gender = strtolower((string) ($user['gender'] ?? 'other'));
    $age_bracket = get_age_bracket($age);

    $goal_profiles = [
        'lose_weight' => [
            'label' => 'Lose Weight',
            'calorie_shift' => -300,
            'protein_pct' => 0.40,
            'carb_pct' => 0.30,
            'fat_pct' => 0.30,
            'focus' => 'Lean proteins, vegetables, and whole grains to support steady fat loss.'
        ],
        'gain_muscle' => [
            'label' => 'Gain Muscle',
            'calorie_shift' => 260,
            'protein_pct' => 0.30,
            'carb_pct' => 0.50,
            'fat_pct' => 0.20,
            'focus' => 'Higher carbs and protein to fuel recovery and muscle growth.'
        ],
        'stay_healthy' => [
            'label' => 'Stay Healthy',
            'calorie_shift' => 0,
            'protein_pct' => 0.25,
            'carb_pct' => 0.50,
            'fat_pct' => 0.25,
            'focus' => 'Balanced meals with variety, hydration, and steady energy.'
        ]
    ];

    $profile = $goal_profiles[$goal] ?? $goal_profiles['stay_healthy'];

    $base_calories = match ($age_bracket) {
        'teens' => 2400,
        'adults' => 2200,
        'seniors' => 1800,
        default => 2200,
    };

    $gender_adjustment = match ($gender) {
        'male' => 120,
        'female' => -80,
        default => 0,
    };

    $weight_adjustment = (int) round(($weight - 70) * 7);
    $height_adjustment = (int) round(($height - 170) * 2);

    $target_calories = max(1400, $base_calories + $gender_adjustment + $weight_adjustment + $height_adjustment + $profile['calorie_shift']);
    $protein_g = max(90, (int) round(($target_calories * $profile['protein_pct']) / 4));
    $carbs_g = max(120, (int) round(($target_calories * $profile['carb_pct']) / 4));
    $fat_g = max(45, (int) round(($target_calories * $profile['fat_pct']) / 9));

    $breakfast_title = match ($age_bracket) {
        'teens' => 'Greek yogurt bowl with granola and fruit',
        'seniors' => 'Vegetable omelet with whole grain toast',
        default => 'Protein smoothie with berries and oats',
    };

    $lunch_title = match (true) {
        $goal === 'gain_muscle' => 'Grilled chicken rice bowl with greens',
        $goal === 'lose_weight' => 'Salmon salad with quinoa',
        default => 'Chicken stir-fry with brown rice',
    };

    $dinner_title = match (true) {
        $goal === 'gain_muscle' => 'Lean beef or tofu with sweet potato',
        $goal === 'lose_weight' => 'Baked fish with roasted vegetables',
        default => 'Tofu curry with brown rice',
    };

    $snacks_title = match ($goal) {
        'gain_muscle' => 'Protein shake with banana',
        'lose_weight' => 'Apple slices with almond butter',
        default => 'Trail mix with fruit',
    };

    $meal_plan = [
        [
            'meal' => 'Breakfast',
            'title' => $breakfast_title,
            'details' => 'A protein-rich breakfast to start the day with steady energy.'
        ],
        [
            'meal' => 'Lunch',
            'title' => $lunch_title,
            'details' => 'Balanced portions of lean protein and fiber-rich vegetables.'
        ],
        [
            'meal' => 'Snacks',
            'title' => $snacks_title,
            'details' => 'A simple snack to stay on track between meals.'
        ],
        [
            'meal' => 'Dinner',
            'title' => $dinner_title,
            'details' => 'A satisfying evening meal that supports recovery and fullness.'
        ],
    ];

    return [
        'age_bracket' => $age_bracket,
        'goal_label' => $profile['label'],
        'focus' => $profile['focus'],
        'target_calories' => $target_calories,
        'target_macros' => [
            'protein_g' => $protein_g,
            'carbs_g' => $carbs_g,
            'fat_g' => $fat_g,
        ],
        'meal_plan' => $meal_plan,
    ];
}

function get_today_tasks($user_id) {
    global $conn;
    $uid = intval($user_id);
    $today = date('Y-m-d');
    $res = $conn->query("SELECT * FROM user_tasks WHERE user_id = $uid AND due_date = '$today'");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function get_today_calories($user_id) {
    global $conn;
    $uid = intval($user_id);
    $today = date('Y-m-d');
    $res = $conn->query("SELECT SUM(calories) as total FROM food_log WHERE user_id = $uid AND logged_date = '$today'");
    $row = $res->fetch_assoc();
    return $row['total'] ?? 0;
}

function get_user_diet_chart($user_id) {
    global $conn;
    $uid = intval($user_id);
    $res = $conn->query("
        SELECT dc.*, uda.assigned_date
        FROM diet_charts dc
        JOIN user_diet_assignments uda ON dc.id = uda.chart_id
        WHERE uda.user_id = $uid
        ORDER BY uda.assigned_date DESC LIMIT 1
    ");
    return $res->fetch_assoc();
}

function get_chart_meals($chart_id) {
    global $conn;
    $cid = intval($chart_id);
    $res = $conn->query("
        SELECT dcm.*, f.name, f.calories_per_100g, f.protein_g, f.carbs_g, f.fat_g,
               ROUND((dcm.quantity_g / 100) * f.calories_per_100g, 1) as total_calories
        FROM diet_chart_meals dcm
        JOIN foods f ON dcm.food_id = f.id
        WHERE dcm.chart_id = $cid
        ORDER BY FIELD(dcm.meal_time, 'breakfast','lunch','dinner','snack')
    ");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function get_exercises_by_goal($goal) {
    global $conn;
    $difficulty = ($goal === 'gain_muscle') ? 'intermediate' : 'beginner';
    $goal_s = $conn->real_escape_string($goal);
    $diff_s = $conn->real_escape_string($difficulty);
    $res = $conn->query("SELECT * FROM exercises WHERE difficulty = '$diff_s' OR difficulty = 'beginner' LIMIT 6");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function get_progress_history($user_id, $limit = 7) {
    global $conn;
    $uid = intval($user_id);
    $lim = intval($limit);
    $res = $conn->query("SELECT * FROM progress_log WHERE user_id = $uid ORDER BY log_date DESC LIMIT $lim");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function get_all_users() {
    global $conn;
    return $conn->query("SELECT id, name, email, goal, bmi, weight_kg, role, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
}

function get_all_foods() {
    global $conn;
    return $conn->query("SELECT * FROM foods ORDER BY category, name")->fetch_all(MYSQLI_ASSOC);
}

function get_all_exercises() {
    global $conn;
    return $conn->query("SELECT * FROM exercises ORDER BY category, difficulty")->fetch_all(MYSQLI_ASSOC);
}

function get_all_diet_charts() {
    global $conn;
    return $conn->query("SELECT dc.*, u.name as created_by_name FROM diet_charts dc LEFT JOIN users u ON dc.created_by = u.id")->fetch_all(MYSQLI_ASSOC);
}
