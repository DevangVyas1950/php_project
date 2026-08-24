<?php
/**
 * FitLife Recommendation Engine
 * -----------------------------
 * Turns a user's real profile data (age/weight/height/gender/goal from
 * `users`) plus their fitness questionnaire (`user_intake`) into concrete,
 * calculable targets: BMR, TDEE, calorie target, macro split, a filtered
 * exercise list, and general dietary guidance.
 *
 * Nothing here estimates anything from images or guesses at a person's
 * body — every number is computed from data the user explicitly entered,
 * using standard, named formulas (Mifflin-St Jeor for BMR, standard
 * activity multipliers for TDEE). All estimates are approximations and
 * are labeled as such; this is not medical advice.
 */

require_once __DIR__ . '/../config/constants.php';

/** Mifflin-St Jeor BMR estimate (kcal/day) */
function calc_bmr($weight_kg, $height_cm, $age, $gender) {
    $weight_kg = (float) $weight_kg;
    $height_cm = (float) $height_cm;
    $age = (int) $age;

    if ($weight_kg <= 0 || $height_cm <= 0 || $age <= 0) {
        return null;
    }

    $base = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age);

    if ($gender === 'male') {
        return round($base + 5);
    }
    if ($gender === 'female') {
        return round($base - 161);
    }
    // Neutral estimate when gender is 'other'/unspecified: midpoint of the two offsets.
    return round($base - 78);
}

function activity_multiplier($activity_level) {
    $map = [
        'sedentary'          => 1.2,
        'lightly_active'     => 1.375,
        'moderately_active'  => 1.55,
        'very_active'        => 1.725,
        'extremely_active'   => 1.9,
    ];
    return $map[$activity_level] ?? $map['moderately_active'];
}

function calc_tdee($bmr, $activity_level) {
    if ($bmr === null) return null;
    return round($bmr * activity_multiplier($activity_level));
}

function calc_calorie_target($tdee, $goal) {
    if ($tdee === null) return null;

    $shift = match ($goal) {
        'lose_weight' => -500,
        'gain_muscle' => 300,
        default       => 0,
    };

    // Safety floor: never recommend under 1200 kcal/day regardless of deficit math.
    return max(1200, (int) round($tdee + $shift));
}

function calc_macro_targets($calorie_target, $weight_kg, $goal) {
    if ($calorie_target === null || $weight_kg <= 0) return null;

    $protein_per_kg = match ($goal) {
        'lose_weight' => 2.0,
        'gain_muscle' => 1.8,
        default       => 1.4,
    };

    $protein_g = round($weight_kg * $protein_per_kg);
    $protein_cal = $protein_g * 4;

    $fat_cal = $calorie_target * 0.25;
    $fat_g = round($fat_cal / 9);

    $carbs_cal = max(0, $calorie_target - $protein_cal - $fat_cal);
    $carbs_g = round($carbs_cal / 4);

    $total_cal = max(1, ($protein_g * 4) + ($carbs_g * 4) + ($fat_g * 9));

    return [
        'protein_g'   => (int) $protein_g,
        'carbs_g'     => (int) $carbs_g,
        'fat_g'       => (int) $fat_g,
        'protein_pct' => round(($protein_g * 4 / $total_cal) * 100),
        'carbs_pct'   => round(($carbs_g * 4 / $total_cal) * 100),
        'fat_pct'     => round(($fat_g * 9 / $total_cal) * 100),
    ];
}

function calc_water_target_liters($weight_kg) {
    if ($weight_kg <= 0) return 2.0;
    return round($weight_kg * 0.033, 1); // ~33ml per kg, a common general guideline
}

/**
 * Filters the exercise library by declared equipment access and flags
 * exercises that may warrant caution based on reported injuries.
 * This is a keyword-based heuristic for user awareness, not a medical
 * screening — always paired with a disclaimer in the UI.
 */
function get_recommended_exercises($conn, $equipment_access, $injuries_text = '') {
    $equipment_access = in_array($equipment_access, ['none', 'home_basic', 'full_gym'], true)
        ? $equipment_access : 'none';

    $full_gym_only_keywords = ['machine', 'cable', 'barbell', 'smith', 'leg press'];
    $home_ok_keywords       = ['dumbbell', 'kettlebell', 'band', 'resistance band'];

    $res = $conn->query("SELECT id, title, category, calories_per_min, media_url, instructions FROM exercise_library ORDER BY category, title");
    $exercises = [];
    while ($row = $res->fetch_assoc()) {
        $exercises[] = $row;
    }

    $injuries_lower = strtolower($injuries_text);
    $injury_keywords_map = [
        'knee'     => ['jumping jacks', 'running'],
        'ankle'    => ['jumping jacks', 'running'],
        'back'     => ['forward fold', 'dumbbell press'],
        'spine'    => ['forward fold', 'dumbbell press'],
        'shoulder' => ['dumbbell press', 'push-ups'],
        'wrist'    => ['push-ups', 'dumbbell press'],
    ];

    $flagged_titles = [];
    foreach ($injury_keywords_map as $keyword => $titles) {
        if ($keyword !== '' && strpos($injuries_lower, $keyword) !== false) {
            foreach ($titles as $t) {
                $flagged_titles[strtolower($t)] = $keyword;
            }
        }
    }

    $out = [];
    foreach ($exercises as $ex) {
        $title_lower = strtolower($ex['title']);
        $needs_full_gym = false;
        $needs_home_equip = false;
        foreach ($full_gym_only_keywords as $kw) {
            if (strpos($title_lower, $kw) !== false) $needs_full_gym = true;
        }
        foreach ($home_ok_keywords as $kw) {
            if (strpos($title_lower, $kw) !== false) $needs_home_equip = true;
        }

        if ($needs_full_gym && $equipment_access !== 'full_gym') {
            continue; // requires equipment the user doesn't have
        }
        if ($needs_home_equip && $equipment_access === 'none') {
            continue;
        }

        $ex['caution'] = $flagged_titles[$title_lower] ?? null;
        $out[] = $ex;
    }

    return $out;
}

/**
 * Curated dietary guidance by stated preference. Not tied to the `foods`
 * table (which is a small fixed meal-log catalog) — these are general
 * food-group suggestions, filtered against reported allergies by simple
 * keyword match.
 */
function get_diet_guidance($dietary_preference, $allergies_text = '') {
    $catalog = [
        'none' => [
            'favor' => ['Chicken breast', 'Eggs', 'Fish', 'Greek yogurt', 'Lentils', 'Oats', 'Brown rice', 'Mixed vegetables', 'Fruit', 'Olive oil', 'Nuts'],
            'note'  => 'A balanced mix of lean protein, whole grains, vegetables, and healthy fats.',
        ],
        'vegetarian' => [
            'favor' => ['Paneer', 'Greek yogurt', 'Eggs', 'Lentils & dal', 'Chickpeas & beans', 'Tofu', 'Nuts & seeds', 'Oats', 'Brown rice', 'Mixed vegetables'],
            'note'  => 'No meat, poultry, or fish — leans on dairy, eggs, and plant proteins for protein targets.',
        ],
        'eggetarian' => [
            'favor' => ['Eggs', 'Paneer', 'Greek yogurt', 'Lentils & dal', 'Chickpeas & beans', 'Tofu', 'Nuts & seeds', 'Oats', 'Brown rice'],
            'note'  => 'Vegetarian plus eggs — a solid, flexible protein base.',
        ],
        'vegan' => [
            'favor' => ['Tofu & tempeh', 'Lentils & dal', 'Chickpeas & beans', 'Edamame', 'Quinoa', 'Nuts & seeds', 'Plant-based protein powder', 'Oats', 'Brown rice', 'Mixed vegetables'],
            'note'  => 'No animal products at all — pay extra attention to B12, iron, and total protein intake across the day.',
        ],
        'pescatarian' => [
            'favor' => ['Fish (salmon, tuna)', 'Shrimp', 'Eggs', 'Greek yogurt', 'Lentils & dal', 'Tofu', 'Oats', 'Brown rice', 'Mixed vegetables'],
            'note'  => 'Vegetarian plus fish and seafood for additional omega-3s and protein variety.',
        ],
        'keto' => [
            'favor' => ['Eggs', 'Chicken thighs', 'Fatty fish', 'Avocado', 'Olive oil', 'Nuts & seeds', 'Leafy greens', 'Cheese', 'Paneer'],
            'note'  => 'Very low carb, high fat. Keep carbs concentrated in non-starchy vegetables.',
        ],
        'gluten_free' => [
            'favor' => ['Rice', 'Quinoa', 'Chicken breast', 'Fish', 'Eggs', 'Lentils & dal', 'Mixed vegetables', 'Nuts & seeds', 'Gluten-free oats'],
            'note'  => 'No wheat, barley, or rye — rice and quinoa replace bread/roti as the main carb source.',
        ],
    ];

    $profile = $catalog[$dietary_preference] ?? $catalog['none'];

    // Filter out any favored food that matches a reported allergy keyword.
    $allergy_terms = array_filter(array_map('trim', explode(',', strtolower($allergies_text))));
    $filtered_out = [];
    $favor = array_filter($profile['favor'], function ($food) use ($allergy_terms, &$filtered_out) {
        $food_lower = strtolower($food);
        foreach ($allergy_terms as $term) {
            if ($term !== '' && strpos($food_lower, $term) !== false) {
                $filtered_out[] = $food;
                return false;
            }
        }
        return true;
    });

    return [
        'favor'       => array_values($favor),
        'filtered_out' => $filtered_out,
        'note'        => $profile['note'],
    ];
}

/**
 * Assembles the full recommendation payload for a given user + their
 * (optional) intake questionnaire data.
 */
function build_recommendation($conn, $user, $intake) {
    $weight = (float) ($user['weight_kg'] ?? 0);
    $height = (float) ($user['height_cm'] ?? 0);
    $age    = (int) ($user['age'] ?? 0);
    $gender = strtolower((string) ($user['gender'] ?? 'other'));
    $goal   = strtolower((string) ($user['goal'] ?? 'stay_healthy'));

    $activity_level = $intake['activity_level'] ?? 'moderately_active';
    $equipment      = $intake['equipment_access'] ?? 'none';
    $injuries       = $intake['injuries'] ?? '';
    $diet_pref      = $intake['dietary_preference'] ?? 'none';
    $allergies      = $intake['allergies'] ?? '';
    $health         = $intake['health_conditions'] ?? '';

    $bmr = calc_bmr($weight, $height, $age, $gender);
    $tdee = calc_tdee($bmr, $activity_level);
    $calorie_target = calc_calorie_target($tdee, $goal);
    $macros = calc_macro_targets($calorie_target, $weight, $goal);
    $water_target = calc_water_target_liters($weight);

    $exercises = get_recommended_exercises($conn, $equipment, $injuries);
    $diet = get_diet_guidance($diet_pref, $allergies);

    $notes = [];
    if (trim($injuries) !== '') {
        $notes[] = 'You reported: "' . htmlspecialchars($injuries) . '". Exercises that may need extra caution are flagged below — consider a physio or trainer\'s guidance before continuing them.';
    }
    if (trim($health) !== '') {
        $notes[] = 'You reported the following health conditions: "' . htmlspecialchars($health) . '". Please factor this into any diet or exercise changes, and consult a doctor if unsure.';
    }
    if ($bmr === null) {
        $notes[] = 'Add your age, weight, and height in your Profile to unlock calorie and macro targets.';
    }

    return [
        'has_intake'      => $intake !== null,
        'bmr'             => $bmr,
        'tdee'            => $tdee,
        'calorie_target'  => $calorie_target,
        'macros'          => $macros,
        'water_target_l'  => $water_target,
        'activity_level'  => $activity_level,
        'goal'            => $goal,
        'exercises'       => $exercises,
        'diet'            => $diet,
        'notes'           => $notes,
    ];
}
