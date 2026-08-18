<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fitlife');

function create_required_tables($conn) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            age INT,
            gender ENUM('male','female','other'),
            weight_kg DECIMAL(5,2),
            height_cm DECIMAL(5,2),
            goal ENUM('lose_weight','gain_muscle','stay_healthy') DEFAULT 'stay_healthy',
            bmi DECIMAL(4,2),
            role ENUM('user','admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS foods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            calories_per_100g DECIMAL(6,2),
            protein_g DECIMAL(5,2),
            carbs_g DECIMAL(5,2),
            fat_g DECIMAL(5,2),
            category ENUM('breakfast','lunch','dinner','snack')
        )",
        "CREATE TABLE IF NOT EXISTS diet_charts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            goal ENUM('lose_weight','gain_muscle','stay_healthy'),
            total_calories INT,
            description TEXT,
            created_by INT,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS diet_chart_meals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            chart_id INT NOT NULL,
            food_id INT NOT NULL,
            meal_time ENUM('breakfast','lunch','dinner','snack'),
            quantity_g DECIMAL(6,2),
            FOREIGN KEY (chart_id) REFERENCES diet_charts(id) ON DELETE CASCADE,
            FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS user_diet_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            chart_id INT NOT NULL,
            assigned_date DATE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (chart_id) REFERENCES diet_charts(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS exercises (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            category ENUM('cardio','strength','flexibility','yoga'),
            duration_min INT,
            calories_burned INT,
            instructions TEXT,
            difficulty ENUM('beginner','intermediate','advanced')
        )",
        "CREATE TABLE IF NOT EXISTS exercise_library (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            category ENUM('cardio','yoga','strength','flexibility') NOT NULL,
            calories_per_min DECIMAL(5,2) DEFAULT 0.00,
            media_url VARCHAR(255) DEFAULT NULL,
            instructions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS user_exercise_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            exercise_id INT NOT NULL,
            category ENUM('cardio','yoga','strength','flexibility') NOT NULL,
            duration_mins INT NOT NULL,
            calories_burned INT NOT NULL,
            log_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (exercise_id) REFERENCES exercise_library(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            task_title VARCHAR(200) NOT NULL,
            task_type ENUM('diet','workout','water','general','other') DEFAULT 'general',
            due_date DATE,
            is_completed TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS user_tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            task_title VARCHAR(200),
            task_type ENUM('diet','exercise','water','sleep','other'),
            due_date DATE,
            is_completed TINYINT(1) DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS food_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            food_name VARCHAR(150) NOT NULL,
            meal_type ENUM('Breakfast','Lunch','Dinner','Snack') NOT NULL DEFAULT 'Breakfast',
            calories DECIMAL(6,2) NOT NULL,
            protein_g DECIMAL(6,2) DEFAULT 0,
            carbs_g DECIMAL(6,2) DEFAULT 0,
            fat_g DECIMAL(6,2) DEFAULT 0,
            logged_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS exercise_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            exercise_id INT NOT NULL,
            duration_min INT,
            calories_burned INT,
            log_date DATE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS progress_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            weight_kg DECIMAL(5,2),
            bmi DECIMAL(4,2),
            log_date DATE,
            notes TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS weight_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            weight_kg DECIMAL(5,2) NOT NULL,
            recorded_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_weight_day (user_id, recorded_date),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )"
    ];

    foreach ($statements as $statement) {
        if (!$conn->query($statement)) {
            throw new Exception('Failed to initialize database schema: ' . $conn->error);
        }
    }

    $sample_exercises = [
        ['Jumping Jacks', 'cardio', 12.5, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80', 'Start with a quick rhythm and keep your core engaged.'],
        ['Running', 'cardio', 10.5, 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80', 'Run at a steady pace to build endurance.'],
        ['Warrior II', 'yoga', 6.5, 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=800&q=80', 'Hold the pose and breathe deeply through the movement.'],
        ['Cobra Pose', 'yoga', 5.5, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80', 'Gently lift the chest and stretch the front body.'],
        ['Dumbbell Press', 'strength', 9.0, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80', 'Press with control and keep your posture upright.'],
        ['Push-ups', 'strength', 8.5, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80', 'Lower slowly and keep the body straight.'],
        ['Forward Fold', 'flexibility', 4.5, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80', 'Fold from the hips and relax the neck and shoulders.'],
        ['Cobra Stretch', 'flexibility', 4.0, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80', 'Stretch the abdomen and upper back with steady breathing.']
    ];

    foreach ($sample_exercises as $exercise) {
        $title = $conn->real_escape_string($exercise[0]);
        $category = $conn->real_escape_string($exercise[1]);
        $calories = floatval($exercise[2]);
        $media = $conn->real_escape_string($exercise[3]);
        $instructions = $conn->real_escape_string($exercise[4]);
        $conn->query("INSERT INTO exercise_library (title, category, calories_per_min, media_url, instructions)
            SELECT '$title', '$category', $calories, '$media', '$instructions'
            WHERE NOT EXISTS (SELECT 1 FROM exercise_library WHERE title = '$title')");
    }
}

function initialize_database() {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn && !$conn->connect_error) {
        $conn->set_charset('utf8mb4');
        create_required_tables($conn);
        return $conn;
    }

    $server_conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($server_conn->connect_error) {
        throw new Exception('Database connection failed: ' . $server_conn->connect_error);
    }

    $server_conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $server_conn->close();

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed after initialization: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    create_required_tables($conn);

    return $conn;
}

try {
    $conn = initialize_database();
} catch (Exception $e) {
    die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
}
